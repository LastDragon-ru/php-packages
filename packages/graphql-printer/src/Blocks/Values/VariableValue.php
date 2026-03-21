<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQL\Printer\Blocks\Values;

use GraphQL\Language\AST\VariableNode;
use LastDragon_ru\GraphQL\Printer\Blocks\Block;
use LastDragon_ru\GraphQL\Printer\Blocks\NamedBlock;
use LastDragon_ru\GraphQL\Printer\Misc\Collector;
use LastDragon_ru\GraphQL\Printer\Misc\Context;
use Override;

/**
 * @internal
 */
class VariableValue extends Block implements NamedBlock {
    public function __construct(
        Context $context,
        protected VariableNode $node,
    ) {
        parent::__construct($context);
    }

    #[Override]
    public function getName(): string {
        return $this->node->name->value;
    }

    #[Override]
    protected function content(Collector $collector, int $level, int $used): string {
        return "\${$this->getName()}";
    }
}
