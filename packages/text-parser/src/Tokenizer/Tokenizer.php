<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Tokenizer;

use ArrayAccess;
use BackedEnum;
use LastDragon_ru\TextParser\Iterables\StringSplitIterable;
use LastDragon_ru\TextParser\Iterables\TokenUnescapeIterable;
use LastDragon_ru\TextParser\Iterables\TransformIterable;
use LastDragon_ru\TextParser\Package;
use WeakMap;

use function array_keys;
use function mb_strtolower;
use function mb_strtoupper;

/**
 * @template TToken of BackedEnum
 */
readonly class Tokenizer {
    /**
     * @var array<non-empty-string, TToken>
     */
    protected array $tokens;
    /**
     * @var ArrayAccess<TToken, TToken>
     */
    protected ArrayAccess $aliases;

    /**
     * @param list<class-string<TToken>>|class-string<TToken> $tokens
     * @param ?TToken                                         $escape
     * @param ?positive-int                                   $buffer
     */
    public function __construct(
        array|string $tokens,
        protected ?BackedEnum $escape = null,
        protected ?int $buffer = null,
        protected bool $caseSensitive = true,
    ) {
        [$this->tokens, $this->aliases] = $this->prepare((array) $tokens, $this->caseSensitive);
    }

    /**
     * @param TToken $alias
     *
     * @return TToken
     */
    public function name(BackedEnum $alias): BackedEnum {
        return $this->aliases[$alias] ?? $alias;
    }

    /**
     * @param iterable<mixed, string> $iterable
     *
     * @return iterable<mixed, Token<TToken>>
     */
    public function tokenize(iterable $iterable, int $offset = 0): iterable {
        $iterable = new StringSplitIterable(
            $iterable,
            array_keys($this->tokens),
            $this->buffer,
            $offset,
            $this->caseSensitive,
        );
        $iterable = new TransformIterable($iterable, function ($token, $offset) {
            return new Token($this->tokens[$token] ?? null, $token, $offset);
        });

        if ($this->escape !== null) {
            $iterable = new TokenUnescapeIterable($iterable, $this->escape);
        }

        return $iterable;
    }

    /**
     * @param list<class-string<TToken>> $enums
     *
     * @return array{array<non-empty-string, TToken>, ArrayAccess<TToken, TToken>}
     */
    private function prepare(array $enums, bool $caseSensitive): array {
        // todo(parser): Need to warning/error if
        //      * values have intersections, eg `(` and `!(` (the shortest value will be lost)
        //      * empty value (will be ignored)

        $tokens   = [];
        $aliases  = new WeakMap();
        $prepared = [];

        foreach ($enums as $enum) {
            // Duplicate?
            if (isset($prepared[$enum])) {
                continue;
            }

            $prepared[$enum] = true;

            // Save
            foreach ($enum::cases() as $case) {
                // Save case and aliases
                $value = (string) $case->value;

                if (!isset($tokens[$value])) {
                    $tokens[$value] = $case;
                } else {
                    $aliases[$case] = $tokens[$value];
                }

                // If case doesn't matter, then we save both
                if ($caseSensitive === false) {
                    $tokens[mb_strtolower($value, Package::Encoding)] = $case;
                    $tokens[mb_strtoupper($value, Package::Encoding)] = $case;
                }
            }
        }

        unset($tokens['']);

        return [$tokens, $aliases];
    }
}
