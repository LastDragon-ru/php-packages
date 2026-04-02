<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Formats\Php;

use LastDragon_ru\LaraASP\Documentator\Composer\ComposerJson;
use LastDragon_ru\LaraASP\Documentator\Processor\Formats\SerializedFile;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer;

/**
 * @extends SerializedFile<ComposerJson>
 */
class ComposerJsonFile extends SerializedFile {
    public function __construct(Serializer $serializer) {
        parent::__construct($serializer, ComposerJson::class);
    }
}
