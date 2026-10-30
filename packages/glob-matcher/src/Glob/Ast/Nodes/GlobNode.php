<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast\Nodes;

use LastDragon_ru\GlobMatcher\Glob\Ast\Node;
use LastDragon_ru\GlobMatcher\Glob\Ast\NodeParent;

/**
 * @implements NodeParent<Node&GlobNodeChild>
 */
readonly class GlobNode implements Node, NodeParent {
    public function __construct(
        /**
         * @var list<Node&GlobNodeChild>
         */
        public array $children,
    ) {
        // empty
    }
}
