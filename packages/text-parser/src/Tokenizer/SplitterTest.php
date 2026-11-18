<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Tokenizer;

use LastDragon_ru\TextParser\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function implode;
use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(Splitter::class)]
final class SplitterTest extends TestCase {
    public function testSplit(): void {
        // Prepare
        $separators = ['[', '!!!', ']['];
        $strings    = [
            'aaa][aaaaaaaa]',
            '[bbbbbbbbbbb!!',
            '!ccccc[',
            'cccccc',
        ];
        $string     = implode('', $strings);

        // Long buffer
        $splitter = new Splitter($separators);
        $long     = iterator_to_array($splitter->split($strings));

        self::assertSame($string, implode('', $long));
        self::assertSame(
            [
                0  => 'aaa',
                3  => '][',
                5  => 'aaaaaaaa',
                13 => '][',
                15 => 'bbbbbbbbbbb',
                26 => '!!!',
                29 => 'ccccc',
                34 => '[',
                35 => 'cccccc',
            ],
            $long,
        );

        // Short buffer
        $splitter = new Splitter($separators, 2);
        $short    = iterator_to_array($splitter->split($strings));

        self::assertSame($string, implode('', $short));
        self::assertSame(
            [
                0  => 'aaa',
                3  => '][',
                5  => 'aaaaaaaa',
                13 => '][',
                15 => 'bbbbbbbbbbb',
                26 => '!!!',
                29 => 'ccccc',
                34 => '[',
                35 => 'cccccc',
            ],
            $short,
        );
    }

    public function testGetIteratorCaseInsensitive(): void {
        $separators = ['a'];
        $input      = ['A[a]A'];
        $splitter   = new Splitter($separators, caseSensitive: false);
        $actual     = iterator_to_array($splitter->split($input));
        $expected   = ['A', '[', 'a', ']', 'A'];

        self::assertSame($expected, $actual);
    }
}
