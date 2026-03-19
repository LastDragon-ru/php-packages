<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Docs\Calculator\Ast;

use LastDragon_ru\TextParser\Ast\NodeParentFactory;
use LogicException;
use Override;

use function array_last;

/**
 * @extends NodeParentFactory<ExpressionNode, ExpressionNodeChild>
 */
class ExpressionNodeFactory extends NodeParentFactory {
    #[Override]
    protected function onCreate(array $children): ?object {
        // Expression cannot be empty
        return $children !== [] ? new ExpressionNode($children) : null;
    }

    #[Override]
    protected function onPush(array $children, ?object $node): bool {
        // Operator is always allowed
        if ($node instanceof OperatorNode) {
            return true;
        }

        // Other nodes must be separated by any Operator
        $previous = array_last($children);
        $valid    = $previous === null || $previous instanceof OperatorNode;

        if (!$valid) {
            throw new LogicException('Operator is missing.');
        }

        return true;
    }
}
