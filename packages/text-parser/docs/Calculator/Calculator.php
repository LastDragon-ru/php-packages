<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Docs\Calculator;

use ArithmeticError;
use LastDragon_ru\TextParser\Docs\Calculator\Ast\Nodes\ExpressionNode;
use LastDragon_ru\TextParser\Docs\Calculator\Ast\Nodes\NumberNode;
use LastDragon_ru\TextParser\Docs\Calculator\Ast\Nodes\OperatorAdditionNode;
use LastDragon_ru\TextParser\Docs\Calculator\Ast\Nodes\OperatorDivisionNode;
use LastDragon_ru\TextParser\Docs\Calculator\Ast\Nodes\OperatorMultiplicationNode;
use LastDragon_ru\TextParser\Docs\Calculator\Ast\Nodes\OperatorNode;
use LastDragon_ru\TextParser\Docs\Calculator\Ast\Nodes\OperatorSubtractionNode;

use function array_last;
use function array_pop;
use function count;

use const NAN;

class Calculator {
    public function calculate(string $expression): float|int {
        // Parse
        $node = (new Parser())->parse($expression);

        if ($node === null) {
            return NAN;
        }

        // Process
        $stack = [];

        foreach ($this->rpn($node) as $item) {
            if ($item instanceof OperatorNode) {
                $b = array_pop($stack) ?? NAN;
                $a = array_pop($stack) ?? NAN;

                try {
                    $stack[] = $this->op($item, $a, $b);
                } catch (ArithmeticError) {
                    $stack[] = NAN;
                }
            } else {
                $stack[] = $item;
            }
        }

        return count($stack) === 1
            ? array_last($stack)
            : NAN;
    }

    protected function op(OperatorNode $node, float|int $a, float|int $b): float|int {
        return match (true) {
            $node instanceof OperatorAdditionNode       => $a + $b,
            $node instanceof OperatorSubtractionNode    => $a - $b,
            $node instanceof OperatorMultiplicationNode => $a * $b,
            $node instanceof OperatorDivisionNode       => $a / $b,
            default                                     => NAN,
        };
    }

    /**
     * @see https://en.wikipedia.org/wiki/Reverse_Polish_notation
     * @see https://en.wikipedia.org/wiki/Shunting_yard_algorithm
     *
     * @return iterable<int, OperatorNode|float|int>
     */
    protected function rpn(ExpressionNode $node): iterable {
        $unary = true;
        $stack = [];

        foreach ($node->children as $child) {
            // Unary?
            if ($child instanceof OperatorNode) {
                if ($unary) {
                    yield 0;
                }

                $unary = true;
            } else {
                $unary = false;
            }

            // Process
            if ($child instanceof OperatorNode) {
                while ($stack !== [] && $this->compare(array_last($stack), $child) >= 0) {
                    yield array_pop($stack);
                }

                $stack[] = $child;
            } elseif ($child instanceof ExpressionNode) {
                yield from $this->rpn($child);
            } elseif ($child instanceof NumberNode) {
                yield $child->value;
            } else {
                yield NAN;
            }
        }

        for ($i = count($stack) - 1; $i >= 0; $i--) {
            yield $stack[$i];
        }

        yield from [];
    }

    protected function compare(OperatorNode $a, OperatorNode $b): int {
        return $this->precedence($a) <=> $this->precedence($b);
    }

    protected function precedence(OperatorNode $node): int {
        return match (true) {
            $node instanceof OperatorMultiplicationNode => 1,
            $node instanceof OperatorDivisionNode       => 1,
            default                                     => 0,
        };
    }
}
