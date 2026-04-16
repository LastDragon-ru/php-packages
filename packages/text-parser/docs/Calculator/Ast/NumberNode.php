<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Docs\Calculator\Ast;

readonly class NumberNode implements Node, ExpressionNodeChild {
    public function __construct(
        public int $value,
    ) {
        // empty
    }
}
