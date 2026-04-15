<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast\Factories;

use LastDragon_ru\GlobMatcher\Glob\Ast\Node;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\CharacterNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\CharacterNodeChild;
use LastDragon_ru\TextParser\Ast\NodeFactory;
use Override;

/**
 * @extends NodeFactory<CharacterNode, Node&CharacterNodeChild>
 */
class CharacterNodeFactory extends NodeFactory {
    public function __construct(
        protected bool $negated,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function onCreate(array $children): ?object {
        return $children !== [] ? new CharacterNode($this->negated, $children) : null;
    }

    #[Override]
    protected function onPush(array $children, ?object $node): bool {
        return true;
    }
}
