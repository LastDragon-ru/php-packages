<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Tokenizer;

use BackedEnum;
use Override;
use Stringable;

/**
 * @template-covariant TName of BackedEnum
 */
readonly class Token implements Stringable {
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

    #[Override]
    public function __toString(): string {
        return $this->value;
    }
}
