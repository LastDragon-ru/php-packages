<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast\Nodes;

use LastDragon_ru\GlobMatcher\Glob\Ast\Node;
use LastDragon_ru\TextParser\Ast\NodeString;

readonly class StringNode extends NodeString implements Node, NameNodeChild, CharacterNodeChild {
    // empty
}
