<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Iterables;

use BackedEnum;
use Closure;
use IteratorAggregate;
use LastDragon_ru\TextParser\Tokenizer\Token;
use Override;
use Traversable;

/**
 * Escapes tokens (adds {@see self::$escape} before each token).
 *
 * @template TToken of BackedEnum
 *
 * @implements IteratorAggregate<int, Token<TToken>>
 */
readonly class TokenEscapeIterable implements IteratorAggregate {
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
        foreach ($this->iterable as $token) {
            if ($token->name !== null && ($this->escapable === null || ($this->escapable)($token))) {
                yield new Token($this->escape, (string) $this->escape->value, $token->offset);
            }

            yield $token;
        }
    }
}
