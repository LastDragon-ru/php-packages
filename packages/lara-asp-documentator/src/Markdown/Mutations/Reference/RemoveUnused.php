<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Reference;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Reference\Node as ReferenceNode;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Delete;
use League\CommonMark\Node\Node;
use Override;

/**
 * Removes unused references.
 *
 * @implements Mutation<ReferenceNode>
 */
readonly class RemoveUnused implements Mutation {
    public function __construct() {
        // empty
    }

    #[Override]
    public static function nodes(): array {
        return [
            ReferenceNode::class,
        ];
    }

    #[Override]
    public function mutagens(Document $document, Node $node): array {
        return !isset(Usages::get($document->node)[$node])
            ? [new Delete(Location::get($node))]
            : [];
    }
}
