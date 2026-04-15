<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Ast\Factories;

use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Node;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Nodes\BraceExpansionNode;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Nodes\BraceExpansionNodeChild;
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
