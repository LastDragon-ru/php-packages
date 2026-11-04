<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Parser;

use LastDragon_ru\TextParser\Iterables\StringTokenizeIterable;
use LastDragon_ru\TextParser\Iterables\TokenUnescapeIterable;
use LastDragon_ru\TextParser\Tokenizer\Token;

readonly class Tokenizer {
    public function __construct() {
        // empty
    }

    /**
     * @param iterable<mixed, string> $iterable
     *
     * @return iterable<mixed, Token<Name>>
     */
    public function tokenize(iterable $iterable, int $offset = 0): iterable {
        $iterable = new StringTokenizeIterable($iterable, Name::class, offset: $offset);
        $iterable = new TokenUnescapeIterable($iterable, Name::Backslash, static function ($token) {
            return $token->name !== Name::Slash;
        });

        foreach ($iterable as $key => $token) {
            if ($token->name === Name::Backslash) {
                continue;
            }

            yield $key => $token;
        }
    }
}
