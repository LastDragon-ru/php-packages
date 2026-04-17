<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast\Factories;

use LastDragon_ru\GlobMatcher\Glob\Ast\Factory;
use LastDragon_ru\GlobMatcher\Glob\Ast\Node;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\GlobNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\GlobNodeChild;
use Override;

/**
 * @extends Factory<GlobNode, Node&GlobNodeChild>
 */
class GlobNodeFactory extends Factory {
    #[Override]
    protected function make(): ?object {
        return $this->children !== [] ? new GlobNode($this->children) : null;
    }
}
