<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Formats\Php;

use PhpParser\NameContext;
use PhpParser\Node\Stmt;

class Content {
    public function __construct(
        /**
         * @var array<array-key, Stmt>
         */
        public readonly array $stmts,
        public readonly NameContext $context,
    ) {
        // empty
    }
}
