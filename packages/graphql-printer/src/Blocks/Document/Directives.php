<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQL\Printer\Blocks\Document;

use GraphQL\Language\AST\DirectiveNode;
use LastDragon_ru\GraphQL\Printer\Blocks\ListBlock;
use Override;

/**
 * @internal
 * @extends ListBlock<Directive, array-key, DirectiveNode>
 */
class Directives extends ListBlock {
    #[Override]
    protected function getSeparator(): string {
        return $this->space();
    }

    #[Override]
    protected function isNormalized(): bool {
        return $this->getSettings()->isNormalizeDirectives();
    }

    #[Override]
    protected function isAlwaysMultiline(): bool {
        return parent::isAlwaysMultiline()
            || $this->getSettings()->isAlwaysMultilineDirectives();
    }

    #[Override]
    protected function block(string|int $key, mixed $item): Directive {
        return new Directive($this->getContext(), $item);
    }
}
