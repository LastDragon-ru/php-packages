<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast\Nodes;

use LastDragon_ru\GlobMatcher\Glob\Ast\Node;

readonly class CharacterCollatingSymbolNode implements Node, CharacterNodeChild {
    public function __construct(
        public string $symbol,
    ) {
        // empty
    }
}
