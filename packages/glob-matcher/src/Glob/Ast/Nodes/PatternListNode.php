<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast\Nodes;

use LastDragon_ru\GlobMatcher\Glob\Ast\Node;
use LastDragon_ru\GlobMatcher\Glob\Ast\NodeParent;

/**
 * @implements NodeParent<Node&PatternListNodeChild>
 */
readonly class PatternListNode implements Node, NodeParent, NameNodeChild {
    public function __construct(
        public PatternListQuantifier $quantifier,
        /**
         * @var list<Node&PatternListNodeChild>
         */
        public array $children,
    ) {
        // empty
    }
}
