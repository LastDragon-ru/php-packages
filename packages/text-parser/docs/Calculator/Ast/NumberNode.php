<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Docs\Calculator\Ast;

class NumberNode implements Node, ExpressionNodeChild {
    public function __construct(
        public readonly int $value,
    ) {
        // empty
    }
}
