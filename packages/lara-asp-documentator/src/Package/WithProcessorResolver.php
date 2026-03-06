<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Package;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Container;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Dispatcher;
use LastDragon_ru\LaraASP\Documentator\Processor\Executor\Resolver;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;

/**
 * @internal
 */
class WithProcessorResolver extends Resolver {
    /**
     * @var list<FilePath>
     */
    public array $resolved = [];
    /**
     * @var list<FilePath>
     */
    public array $saved = [];
    /**
     * @var list<FilePath>
     */
    public array $queued = [];
    /**
     * @var list<DirectoryPath|FilePath>
     */
    public array $deleted = [];

    public function __construct(Container $container, Dispatcher $dispatcher, FileSystem $fs) {
        parent::__construct(
            $container,
            $dispatcher,
            $fs,
            function (File $file): void {
                $this->resolved[] = $file->path;
            },
            function (File $file): void {
                $this->saved[] = $file->path;
            },
            function (File $file): void {
                $this->queued[] = $file->path;
            },
            function (DirectoryPath|FilePath $path): void {
                $this->deleted[] = $path;
            },
        );
    }
}
