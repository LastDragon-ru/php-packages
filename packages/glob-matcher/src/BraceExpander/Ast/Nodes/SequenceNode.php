<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Ast\Nodes;

use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Cursor;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Node;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\NodeParent;
use Override;

/**
 * @implements NodeParent<Node&SequenceNodeChild>
 */
class SequenceNode implements Node, NodeParent, BraceExpansionNodeChild, SequenceNodeChild {
    public function __construct(
        /**
         * @var list<Node&SequenceNodeChild>
         */
        public array $children,
    ) {
        // empty
    }

    #[Override]
    public static function toIterable(Cursor $cursor): iterable {
        foreach ($cursor->children as $child) {
            yield from $child->node::toIterable($child);
        }
    }
}
