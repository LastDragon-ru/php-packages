<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts;

use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;

interface Parameters extends Serializable {
    /**
     * @var non-empty-string
     */
    public string $target {
        get;
    }
}
