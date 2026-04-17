<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Docs\Calculator\Ast;

use LastDragon_ru\TextParser\Ast\NodeFactory;
use LogicException;
use Override;

use function array_last;

/**
 * @extends NodeFactory<ExpressionNode, Node&ExpressionNodeChild>
 */
class ExpressionNodeFactory extends NodeFactory {
    #[Override]
    protected function make(): ?object {
        // Expression cannot be empty
        return $this->children !== [] ? new ExpressionNode($this->children) : null;
    }

    #[Override]
    protected function valid(object $node): bool {
        // Operator is always allowed
        if ($node instanceof OperatorNode) {
            return true;
        }

        // Other nodes must be separated by any Operator
        $previous = array_last($this->children);
        $valid    = $previous === null || $previous instanceof OperatorNode;

        if (!$valid) {
            throw new LogicException('Operator is missing.');
        }

        return true;
    }
}
