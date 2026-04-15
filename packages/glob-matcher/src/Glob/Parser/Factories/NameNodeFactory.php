<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Parser\Factories;

use LastDragon_ru\GlobMatcher\Glob\Ast\NameNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\NameNodeChild;
use LastDragon_ru\GlobMatcher\Glob\Ast\Node;
use LastDragon_ru\TextParser\Ast\NodeFactory;
use Override;

/**
 * @extends NodeFactory<NameNode, Node&NameNodeChild>
 */
class NameNodeFactory extends NodeFactory {
    #[Override]
    protected function onCreate(array $children): ?object {
        return $children !== [] ? new NameNode($children) : null;
    }

    #[Override]
    protected function onPush(array $children, ?object $node): bool {
        return true;
    }
}
