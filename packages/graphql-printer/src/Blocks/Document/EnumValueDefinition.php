<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQL\Printer\Blocks\Document;

use GraphQL\Language\AST\EnumValueDefinitionNode;
use GraphQL\Type\Definition\EnumValueDefinition as GraphQLEnumValueDefinition;
use LastDragon_ru\GraphQL\Printer\Blocks\Types\DefinitionBlock;
use LastDragon_ru\GraphQL\Printer\Misc\Context;
use LastDragon_ru\GraphQL\Printer\Package\GraphQLAstNode;
use LastDragon_ru\GraphQL\Printer\Package\GraphQLDefinition;

/**
 * @internal
 * @extends DefinitionBlock<EnumValueDefinitionNode|GraphQLEnumValueDefinition>
 */
#[GraphQLAstNode(EnumValueDefinitionNode::class)]
#[GraphQLDefinition(GraphQLEnumValueDefinition::class)]
class EnumValueDefinition extends DefinitionBlock {
    public function __construct(
        Context $context,
        EnumValueDefinitionNode|GraphQLEnumValueDefinition $value,
    ) {
        parent::__construct($context, $value);
    }
}
