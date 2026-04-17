<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast\Factories;

use LastDragon_ru\GlobMatcher\Glob\Ast\Factory;
use LastDragon_ru\GlobMatcher\Glob\Ast\Node;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\PatternListNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\PatternListNodeChild;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\PatternListQuantifier;
use Override;

/**
 * @extends Factory<PatternListNode, Node&PatternListNodeChild>
 */
class PatternListNodeFactory extends Factory {
    public function __construct(
        protected PatternListQuantifier $quantifier,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function make(): ?object {
        return new PatternListNode($this->quantifier, $this->children);
    }
}
