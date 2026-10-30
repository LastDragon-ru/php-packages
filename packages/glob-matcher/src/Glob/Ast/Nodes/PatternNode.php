<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast\Nodes;

use LastDragon_ru\GlobMatcher\Glob\Ast\Node;
use LastDragon_ru\GlobMatcher\Glob\Ast\NodeParent;

/**
 * @implements NodeParent<Node&NameNodeChild>
 */
readonly class PatternNode implements Node, NodeParent, PatternListNodeChild {
    public function __construct(
        /**
         * @var list<Node&NameNodeChild>
         */
        public array $children,
    ) {
        // empty
    }
}
