<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use Exception;
use InvalidArgumentException;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Adapter;
use LastDragon_ru\LaraASP\Documentator\Processor\Dispatcher;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemDeleteBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemDeleteEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemDeleteResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemReadBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemReadEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemReadResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemWriteBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemWriteEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemWriteResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\PathNotFound;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\PathNotWritable;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\PathUnavailable;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;

use function sprintf;
use function str_starts_with;
use function strlen;

/**
 * @internal
 */
class FileSystem {
    private Content $content;

    public function __construct(
        private readonly Adapter $adapter,
        private readonly Dispatcher $dispatcher,
        public protected(set) DirectoryPath $input { get => $this->input; },
        public readonly DirectoryPath $output,
    ) {
        if ($input->relative) {
            throw new InvalidArgumentException(
                sprintf(
                    'The `$input` path must be absolute, `%s` given.',
                    $input,
                ),
            );
        }

        if ($output->relative) {
            throw new InvalidArgumentException(
                sprintf(
                    'The `$output` path must be absolute, `%s` given.',
                    $input,
                ),
            );
        }

        $this->content = new Content();
    }

    public function exists(FilePath $path): bool {
        return $this->adapter->exists($this->path($path));
    }

    /**
     * @param list<non-empty-string> $exclude
     * @param list<non-empty-string> $include
     *
     * @return iterable<mixed, DirectoryPath|FilePath>
     */
    public function search(
        DirectoryPath $directory,
        array $include = [],
        array $exclude = [],
        bool $hidden = false,
    ): iterable {
        // Exist?
        $directory = $this->path($directory);

        if (!$this->adapter->exists($directory)) {
            throw new PathNotFound($directory);
        }

        // Search
        $iterator = $this->adapter->search($directory, $include, $exclude, $hidden);

        foreach ($iterator as $path) {
            yield $directory->resolve($path);
        }

        yield from [];
    }

    public function read(FilePath $path): string {
        $path    = $this->path($path);
        $content = $this->content[$path] ?? null;

        if ($content === null) {
            $result = ($this->dispatcher)(new FileSystemReadBegin($path), FileSystemReadResult::Success);
            $bytes  = 0;

            try {
                $content = $this->adapter->read($path);
                $bytes   = strlen($content); // @phpstan-ignore disallowed.function (ok)
            } catch (Exception $exception) {
                $result = FileSystemReadResult::Error;

                throw $exception;
            } finally {
                ($this->dispatcher)(new FileSystemReadEnd($result, $bytes));
            }
        }

        return $content;
    }

    /**
     * If file exists, it will be saved only after {@see self::commit()},
     * if not, it will be created immediately.
     */
    public function write(FilePath $path, string $content): void {
        // Writable?
        $path = $this->path($path);

        if (!$this->output->contains($path)) {
            throw new PathNotWritable($path);
        }

        // Save
        $exists               = $this->exists($path);
        $this->content[$path] = $content;

        if (!$exists) {
            $this->save($path);
        }
    }

    public function delete(DirectoryPath|FilePath $path): void {
        // Writable?
        $path = $this->path($path);

        if (!$this->output->contains($path)) {
            throw new PathNotWritable($path);
        }

        // Delete
        $result = ($this->dispatcher)(new FileSystemDeleteBegin($path), FileSystemDeleteResult::Success);

        try {
            $this->adapter->delete($path);
            $this->content->delete($path);
        } catch (Exception $exception) {
            $result = FileSystemDeleteResult::Error;

            throw $exception;
        } finally {
            ($this->dispatcher)(new FileSystemDeleteEnd($result));
        }
    }

    public function begin(): void {
        // empty
    }

    public function commit(): void {
        // Dump
        foreach ($this->content->changes() as $path) {
            $this->save($path);
        }

        // Cleanup
        $this->content->cleanup();
    }

    protected function save(FilePath $path): void {
        if (!isset($this->content[$path])) {
            return;
        }

        $result = ($this->dispatcher)(new FileSystemWriteBegin($path), FileSystemWriteResult::Success);
        $bytes  = strlen($this->content[$path] ?? ''); // @phpstan-ignore disallowed.function (ok)

        try {
            $this->adapter->write($path, $this->content[$path]);
            $this->content->reset($path);
        } catch (Exception $exception) {
            $result = FileSystemWriteResult::Error;
            $bytes  = 0;

            throw $exception;
        } finally {
            ($this->dispatcher)(new FileSystemWriteEnd($result, $bytes));
        }
    }

    /**
     * @template T of DirectoryPath|FilePath
     *
     * @param T $path
     *
     * @return new<T>
     */
    protected function path(DirectoryPath|FilePath $path): DirectoryPath|FilePath {
        $path = $path->normalized();

        if (!str_starts_with($path->path, $this->input->path) && !str_starts_with($path->path, $this->output->path)) {
            throw new PathUnavailable($path);
        }

        return $path;
    }
}
