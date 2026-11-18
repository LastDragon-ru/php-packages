<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Tokenizer;

use ArrayAccess;
use BackedEnum;
use LastDragon_ru\TextParser\Iterables\TransformIterable;
use LastDragon_ru\TextParser\Package;
use WeakMap;

use function array_keys;
use function mb_strtolower;

/**
 * @template TLexeme of BackedEnum
 */
readonly class Tokenizer {
    /**
     * @var array<non-empty-string, TLexeme>
     */
    protected array $lexemes;
    /**
     * @var ArrayAccess<TLexeme, TLexeme>
     */
    protected ArrayAccess $aliases;
    protected Splitter    $splitter;

    /**
     * @param list<class-string<TLexeme>>|class-string<TLexeme> $lexemes
     * @param ?TLexeme                                          $unknown
     * @param ?positive-int                                     $buffer
     */
    public function __construct(
        array|string $lexemes,
        protected ?BackedEnum $unknown = null,
        protected bool $caseSensitive = true,
        protected ?int $buffer = null,
    ) {
        [$this->lexemes, $this->aliases] = $this->prepare((array) $lexemes, $this->caseSensitive);
        $this->splitter                  = new Splitter(
            array_keys($this->lexemes),
            $this->caseSensitive,
            $this->buffer,
        );
    }

    /**
     * @param TLexeme $alias
     *
     * @return TLexeme
     */
    public function name(BackedEnum $alias): BackedEnum {
        return $this->aliases[$alias] ?? $alias;
    }

    /**
     * @param iterable<mixed, string> $iterable
     *
     * @return iterable<mixed, Token<TLexeme>>
     */
    public function tokenize(iterable $iterable, int $offset = 0): iterable {
        return new TransformIterable(
            $this->splitter->split($iterable, $offset),
            $this->caseSensitive ? $this->tcs(...) : $this->tci(...),
        );
    }

    /**
     * @param list<class-string<TLexeme>> $enums
     *
     * @return array{array<non-empty-string, TLexeme>, ArrayAccess<TLexeme, TLexeme>}
     */
    private function prepare(array $enums, bool $caseSensitive): array {
        // todo(parser): Need to warning/error if
        //      * values have intersections, eg `(` and `!(` (the shortest value will be lost)
        //      * empty value (will be ignored)

        $lexemes  = [];
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
                $value = (string) $case->value;

                if (!isset($lexemes[$value])) {
                    $lexemes[$value] = $case;

                    if (!$caseSensitive) {
                        $lexemes[mb_strtolower($value, Package::Encoding)] = $case;
                    }
                } else {
                    $aliases[$case] = $lexemes[$value];
                }
            }
        }

        unset($lexemes['']);

        return [$lexemes, $aliases];
    }

    /**
     * @return Token<TLexeme>
     */
    private function tcs(string $lexeme, int $offset): Token {
        return new Token(
            $this->lexemes[$lexeme] ?? $this->unknown,
            $lexeme,
            $offset,
        );
    }

    /**
     * @return Token<TLexeme>
     */
    private function tci(string $lexeme, int $offset): Token {
        return new Token(
            $this->lexemes[$lexeme] ?? $this->lexemes[mb_strtolower($lexeme, Package::Encoding)] ?? $this->unknown,
            $lexeme,
            $offset,
        );
    }
}
