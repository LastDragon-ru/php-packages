<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeExample\Contracts;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;

interface Runner {
    /**
     * @param File<string> $file
     */
    public function __invoke(File $file): ?string;
}
