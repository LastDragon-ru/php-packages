<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast\Factories;

use LastDragon_ru\GlobMatcher\Glob\Ast\Factory;
use LastDragon_ru\GlobMatcher\Glob\Ast\Node;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\CharacterNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\CharacterNodeChild;
use Override;

/**
 * @extends Factory<CharacterNode, Node&CharacterNodeChild>
 */
class CharacterNodeFactory extends Factory {
    public function __construct(
        protected bool $negated,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function make(): ?object {
        return $this->children !== [] ? new CharacterNode($this->negated, $this->children) : null;
    }
}
