<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Ast\Factories;

use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Node;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Nodes\SequenceNode;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Nodes\SequenceNodeChild;
use LastDragon_ru\TextParser\Ast\NodeFactory;
use Override;

use function count;

/**
 * @extends NodeFactory<SequenceNode, Node&SequenceNodeChild>
 */
class SequenceNodeFactory extends NodeFactory {
    #[Override]
    protected function make(): ?object {
        return count($this->children) > 1 ? new SequenceNode($this->children) : null;
    }
}
