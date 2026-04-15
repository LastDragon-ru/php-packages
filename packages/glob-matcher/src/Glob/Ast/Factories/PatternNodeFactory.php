<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast\Factories;

use LastDragon_ru\GlobMatcher\Glob\Ast\Node;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\NameNodeChild;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\PatternNode;
use LastDragon_ru\TextParser\Ast\NodeFactory;
use Override;

/**
 * @extends NodeFactory<PatternNode, Node&NameNodeChild>
 */
class PatternNodeFactory extends NodeFactory {
    #[Override]
    protected function onCreate(array $children): ?object {
        return $children !== [] ? new PatternNode($children) : null;
    }

    #[Override]
    protected function onPush(array $children, ?object $node): bool {
        return true;
    }
}
