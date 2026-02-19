<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Contracts;

/**
 * @template TValue
 */
interface Cast {
    /**
     * @return TValue
     */
    public function __invoke(Resolver $resolver, File $file): mixed;
}
