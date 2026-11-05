<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Tokenizer;

use BackedEnum;

/**
 * @template-covariant TLexeme of BackedEnum
 */
readonly class Token {
    public function __construct(
        /**
         * @var TLexeme|null
         */
        public ?BackedEnum $name,
        public string $value,
        public int $offset,
    ) {
        // empty
    }
}
