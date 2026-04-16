<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Ast;

use Override;

readonly class NodeString implements NodeMergeable {
    final public function __construct(
        /**
         * @var non-empty-string
         */
        public string $string,
    ) {
        // empty
    }

    #[Override]
    public static function merge(NodeMergeable $previous, NodeMergeable $current): NodeMergeable {
        if ($previous::class === $current::class) {
            $current = new static($previous->string.$current->string);
        }

        return $current; // @phpstan-ignore return.type (fixme)
    }
}
