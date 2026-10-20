<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Docs\Calculator\Ast\Nodes;

use LastDragon_ru\TextParser\Docs\Calculator\Ast\Node;

abstract readonly class OperatorNode implements Node, ExpressionNodeChild {
    public function __construct() {
        // empty
    }

    abstract public function priority(): int;

    abstract public function calculate(float|int $left, float|int $right): float|int;
}
