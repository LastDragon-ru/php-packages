<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Ast;

readonly class NodeString {
    final public function __construct(
        /**
         * @var non-empty-string
         */
        public string $string,
    ) {
        // empty
    }
}
