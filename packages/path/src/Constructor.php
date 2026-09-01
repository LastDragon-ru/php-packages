<?php declare(strict_types = 1);

namespace LastDragon_ru\Path;

/**
 * @internal
 * @template TPath of string
 */
interface Constructor {
    /**
     * @param TPath $path
     */
    public function __construct(string $path);
}
