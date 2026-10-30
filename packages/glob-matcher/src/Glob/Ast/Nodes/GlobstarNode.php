<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast\Nodes;

use LastDragon_ru\GlobMatcher\Glob\Ast\Node;

readonly class GlobstarNode implements Node, GlobNodeChild {
    final public function __construct(
        /**
         * @var positive-int
         */
        public int $count = 1,
    ) {
        // empty
    }
}
