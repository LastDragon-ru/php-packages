<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQL\Printer\Blocks\Document;

use GraphQL\Language\AST\UnionTypeExtensionNode;
use LastDragon_ru\GraphQL\Printer\Blocks\Types\ExtensionDefinitionBlock;
use LastDragon_ru\GraphQL\Printer\Blocks\Types\UnionDefinitionBlock;
use LastDragon_ru\GraphQL\Printer\Misc\Context;
use LastDragon_ru\GraphQL\Printer\Package\GraphQLAstNode;
use Override;

/**
 * @internal
 *
 * @extends UnionDefinitionBlock<UnionTypeExtensionNode>
 */
#[GraphQLAstNode(UnionTypeExtensionNode::class)]
class UnionTypeExtension extends UnionDefinitionBlock implements ExtensionDefinitionBlock {
    public function __construct(
        Context $context,
        UnionTypeExtensionNode $definition,
    ) {
        parent::__construct($context, $definition);
    }

    #[Override]
    protected function prefix(): ?string {
        return 'extend union';
    }
}
