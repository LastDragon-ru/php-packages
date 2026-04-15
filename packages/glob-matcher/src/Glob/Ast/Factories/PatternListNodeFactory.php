<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast\Factories;

use LastDragon_ru\GlobMatcher\Glob\Ast\Node;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\PatternListNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\PatternListNodeChild;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\PatternListQuantifier;
use LastDragon_ru\TextParser\Ast\NodeFactory;
use Override;

/**
 * @extends NodeFactory<PatternListNode, Node&PatternListNodeChild>
 */
class PatternListNodeFactory extends NodeFactory {
    public function __construct(
        protected PatternListQuantifier $quantifier,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function onCreate(array $children): ?object {
        return new PatternListNode($this->quantifier, $children);
    }

    #[Override]
    protected function onPush(array $children, ?object $node): bool {
        return true;
    }
}
