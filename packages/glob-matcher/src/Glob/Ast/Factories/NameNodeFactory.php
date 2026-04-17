<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast\Factories;

use LastDragon_ru\GlobMatcher\Glob\Ast\Factory;
use LastDragon_ru\GlobMatcher\Glob\Ast\Node;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\NameNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\NameNodeChild;
use Override;

/**
 * @extends Factory<NameNode, Node&NameNodeChild>
 */
class NameNodeFactory extends Factory {
    #[Override]
    protected function make(): ?object {
        return $this->children !== [] ? new NameNode($this->children) : null;
    }
}
