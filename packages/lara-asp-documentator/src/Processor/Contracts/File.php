<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Contracts;

use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File as FileImpl;
use LastDragon_ru\Path\FilePath;

/**
 * @phpstan-require-extends FileImpl
 */
interface File {
    public FilePath $path {
        get;
    }

    /**
     * @var non-empty-string
     */
    public string $name {
        get;
    }

    /**
     * @var ?non-empty-string
     */
    public ?string $extension {
        get;
    }

    public string $content {
        get;
    }
}
