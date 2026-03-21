<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQL\Printer\Blocks\Document;

use GraphQL\Language\AST\ScalarTypeExtensionNode;
use LastDragon_ru\GraphQL\Printer\Blocks\Types\DefinitionBlock;
use LastDragon_ru\GraphQL\Printer\Blocks\Types\ExtensionDefinitionBlock;
use LastDragon_ru\GraphQL\Printer\Misc\Context;
use LastDragon_ru\GraphQL\Printer\Package\GraphQLAstNode;
use Override;

/**
 * @internal
 *
 * @extends DefinitionBlock<ScalarTypeExtensionNode>
 */
#[GraphQLAstNode(ScalarTypeExtensionNode::class)]
class ScalarTypeExtension extends DefinitionBlock implements ExtensionDefinitionBlock {
    public function __construct(
        Context $context,
        ScalarTypeExtensionNode $definition,
    ) {
        parent::__construct($context, $definition);
    }

    #[Override]
    protected function prefix(): ?string {
        return 'extend scalar';
    }
}
