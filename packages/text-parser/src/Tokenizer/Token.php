<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Tokenizer;

use BackedEnum;

/**
 * @template-covariant TName of BackedEnum
 */
readonly class Token {
    public function __construct(
        /**
         * @var TName|null
         */
        public ?BackedEnum $name,
        public string $value,
        public int $offset,
    ) {
        // empty
    }

    public function is(?BackedEnum $name): bool {
        return $this->name === $name;
    }
}
