<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Parser\Factories;

use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Node;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\SequenceNode;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\SequenceNodeChild;
use LastDragon_ru\TextParser\Ast\NodeFactory;
use Override;

use function count;

/**
 * @extends NodeFactory<SequenceNode, Node&SequenceNodeChild>
 */
class SequenceNodeFactory extends NodeFactory {
    #[Override]
    protected function onCreate(array $children): ?object {
        return count($children) > 1 ? new SequenceNode($children) : null;
    }

    #[Override]
    protected function onPush(array $children, ?object $node): bool {
        return true;
    }
}
