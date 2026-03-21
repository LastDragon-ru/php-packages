<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQL\Printer\Blocks\Document;

use GraphQL\Language\AST\SchemaExtensionNode;
use LastDragon_ru\GraphQL\Printer\Blocks\Block;
use LastDragon_ru\GraphQL\Printer\Blocks\Types\DefinitionBlock;
use LastDragon_ru\GraphQL\Printer\Blocks\Types\ExtensionDefinitionBlock;
use LastDragon_ru\GraphQL\Printer\Misc\Context;
use LastDragon_ru\GraphQL\Printer\Package\GraphQLAstNode;
use Override;

/**
 * @internal
 *
 * @extends DefinitionBlock<SchemaExtensionNode>
 */
#[GraphQLAstNode(SchemaExtensionNode::class)]
class SchemaExtension extends DefinitionBlock implements ExtensionDefinitionBlock {
    public function __construct(
        Context $context,
        SchemaExtensionNode $definition,
    ) {
        parent::__construct($context, $definition);
    }

    #[Override]
    protected function prefix(): ?string {
        return 'extend schema';
    }

    #[Override]
    protected function fields(bool $multiline): ?Block {
        $definition = $this->getDefinition();
        $operations = [];

        foreach ($definition->operationTypes as $operation) {
            $operations[$operation->operation] = $operation->type;
        }

        return new RootOperationTypesDefinition(
            $this->getContext(),
            $operations,
        );
    }
}
