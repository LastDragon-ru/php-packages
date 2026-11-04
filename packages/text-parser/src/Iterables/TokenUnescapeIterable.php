<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Iterables;

use BackedEnum;
use Closure;
use IteratorAggregate;
use LastDragon_ru\TextParser\Tokenizer\Token;
use Override;
use Traversable;

/**
 * Converts escaped tokens into string.
 *
 * @template TToken of BackedEnum
 *
 * @implements IteratorAggregate<int, Token<TToken>>
 */
readonly class TokenUnescapeIterable implements IteratorAggregate {
    public function __construct(
        /**
         * @var iterable<mixed, Token<TToken>>
         */
        protected iterable $iterable,
        /**
         * @var TToken
         */
        protected BackedEnum $escape,
        /**
         * @var Closure(Token<TToken>): bool
         */
        protected ?Closure $escapable = null,
    ) {
        // empty
    }

    #[Override]
    public function getIterator(): Traversable {
        $escaped = null;

        foreach ($this->iterable as $token) {
            if ($escaped !== null) {
                if ($token->name !== null && ($this->escapable === null || ($this->escapable)($token))) {
                    /** @phpstan-ignore generator.valueType (`null` is fine) */
                    yield new Token(null, $token->value, $token->offset);
                } else {
                    yield $escaped;
                    yield $token;
                }

                $escaped = null;
            } elseif ($token->name === $this->escape) {
                $escaped = $token;
            } else {
                yield $token;
            }
        }
    }
}
