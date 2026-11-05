<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Iterables;

use LastDragon_ru\TextParser\Package\TestCase;
use LastDragon_ru\TextParser\Tokenizer\Token;
use PHPUnit\Framework\Attributes\CoversClass;

use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(TokenEscapeIterable::class)]
final class TokenEscapeIterableTest extends TestCase {
    public function testGetIterator(): void {
        $tokens   = [
            new Token(null, 'a', 0),
            new Token(TokenEscapeIterableTest_Lexeme::Asterisk, '*', 1),
            new Token(TokenEscapeIterableTest_Lexeme::Backslash, '\\', 2),
            new Token(TokenEscapeIterableTest_Lexeme::Asterisk, '*', 3),
            new Token(null, 'b', 4),
            new Token(TokenEscapeIterableTest_Lexeme::Backslash, '\\', 5),
            new Token(TokenEscapeIterableTest_Lexeme::Backslash, '\\', 6),
            new Token(TokenEscapeIterableTest_Lexeme::Slash, '/', 7),
            new Token(TokenEscapeIterableTest_Lexeme::Backslash, '\\', 8),
            new Token(null, 'c', 9),
            new Token(TokenEscapeIterableTest_Lexeme::Backslash, '\\', 10),
        ];
        $iterable = new TokenEscapeIterable(
            $tokens,
            TokenEscapeIterableTest_Lexeme::Backslash,
        );

        self::assertEquals(
            [
                new Token(null, 'a', 0),
                new Token(TokenEscapeIterableTest_Lexeme::Backslash, '\\', 1),
                new Token(TokenEscapeIterableTest_Lexeme::Asterisk, '*', 1),
                new Token(TokenEscapeIterableTest_Lexeme::Backslash, '\\', 2),
                new Token(TokenEscapeIterableTest_Lexeme::Backslash, '\\', 2),
                new Token(TokenEscapeIterableTest_Lexeme::Backslash, '\\', 3),
                new Token(TokenEscapeIterableTest_Lexeme::Asterisk, '*', 3),
                new Token(null, 'b', 4),
                new Token(TokenEscapeIterableTest_Lexeme::Backslash, '\\', 5),
                new Token(TokenEscapeIterableTest_Lexeme::Backslash, '\\', 5),
                new Token(TokenEscapeIterableTest_Lexeme::Backslash, '\\', 6),
                new Token(TokenEscapeIterableTest_Lexeme::Backslash, '\\', 6),
                new Token(TokenEscapeIterableTest_Lexeme::Backslash, '\\', 7),
                new Token(TokenEscapeIterableTest_Lexeme::Slash, '/', 7),
                new Token(TokenEscapeIterableTest_Lexeme::Backslash, '\\', 8),
                new Token(TokenEscapeIterableTest_Lexeme::Backslash, '\\', 8),
                new Token(null, 'c', 9),
                new Token(TokenEscapeIterableTest_Lexeme::Backslash, '\\', 10),
                new Token(TokenEscapeIterableTest_Lexeme::Backslash, '\\', 10),
            ],
            iterator_to_array($iterable, false),
        );
    }

    public function testGetIteratorEscapable(): void {
        $tokens   = [
            new Token(null, 'a', 0),
            new Token(TokenEscapeIterableTest_Lexeme::Asterisk, '*', 1),
            new Token(TokenEscapeIterableTest_Lexeme::Backslash, '\\', 2),
            new Token(TokenEscapeIterableTest_Lexeme::Slash, '/', 3),
        ];
        $iterable = new TokenEscapeIterable(
            $tokens,
            TokenEscapeIterableTest_Lexeme::Backslash,
            static fn ($token) => $token->name !== TokenEscapeIterableTest_Lexeme::Asterisk,
        );

        self::assertEquals(
            [
                new Token(null, 'a', 0),
                new Token(TokenEscapeIterableTest_Lexeme::Asterisk, '*', 1),
                new Token(TokenEscapeIterableTest_Lexeme::Backslash, '\\', 2),
                new Token(TokenEscapeIterableTest_Lexeme::Backslash, '\\', 2),
                new Token(TokenEscapeIterableTest_Lexeme::Backslash, '\\', 3),
                new Token(TokenEscapeIterableTest_Lexeme::Slash, '/', 3),
            ],
            iterator_to_array($iterable, false),
        );
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

enum TokenEscapeIterableTest_Lexeme: string {
    case String    = '';
    case Slash     = '/';
    case Asterisk  = '*';
    case Backslash = '\\';
}
