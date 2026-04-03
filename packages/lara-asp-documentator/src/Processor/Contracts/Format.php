<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Contracts;

/**
 * @template TInput
 * @template TOutput
 */
interface Format {
    /**
     * @param File<TInput> $file
     *
     * @return TOutput
     */
    public function read(Resolver $resolver, File $file): mixed;

    /**
     * @param File<TOutput> $file
     * @param TOutput       $content
     *
     * @return TInput
     */
    public function write(Resolver $resolver, File $file, mixed $content): mixed;
}
