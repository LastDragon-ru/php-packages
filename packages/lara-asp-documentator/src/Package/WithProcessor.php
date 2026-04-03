<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Package;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Container;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\FileTask;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\HookTask;
use LastDragon_ru\LaraASP\Documentator\Processor\Dispatcher;
use LastDragon_ru\LaraASP\Documentator\Processor\Executor\Files\NativeFile;
use LastDragon_ru\LaraASP\Documentator\Processor\Executor\Listener;
use LastDragon_ru\LaraASP\Documentator\Processor\Executor\Resolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Executor\Resolver as ResolverImpl;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Adapters\SymfonyFileSystem;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\LaraASP\Documentator\Processor\Hook;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use Override;
use Symfony\Component\Finder\Finder;

use function array_first;
use function is_array;

/**
 * @phpstan-require-extends TestCase
 * @internal
 */
trait WithProcessor {
    abstract protected function app(): Application;

    protected function getFileSystem(
        DirectoryPath|string $input,
        DirectoryPath|string|null $output = null,
    ): FileSystem {
        $input      = ($input instanceof DirectoryPath ? $input : new DirectoryPath($input))->normalized();
        $output     = $output !== null
            ? ($output instanceof DirectoryPath ? $output : new DirectoryPath($output))->normalized()
            : $input;
        $adapter    = new class() extends SymfonyFileSystem {
            #[Override]
            protected function getFinder(
                DirectoryPath $directory,
                ?Closure $include,
                ?Closure $exclude,
                bool $hidden,
            ): Finder {
                return parent::getFinder($directory, $include, $exclude, $hidden)
                    ->sortByName(true);
            }

            #[Override]
            public function write(FilePath $path, string $content): void {
                // Skip
            }
        };
        $dispatcher = new Dispatcher(null);
        $filesystem = new FileSystem($adapter, $dispatcher, $input, $output);

        return $filesystem;
    }

    /**
     * @param File<string>|null $file
     */
    protected function runProcessorHookTask(
        HookTask $task,
        FileSystem $fs,
        ?File $file = null,
        ?Hook $hook = null,
    ): void {
        $file ??= self::createStub(File::class);
        $hook ??= $task::hook();
        $hook   = is_array($hook) ? array_first($hook) : $hook;

        $task($this->getProcessorResolver($fs), $file, $hook);
    }

    /**
     * @return File<string>
     */
    protected function runProcessorFileTask(FileTask $task, FileSystem $fs, FilePath $path): File {
        $resolver = $this->getProcessorResolver($fs);
        $file     = new NativeFile($resolver, $path);

        $task($resolver, $file);

        return $file;
    }

    private function getProcessorResolver(FileSystem $fs): Resolver {
        return new ResolverImpl(
            $this->app()->make(Container::class),
            new Dispatcher(null),
            $fs,
            new class() implements Listener {
                public function __construct() {
                    // empty
                }

                #[Override]
                public function run(FilePath $path): void {
                    // empty
                }

                #[Override]
                public function save(FilePath $path): void {
                    // empty
                }

                #[Override]
                public function queue(FilePath $path): void {
                    // empty
                }

                #[Override]
                public function delete(DirectoryPath|FilePath $path): void {
                    // empty
                }
            },
        );
    }
}
