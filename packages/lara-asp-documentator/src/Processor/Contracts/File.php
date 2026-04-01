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
     * @template O
     * @template F of Format<TContent, O>
     *
     * @param class-string<F> $format
     *
     * @return self<O>
     */
    public function as(string $format): self;

    /**
     * @param TContent $content
     */
    public function save(mixed $content): void;

    public function delete(): void;
}
