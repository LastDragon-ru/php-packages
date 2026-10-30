<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast\Nodes;

use LastDragon_ru\GlobMatcher\Glob\Ast\Node;
use LastDragon_ru\GlobMatcher\Glob\Ast\NodeParent;

/**
 * @implements NodeParent<Node&CharacterNodeChild>
 */
readonly class CharacterNode implements Node, NodeParent, NameNodeChild {
    public function __construct(
        public bool $negated,
        /**
         * @var list<Node&CharacterNodeChild>
         */
        public array $children,
    ) {
        // empty
    }
}
