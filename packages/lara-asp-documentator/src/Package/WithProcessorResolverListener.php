<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Package;

use LastDragon_ru\LaraASP\Documentator\Processor\Executor\Listener;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use Override;

/**
 * @internal
 */
class WithProcessorResolverListener implements Listener {
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

    public function __construct() {
        // empty
    }

    #[Override]
    public function run(FilePath $path): void {
        $this->resolved[] = $path;
    }

    #[Override]
    public function save(FilePath $path): void {
        $this->saved[] = $path;
    }

    #[Override]
    public function queue(FilePath $path): void {
        $this->queued[] = $path;
    }

    #[Override]
    public function delete(DirectoryPath|FilePath $path): void {
        $this->deleted[] = $path;
    }
}
