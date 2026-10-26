<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Ast\Nodes;

use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Node;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\NodeParent;

/**
 * @implements NodeParent<Node&SequenceNodeChild>
 */
readonly class SequenceNode implements Node, NodeParent, BraceExpansionNodeChild, SequenceNodeChild {
    public function __construct(
        /**
         * @var list<Node&SequenceNodeChild>
         */
        public array $children,
    ) {
        // empty
    }
}
