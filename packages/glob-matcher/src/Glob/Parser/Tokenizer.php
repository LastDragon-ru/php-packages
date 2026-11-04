<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Parser;

use LastDragon_ru\TextParser\Iterables\TokenUnescapeIterable;
use LastDragon_ru\TextParser\Tokenizer\Tokenizer as TextParserTokenizer;
use Override;

/**
 * @extends TextParserTokenizer<Name>
 */
readonly class Tokenizer extends TextParserTokenizer {
    public function __construct() {
        parent::__construct(Name::class);
    }

    #[Override]
    public function tokenize(iterable $iterable, int $offset = 0): iterable {
        $iterable = parent::tokenize($iterable, $offset);
        $iterable = new TokenUnescapeIterable($iterable, Name::Backslash, static function ($token) {
            return $token->name !== Name::Slash;
        });

        return $iterable;
    }
}
