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
        $tokenizer = new Tokenizer(TokenizerTest_LexemeA::class);
        $expected  = [
            new Token(null, 'abc', 0),
            new Token(TokenizerTest_LexemeA::Asterisk, '*', 3),
            new Token(null, 'cd', 4),
            new Token(TokenizerTest_LexemeA::Backslash, '\\', 6),
            new Token(TokenizerTest_LexemeA::Asterisk, '*', 7),
            new Token(null, 'e', 8),
        ];

        self::assertEquals($expected, iterator_to_array($tokenizer->tokenize($source), false));
    }

    public function testTokenizeAliases(): void {
        $source    = ['abc*cd\\*e'];
        $tokenizer = new Tokenizer(
            [TokenizerTest_LexemeA::class, TokenizerTest_LexemeB::class],
        );
        $expected  = [
            new Token(null, 'abc', 0),
            new Token(TokenizerTest_LexemeA::Asterisk, '*', 3),
            new Token(null, 'cd', 4),
            new Token(TokenizerTest_LexemeA::Backslash, '\\', 6),
            new Token(TokenizerTest_LexemeA::Asterisk, '*', 7),
            new Token(null, 'e', 8),
        ];

        self::assertEquals($expected, iterator_to_array($tokenizer->tokenize($source), false));
    }

    public function testTokenizeUnknown(): void {
        $source    = ['a*b'];
        $offset    = 2;
        $tokenizer = new Tokenizer(TokenizerTest_LexemeA::class, unknown: TokenizerTest_LexemeC::A);
        $expected  = [
            new Token(TokenizerTest_LexemeC::A, 'a', $offset + 0),
            new Token(TokenizerTest_LexemeA::Asterisk, '*', $offset + 1),
            new Token(TokenizerTest_LexemeC::A, 'b', $offset + 2),
        ];

        self::assertEquals($expected, iterator_to_array($tokenizer->tokenize($source, $offset), false));
    }

    public function testTokenizeCaseInsensitive(): void {
        $source    = ['LeXeMeA LexeMea lexemea LeXeMeB LexemeB lexemeb'];
        $tokenizer = new Tokenizer(TokenizerTest_LexemeC::class, caseSensitive: false);
        $expected  = [
            new Token(TokenizerTest_LexemeC::A, 'LeXeMeA', 0),
            new Token(null, ' ', 7),
            new Token(TokenizerTest_LexemeC::A, 'LexeMea', 8),
            new Token(null, ' ', 15),
            new Token(TokenizerTest_LexemeC::A, 'lexemea', 16),
            new Token(null, ' ', 23),
            new Token(TokenizerTest_LexemeC::B, 'LeXeMeB', 24),
            new Token(null, ' ', 31),
            new Token(TokenizerTest_LexemeC::B, 'LexemeB', 32),
            new Token(null, ' ', 39),
            new Token(TokenizerTest_LexemeC::B, 'lexemeb', 40),
        ];

        self::assertEquals($expected, iterator_to_array($tokenizer->tokenize($source), false));
    }

    public function testName(): void {
        $tokenizer = new Tokenizer([TokenizerTest_LexemeA::class, TokenizerTest_LexemeB::class]);

        self::assertSame(
            TokenizerTest_LexemeA::Asterisk,
            $tokenizer->name(TokenizerTest_LexemeB::Asterisk),
        );
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

enum TokenizerTest_LexemeA: string {
    case String    = '';
    case Slash     = '/';
    case Asterisk  = '*';
    case Backslash = '\\';
}

enum TokenizerTest_LexemeB: string {
    case Asterisk = '*';
}

enum TokenizerTest_LexemeC: string {
    case A = 'LexemeA';
    case B = 'LeXeMeB';
}
