<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Parser\Factories;

use LastDragon_ru\GlobMatcher\BraceExpander\Ast\BraceExpansionNode;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\BraceExpansionNodeChild;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Node;
use LastDragon_ru\TextParser\Ast\NodeFactory;
use Override;

/**
 * @extends NodeFactory<BraceExpansionNode, Node&BraceExpansionNodeChild>
 */
class BraceExpansionNodeFactory extends NodeFactory {
    #[Override]
    protected function onCreate(array $children): ?object {
        return new BraceExpansionNode($children);
    }

    #[Override]
    protected function onPush(array $children, ?object $node): bool {
        return true;
    }
}
