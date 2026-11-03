<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Tokenizer;

use LastDragon_ru\TextParser\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(Tokenizer::class)]
final class TokenizerTest extends TestCase {
    public function testTokenize(): void {
        $source    = ['abc*cd\\*e'];
        $tokenizer = new Tokenizer(TokenizerTest_TokenA::class, TokenizerTest_TokenA::Backslash);
        $expected  = [
            new Token(null, 'abc', 0),
            new Token(TokenizerTest_TokenA::Asterisk, '*', 3),
            new Token(null, 'cd', 4),
            new Token(null, '*', 7),
            new Token(null, 'e', 8),
        ];

        self::assertEquals($expected, iterator_to_array($tokenizer->tokenize($source), false));
    }

    public function testTokenizeAliases(): void {
        $source    = ['abc*cd\\*e'];
        $tokenizer = new Tokenizer(
            [TokenizerTest_TokenA::class, TokenizerTest_TokenB::class],
            TokenizerTest_TokenA::Backslash,
        );
        $expected  = [
            new Token(null, 'abc', 0),
            new Token(TokenizerTest_TokenA::Asterisk, '*', 3),
            new Token(null, 'cd', 4),
            new Token(null, '*', 7),
            new Token(null, 'e', 8),
        ];

        self::assertEquals($expected, iterator_to_array($tokenizer->tokenize($source), false));
    }

    public function testTokenizeNoEscape(): void {
        $source    = ['abc*cd\\*e'];
        $offset    = 2;
        $tokenizer = new Tokenizer(TokenizerTest_TokenA::class);
        $expected  = [
            new Token(null, 'abc', $offset + 0),
            new Token(TokenizerTest_TokenA::Asterisk, '*', $offset + 3),
            new Token(null, 'cd', $offset + 4),
            new Token(TokenizerTest_TokenA::Backslash, '\\', $offset + 6),
            new Token(TokenizerTest_TokenA::Asterisk, '*', $offset + 7),
            new Token(null, 'e', $offset + 8),
        ];

        self::assertEquals($expected, iterator_to_array($tokenizer->tokenize($source, $offset), false));
    }

    public function testTokenizeCaseInsensitive(): void {
        $source    = ['aBcbA'];
        $tokenizer = new Tokenizer(TokenizerTest_TokenC::class, caseSensitive: false);
        $expected  = [
            new Token(TokenizerTest_TokenC::A, 'a', 0),
            new Token(TokenizerTest_TokenC::B, 'B', 1),
            new Token(null, 'c', 2),
            new Token(TokenizerTest_TokenC::B, 'b', 3),
            new Token(TokenizerTest_TokenC::A, 'A', 4),
        ];

        self::assertEquals($expected, iterator_to_array($tokenizer->tokenize($source), false));
    }

    public function testName(): void {
        $tokenizer = new Tokenizer([TokenizerTest_TokenA::class, TokenizerTest_TokenB::class]);

        self::assertSame(
            TokenizerTest_TokenA::Asterisk,
            $tokenizer->name(TokenizerTest_TokenB::Asterisk),
        );
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

enum TokenizerTest_TokenA: string {
    case String    = '';
    case Slash     = '/';
    case Asterisk  = '*';
    case Backslash = '\\';
}

enum TokenizerTest_TokenB: string {
    case Asterisk = '*';
}

enum TokenizerTest_TokenC: string {
    case A = 'a';
    case B = 'B';
}
