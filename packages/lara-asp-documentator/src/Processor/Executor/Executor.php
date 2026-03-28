<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Executor;

use Exception;
use LastDragon_ru\GlobMatcher\Contracts\Matcher;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Container;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\FileTask;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\HookTask;
use LastDragon_ru\LaraASP\Documentator\Processor\Dispatcher;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\HookBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\HookEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\HookResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyCircularDependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyUnavailable;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\TaskNotInvokable;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\LaraASP\Documentator\Processor\Hook;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use Override;

use function array_last;
use function array_values;

/**
 * @internal
 */
class Executor implements Listener {
    private State             $state;
    private readonly Iterator $iterator;
    private readonly Resolver $resolver;

    /**
     * @var array<string, true>
     */
    private array $processed = [];

    /**
     * @var array<string, FilePath>
     */
    private array $stack = [];

    /**
     * @param iterable<mixed, DirectoryPath|FilePath> $files
     */
    public function __construct(
        private readonly Container $container,
        private readonly Dispatcher $dispatcher,
        private readonly Tasks $tasks,
        private readonly FileSystem $fs,
        iterable $files,
        private readonly Matcher $skipped,
    ) {
        $this->state    = State::Created;
        $this->iterator = new Iterator($files);
        $this->resolver = new Resolver($this->container, $this->dispatcher, $this->fs, $this);
    }

    public function __invoke(): void {
        $file        = null;
        $this->state = State::Preparation;

        foreach ($this->iterator as $item) {
            if (!$this->fs->exists($item)) {
                continue;
            }

            if ($file === null) {
                $this->hook(Hook::Before, $item);

                $this->state = State::Iteration;
            }

            $this->file($item);

            $file = $item;
        }

        if ($file !== null) {
            $this->state = State::Finished;

            $this->hook(Hook::After, $file);
        }
    }

    protected function hook(Hook $hook, FilePath $path): void {
        // Tasks?
        if ($hook === Hook::File || !$this->tasks->has($hook)) {
            return;
        }

        // Run
        $result = ($this->dispatcher)(new HookBegin($hook, $path), HookResult::Success);

        try {
            $this->tasks($this->tasks->get($hook), $hook, $path);
        } catch (Exception $exception) {
            $result = HookResult::Error;

            throw $exception;
        } finally {
            ($this->dispatcher)(new HookEnd($result));
        }
    }

    protected function file(FilePath $path): void {
        // Processed?
        if (isset($this->processed[(string) $path])) {
            return;
        }

        // Circular?
        if (isset($this->stack[(string) $path])) {
            // The $file cannot be processed if it is skipped, so we can return
            // it safely in this case.
            if (!$this->isSkipped($path)) {
                ($this->dispatcher)(new FileBegin($path));
                ($this->dispatcher)(new FileEnd(FileResult::Error));

                throw new DependencyCircularDependency($path, array_values($this->stack));
            } else {
                return;
            }
        }

        // Process
        $result                      = ($this->dispatcher)(new FileBegin($path), FileResult::Success);
        $this->stack[(string) $path] = $path;

        try {
            if (!$this->isSkipped($path)) {
                $this->tasks($this->tasks->get($path), Hook::File, $path);
            } else {
                $result = FileResult::Skipped;
            }
        } catch (Exception $exception) {
            $result = FileResult::Error;

            throw $exception;
        } finally {
            ($this->dispatcher)(new FileEnd($result));

            $this->processed[(string) $path] = true;

            unset($this->stack[(string) $path]);
        }
    }

    /**
     * @param iterable<int, Task> $tasks
     */
    protected function tasks(iterable $tasks, Hook $hook, FilePath $path): void {
        $this->resolver->begin($path);
        $this->fs->begin();

        $exists = $this->fs->exists($path);

        foreach ($tasks as $task) {
            if ($exists) {
                $this->task($task, $hook, $path);

                $exists = $this->fs->exists($path);
            } else {
                ($this->dispatcher)(new TaskBegin($task::class));
                ($this->dispatcher)(new TaskEnd(TaskResult::Skipped));
            }
        }

        $this->fs->commit();
        $this->resolver->commit();
    }

    protected function task(Task $task, Hook $hook, FilePath $path): void {
        $result = ($this->dispatcher)(new TaskBegin($task::class), TaskResult::Success);
        $file   = $this->resolver->file($path);

        try {
            if ($task instanceof FileTask) {
                $task($this->resolver, $file);
            } elseif ($task instanceof HookTask) {
                $task($this->resolver, $file, $hook);
            } else {
                throw new TaskNotInvokable($task, $hook, $path);
            }
        } catch (Exception $exception) {
            $result = TaskResult::Error;

            throw $exception;
        } finally {
            ($this->dispatcher)(new TaskEnd($result));
        }
    }

    protected function push(FilePath $path): void {
        $this->iterator->push($path);
    }

    #[Override]
    public function run(FilePath $path): void {
        // Possible?
        if ($this->state->is(State::Created)) {
            throw new DependencyUnavailable($path);
        }

        // Skipped?
        if ($this->isSkipped($path)) {
            return;
        }

        // Process
        if (!$this->state->is(State::Preparation) && !$path->equals(array_last($this->stack))) {
            $this->file($path);
        }
    }

    #[Override]
    public function save(FilePath $path): void {
        // Current?
        if ($path->equals(array_last($this->stack))) {
            return;
        }

        // Skipped?
        if ($this->isSkipped($path)) {
            return;
        }

        // Reset
        unset($this->processed[(string) $path]);

        // Run/Queue
        if ($this->state->is(State::Finished)) {
            $this->file($path);
        } else {
            $this->push($path);
        }
    }

    #[Override]
    public function queue(FilePath $path): void {
        // Possible?
        if ($this->state->is(State::Finished)) {
            throw new DependencyUnavailable($path);
        }

        // Skipped?
        if ($this->isSkipped($path)) {
            return;
        }

        // Queue
        $this->push($path);
    }

    #[Override]
    public function delete(DirectoryPath|FilePath $path): void {
        // todo(documentator/processor): if we have some queued files, then they
        //      should be removed from the queue
    }

    protected function isSkipped(FilePath $path): bool {
        // Tasks?
        if (!$this->tasks->has($path)) {
            return true;
        }

        // Outside?
        if (!$this->fs->input->contains($path)) {
            return true;
        }

        // Excluded?
        $relative = $this->fs->input->relative($path);
        $skipped  = $relative === null || $this->skipped->match($relative);

        if ($skipped) {
            return true;
        }

        // Return
        return false;
    }
}
