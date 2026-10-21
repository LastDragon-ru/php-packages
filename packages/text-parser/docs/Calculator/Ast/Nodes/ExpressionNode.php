<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Docs\Calculator\Ast\Nodes;

use LastDragon_ru\TextParser\Docs\Calculator\Ast\Node;
use LastDragon_ru\TextParser\Docs\Calculator\Ast\NodeParent;

/**
 * @implements NodeParent<Node&ExpressionNodeChild>
 */
readonly class ExpressionNode implements Node, NodeParent, ExpressionNodeChild {
    public function __construct(
        /**
         * @var list<Node&ExpressionNodeChild>
         */
        public array $children,
    ) {
        // empty
    }
}
