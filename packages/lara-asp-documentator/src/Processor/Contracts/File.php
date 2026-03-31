<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Contracts;

use LastDragon_ru\Path\FilePath;

/**
 * @template TContent
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

    /**
     * @var TContent
     */
    public mixed $content {
        get;
    }

    /**
     * @param TContent $content
     */
    public function save(mixed $content): void;
}
