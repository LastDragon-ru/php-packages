<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Ast\Nodes;

use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Node;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\NodeParent;

/**
 * @implements NodeParent<Node&BraceExpansionNodeChild>
 */
readonly class BraceExpansionNode implements Node, NodeParent, SequenceNodeChild {
    public function __construct(
        /**
         * @var list<Node&BraceExpansionNodeChild>
         */
        public array $children,
    ) {
        // empty
    }
}
