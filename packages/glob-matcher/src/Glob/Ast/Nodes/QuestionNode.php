<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast\Nodes;

use LastDragon_ru\GlobMatcher\Glob\Ast\Node;

readonly class QuestionNode implements Node, NameNodeChild {
    public function __construct() {
        // empty
    }
}
