<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQL\Printer\Blocks\Document;

use GraphQL\Language\AST\EnumValueDefinitionNode;
use GraphQL\Type\Definition\EnumValueDefinition as GraphQLEnumValueDefinition;
use LastDragon_ru\GraphQL\Printer\Blocks\Block;
use LastDragon_ru\GraphQL\Printer\Blocks\ObjectBlockList;
use Override;

/**
 * @internal
 * @extends ObjectBlockList<EnumValueDefinition, array-key, EnumValueDefinitionNode|GraphQLEnumValueDefinition>
 */
class EnumValuesDefinition extends ObjectBlockList {
    #[Override]
    protected function isWrapped(): bool {
        return true;
    }

    #[Override]
    protected function isNormalized(): bool {
        return $this->getSettings()->isNormalizeEnums();
    }

    #[Override]
    protected function isAlwaysMultiline(): bool {
        return true;
    }

    #[Override]
    protected function block(string|int $key, mixed $item): Block {
        return new EnumValueDefinition(
            $this->getContext(),
            $item,
        );
    }
}
