<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Parser;

use LastDragon_ru\GlobMatcher\Package\TestCase;
use LastDragon_ru\TextParser\Tokenizer\Token;
use PHPUnit\Framework\Attributes\CoversClass;

use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(Tokenizer::class)]
final class TokenizerTest extends TestCase {
    public function testTokenize(): void {
        $tokenizer = new Tokenizer();
        $source    = ['a/b\\/c*\\d\\*e'];
        $expected  = [
            new Token(null, 'a', 0),
            new Token(Lexeme::Slash, '/', 1),
            new Token(null, 'b', 2),
            new Token(Lexeme::Backslash, '\\', 3),
            new Token(Lexeme::Slash, '/', 4),
            new Token(null, 'c', 5),
            new Token(Lexeme::Asterisk, '*', 6),
            new Token(Lexeme::Backslash, '\\', 7),
            new Token(null, 'd', 8),
            new Token(null, '*', 10),
            new Token(null, 'e', 11),
        ];

        self::assertEquals($expected, iterator_to_array($tokenizer->tokenize($source), false));
    }
}
