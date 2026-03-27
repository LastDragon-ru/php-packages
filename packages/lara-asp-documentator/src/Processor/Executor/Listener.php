<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Executor;

use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;

/**
 * @internal
 */
interface Listener {
    public function run(FilePath $path): void;

    public function save(FilePath $path): void;

    public function queue(FilePath $path): void;

    public function delete(DirectoryPath|FilePath $path): void;
}
