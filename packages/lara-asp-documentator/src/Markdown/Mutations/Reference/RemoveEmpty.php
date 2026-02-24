<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Reference;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Reference\Node as ReferenceNode;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Link\Unlink;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Delete;
use LastDragon_ru\LaraASP\Documentator\Markdown\Utils;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Node\Node;
use Override;

use function array_merge;
use function rawurldecode;

/**
 * Removes references with empty destination..
 *
 * @implements Mutation<ReferenceNode>
 */
readonly class RemoveEmpty implements Mutation {
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
        // Empty?
        if (!Utils::isPathEmpty($document->path, rawurldecode($node->getDestination()))) {
            return [];
        }

        // Remove
        $usages   = Usages::get($document->node)[$node] ?? [];
        $unlink   = new Unlink();
        $mutagens = [new Delete(Location::get($node))];

        foreach ($usages as $usage) {
            if ($usage instanceof Link) {
                $mutagens = array_merge($mutagens, $unlink->mutagens($document, $usage));
            } elseif ($usage instanceof Image) {
                $mutagens[] = new Delete(Location::get($usage));
            } else {
                // what we should do?
            }
        }

        return $mutagens;
    }
}
