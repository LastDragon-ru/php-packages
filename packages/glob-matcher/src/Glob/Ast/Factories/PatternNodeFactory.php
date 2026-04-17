<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast\Factories;

use LastDragon_ru\GlobMatcher\Glob\Ast\Factory;
use LastDragon_ru\GlobMatcher\Glob\Ast\Node;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\NameNodeChild;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\PatternNode;
use Override;

/**
 * @extends Factory<PatternNode, Node&NameNodeChild>
 */
class PatternNodeFactory extends Factory {
    #[Override]
    protected function make(): ?object {
        return $this->children !== [] ? new PatternNode($this->children) : null;
    }
}
