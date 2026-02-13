<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Serializer\Attributes;

use Attribute;
use Symfony\Component\Serializer\Attribute\DiscriminatorMap;

#[Attribute(Attribute::TARGET_CLASS)]
class TypeMap extends DiscriminatorMap {
    /**
     * @param array<string, class-string> $mapping
     */
    public function __construct(array $mapping) {
        parent::__construct('$type', $mapping);
    }
}
