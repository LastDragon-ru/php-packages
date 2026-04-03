<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\Path\FilePath;
use PHPUnit\Framework\Attributes\CoversClass;

use function array_values;

/**
 * @internal
 */
#[CoversClass(Content::class)]
final class ContentTest extends TestCase {
    public function testPropertyChanges(): void {
        $aPath   = new FilePath('a.txt');
        $bPath   = new FilePath('b.txt');
        $content = new Content();

        self::assertSame([], $content->changes);

        $content->set($aPath, 'a');
        $content->set($bPath, 'b');

        self::assertSame([$aPath, $bPath], array_values($content->changes));
    }

    public function testMethods(): void {
        $aPath   = new FilePath('/directory/a.txt');
        $bPath   = new FilePath('/directory/b.txt');
        $cPath   = new FilePath('/c.txt');
        $content = new Content();

        $content->set($aPath, 'a');
        $content->set($bPath, 'b');
        $content->set($cPath, 'c');

        self::assertSame('a', $content->get($aPath));
        self::assertSame('b', $content->get($bPath));
        self::assertSame('c', $content->get($cPath));

        $content->delete($cPath);

        self::assertSame('a', $content->get($aPath));
        self::assertSame('b', $content->get($bPath));
        self::assertNull($content->get($cPath));

        $content->delete($aPath->directory());

        self::assertNull($content->get($aPath));
        self::assertNull($content->get($bPath));
        self::assertNull($content->get($cPath));
    }

    public function testCleanup(): void {
        $aPath   = new FilePath('/directory/a.txt');
        $bPath   = new FilePath('/directory/b.txt');
        $content = new Content();

        $content->set($aPath, 'a');
        $content->set($bPath, 'b');

        self::assertSame('a', $content->get($aPath));
        self::assertSame('b', $content->get($bPath));

        $content->cleanup();

        self::assertNull($content->get($aPath));
        self::assertNull($content->get($bPath));
    }
}
