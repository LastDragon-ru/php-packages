<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Package;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Container;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\FileTask;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\HookTask;
use LastDragon_ru\LaraASP\Documentator\Processor\Dispatcher;
use LastDragon_ru\LaraASP\Documentator\Processor\Executor\Resolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Executor\Resolver as ResolverImpl;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Adapters\SymfonyFileSystem;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\LaraASP\Documentator\Processor\Hook;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use Override;
use PHPUnit\Framework\Attributes\After;
use Symfony\Component\Finder\Finder;

use function array_first;
use function count;
use function is_array;

/**
 * @phpstan-require-extends TestCase
 * @internal
 */
trait WithProcessor {
    private ?WithProcessorResolverListener $withProcessorResolverListener = null;

    abstract protected function app(): Application;

    #[After]
    protected function withProcessorAfter(): void {
        $this->withProcessorResolverListener = null;
    }

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
     * @param File<string> $file
     */
    protected function runProcessorFileTask(FileTask $task, FileSystem $fs, File $file): void {
        $task($this->getProcessorResolver($fs), $file);
    }

    private function getProcessorResolver(FileSystem $filesystem): Resolver {
        $this->withProcessorResolverListener = new WithProcessorResolverListener();
        $resolver                            = new ResolverImpl(
            $this->app()->make(Container::class),
            new Dispatcher(null),
            $filesystem,
            $this->withProcessorResolverListener,
        );

        return $resolver;
    }

    protected function assertProcessorResolvedPathsCount(int $count): void {
        self::assertSame(
            $count,
            $this->withProcessorResolverListener !== null
                ? count($this->withProcessorResolverListener->resolved)
                : null,
        );
    }

    /**
     * @param list<FilePath> $paths
     */
    protected function assertProcessorResolvedPaths(array $paths): void {
        self::assertEquals($paths, $this->withProcessorResolverListener->resolved ?? null);
    }

    protected function assertProcessorSavedPathsCount(int $count): void {
        self::assertSame(
            $count,
            $this->withProcessorResolverListener !== null
                ? count($this->withProcessorResolverListener->saved)
                : null,
        );
    }

    /**
     * @param list<FilePath> $paths
     */
    protected function assertProcessorSavedPaths(array $paths): void {
        self::assertEquals($paths, $this->withProcessorResolverListener->saved ?? null);
    }

    protected function assertProcessorQueuedPathsCount(int $count): void {
        self::assertSame(
            $count,
            $this->withProcessorResolverListener !== null
                ? count($this->withProcessorResolverListener->queued)
                : null,
        );
    }

    /**
     * @param list<FilePath> $paths
     */
    protected function assertProcessorQueuedPaths(array $paths): void {
        self::assertEquals($paths, $this->withProcessorResolverListener->queued ?? null);
    }

    protected function assertProcessorDeletedPathsCount(int $count): void {
        self::assertSame(
            $count,
            $this->withProcessorResolverListener !== null
                ? count($this->withProcessorResolverListener->deleted)
                : null,
        );
    }

    /**
     * @param list<DirectoryPath|FilePath> $paths
     */
    protected function assertProcessorDeletedPaths(array $paths): void {
        self::assertEquals($paths, $this->withProcessorResolverListener->deleted ?? null);
    }
}
