<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQL\Printer\Blocks\Document;

use GraphQL\Language\AST\DocumentNode;
use LastDragon_ru\GraphQL\Printer\Blocks\Block;
use LastDragon_ru\GraphQL\Printer\Blocks\Types\DefinitionList;
use LastDragon_ru\GraphQL\Printer\Misc\Collector;
use LastDragon_ru\GraphQL\Printer\Misc\Context;
use LastDragon_ru\GraphQL\Printer\Package\GraphQLAstNode;
use Override;

/**
 * @internal
 */
#[GraphQLAstNode(DocumentNode::class)]
class Document extends Block {
    public function __construct(
        Context $context,
        private DocumentNode $document,
    ) {
        parent::__construct($context);
    }

    protected function getDocument(): DocumentNode {
        return $this->document;
    }

    #[Override]
    protected function content(Collector $collector, int $level, int $used): string {
        $context     = $this->getContext();
        $document    = $this->getDocument();
        $definitions = new DefinitionList($context, $document->definitions, static fn() => null);

        return $definitions->serialize($collector, $level, $used);
    }
}
