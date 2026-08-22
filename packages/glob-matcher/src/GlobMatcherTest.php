<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher;

use InvalidArgumentException;
use LastDragon_ru\GlobMatcher\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(GlobMatcher::class)]
final class GlobMatcherTest extends TestCase {
    public function testMatch(): void {
        self::assertTrue((new GlobMatcher('path/to/file'))->match('path/to/file'));
        self::assertTrue((new GlobMatcher('path/to/file-{0..2}.txt'))->match('path/to/file-1.txt'));
        self::assertFalse(
            (new GlobMatcher('path/to/file-{0..2}.txt', new Options(braces: false)))->match('path/to/file-1.txt'),
        );
        self::assertTrue(
            (new GlobMatcher('**/file-{0..2}.txt'))->match('path/to/file-1.txt'),
        );
        self::assertFalse(
            (new GlobMatcher('**/file-{0..2}.txt', new Options(globstar: false)))->match('path/to/file-1.txt'),
        );
        self::assertFalse(
            (new GlobMatcher('**/*.txt'))->match('path/to/.hidden.txt'),
        );
        self::assertTrue(
            (new GlobMatcher('**/*.txt', new Options(hidden: true)))->match('path/to/.hidden.txt'),
        );
        self::assertTrue(
            (new GlobMatcher('**/*.txt', new Options(matchHidden: true)))->match('path/to/.hidden.txt'),
        );
        self::assertFalse(
            (new GlobMatcher('\\*.txt'))->match('a.txt'),
        );
        self::assertTrue(
            (new GlobMatcher('\\*.txt'))->match('*.txt'),
        );
    }

    public function testMatchBracesLimit(): void {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('The `$pattern` generates too many variants.');

        new GlobMatcher('{1..100}{1..25}');
    }

    public function testEscape(): void {
        self::assertSame('/a/b/c.txt', GlobMatcher::escape('/a/b/c.txt'));
        self::assertSame('/a/b/\\*.txt', GlobMatcher::escape('/a/b/*.txt'));
        self::assertSame('/a/\\*\\*/\\*.txt', GlobMatcher::escape('/a/**/*.txt'));
        self::assertSame('\\[\\[.ch.\\]\\].txt', GlobMatcher::escape('[[.ch.]].txt'));
        self::assertSame('\\[\\[=a=\\]\\].txt', GlobMatcher::escape('[[=a=]].txt'));
        self::assertSame('/\\{a,b,c\\}.txt', GlobMatcher::escape('/{a,b,c}.txt'));
        self::assertSame('/\\{a..c\\}.txt', GlobMatcher::escape('/{a..c}.txt'));

        self::assertSame('/{a,b,c}.\\*.txt', GlobMatcher::escape('/{a,b,c}.*.txt', new Options(braces: false)));
        self::assertSame('/{a..c}.\\*.txt', GlobMatcher::escape('/{a..c}.*.txt', new Options(braces: false)));
    }
}
