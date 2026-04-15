<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast\Factories;

use LastDragon_ru\GlobMatcher\Glob\Ast\Node;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\GlobNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\GlobNodeChild;
use LastDragon_ru\TextParser\Ast\NodeFactory;
use Override;

/**
 * @extends NodeFactory<GlobNode, Node&GlobNodeChild>
 */
class GlobNodeFactory extends NodeFactory {
    #[Override]
    protected function onCreate(array $children): ?object {
        return $children !== [] ? new GlobNode($children) : null;
    }

    #[Override]
    protected function onPush(array $children, ?object $node): bool {
        return true;
    }
}
