<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Reference;

use ArrayAccess;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Data;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Reference;
use League\CommonMark\Extension\CommonMark\Node\Inline\AbstractWebResource;
use League\CommonMark\Node\Node;
use League\CommonMark\Reference\ReferenceInterface;
use Override;
use WeakMap;

/**
 * @internal
 * @extends Data<ArrayAccess<ReferenceInterface, list<AbstractWebResource>>>
 */
readonly class Usages extends Data {
    #[Override]
    protected static function default(Node $node): mixed {
        /** @var WeakMap<ReferenceInterface, list<AbstractWebResource>> $usages */
        $usages = new WeakMap();

        foreach ($node->iterator() as $child) {
            if (!$child instanceof AbstractWebResource) {
                continue;
            }

            $reference = Reference::get($child);

            if ($reference !== null) {
                $usages[$reference] ??= [];
                $usages[$reference][] = $child;
            }
        }

        return $usages;
    }
}
