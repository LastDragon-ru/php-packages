<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Contracts;

use LastDragon_ru\Path\FilePath;

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
