<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Format;
use LastDragon_ru\Path\FilePath;
use Throwable;

use function sprintf;

class FormatNotWritable extends FormatException {
    public function __construct(
        /**
         * @var class-string<Format<*, *>>
         */
        protected string $format,
        protected readonly FilePath $target,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'File format `%s` is not writable (path: `%s`).',
                $this->format,
                $this->target,
            ),
            $previous,
        );
    }
}
