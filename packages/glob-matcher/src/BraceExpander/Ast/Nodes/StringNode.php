<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Ast\Nodes;

use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Cursor;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Node;
use LastDragon_ru\TextParser\Ast\NodeString;
use Override;

readonly class StringNode extends NodeString implements Node, BraceExpansionNodeChild {
    #[Override]
    public static function toIterable(Cursor $cursor): iterable {
        return [$cursor->node->string];
    }
}
