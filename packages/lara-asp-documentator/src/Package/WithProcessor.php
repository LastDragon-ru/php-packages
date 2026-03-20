<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Package;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Container;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\FileTask;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\HookTask;
use LastDragon_ru\LaraASP\Documentator\Processor\Dispatcher;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Adapters\SymfonyFileSystem;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File as FileImpl;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\LaraASP\Documentator\Processor\Hook;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use Mockery;
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
    private ?WithProcessorResolver $withProcessorResolver = null;

    abstract protected function app(): Application;

    #[After]
    protected function withProcessorAfter(): void {
        $this->withProcessorResolver = null;
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

    protected function runProcessorHookTask(
        HookTask $task,
        FileSystem $fs,
        ?File $file = null,
        ?Hook $hook = null,
    ): void {
        $file ??= Mockery::mock(FileImpl::class);
        $hook ??= $task::hook();
        $hook   = is_array($hook) ? array_first($hook) : $hook;

        $task($this->getProcessorResolver($fs), $file, $hook);
    }

    protected function runProcessorFileTask(FileTask $task, FileSystem $fs, File $file): void {
        $task($this->getProcessorResolver($fs), $file);
    }

    private function getProcessorResolver(FileSystem $filesystem): WithProcessorResolver {
        $dispatcher                  = new Dispatcher(null);
        $container                   = $this->app()->make(Container::class);
        $this->withProcessorResolver = new WithProcessorResolver($container, $dispatcher, $filesystem);

        return $this->withProcessorResolver;
    }

    protected function assertProcessorResolvedPathsCount(int $count): void {
        self::assertSame(
            $count,
            $this->withProcessorResolver !== null
                ? count($this->withProcessorResolver->resolved)
                : null,
        );
    }

    /**
     * @param list<FilePath> $paths
     */
    protected function assertProcessorResolvedPaths(array $paths): void {
        self::assertEquals($paths, $this->withProcessorResolver->resolved ?? null);
    }

    protected function assertProcessorSavedPathsCount(int $count): void {
        self::assertSame(
            $count,
            $this->withProcessorResolver !== null
                ? count($this->withProcessorResolver->saved)
                : null,
        );
    }

    /**
     * @param list<FilePath> $paths
     */
    protected function assertProcessorSavedPaths(array $paths): void {
        self::assertEquals($paths, $this->withProcessorResolver->saved ?? null);
    }

    protected function assertProcessorQueuedPathsCount(int $count): void {
        self::assertSame(
            $count,
            $this->withProcessorResolver !== null
                ? count($this->withProcessorResolver->queued)
                : null,
        );
    }

    /**
     * @param list<FilePath> $paths
     */
    protected function assertProcessorQueuedPaths(array $paths): void {
        self::assertEquals($paths, $this->withProcessorResolver->queued ?? null);
    }

    protected function assertProcessorDeletedPathsCount(int $count): void {
        self::assertSame(
            $count,
            $this->withProcessorResolver !== null
                ? count($this->withProcessorResolver->deleted)
                : null,
        );
    }

    /**
     * @param list<DirectoryPath|FilePath> $paths
     */
    protected function assertProcessorDeletedPaths(array $paths): void {
        self::assertEquals($paths, $this->withProcessorResolver->deleted ?? null);
    }
}
