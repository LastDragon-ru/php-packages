<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\Path\FilePath;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Content::class)]
final class ContentTest extends TestCase {
    public function testChanged(): void {
        $content = new Content();
        $path    = new FilePath('file.txt');

        self::assertFalse($content->changed($path));

        $content[$path] = 'abc';

        self::assertTrue($content->changed($path));
        self::assertTrue($content->changed(new FilePath('file.txt')));

        unset($content[$path]);

        self::assertFalse($content->changed($path));
        self::assertFalse($content->changed(new FilePath('file.txt')));
    }

    public function testChanges(): void {
        $aPath   = new FilePath('a.txt');
        $bPath   = new FilePath('b.txt');
        $content = new Content();

        self::assertSame([], $content->changes());

        $content[$aPath] = 'a';
        $content[$bPath] = 'b';

        self::assertSame([$aPath, $bPath], $content->changes());
        self::assertSame([$aPath, $bPath], $content->changes());
    }

    public function testDelete(): void {
        $aPath   = new FilePath('/directory/a.txt');
        $bPath   = new FilePath('/directory/b.txt');
        $cPath   = new FilePath('/c.txt');
        $content = new Content();

        $content[$aPath] = 'a';
        $content[$bPath] = 'b';
        $content[$cPath] = 'c';

        self::assertTrue(isset($content[$aPath]));
        self::assertTrue(isset($content[$bPath]));
        self::assertTrue(isset($content[$cPath]));

        $content->delete($cPath);

        self::assertTrue(isset($content[$aPath]));
        self::assertTrue(isset($content[$bPath]));
        self::assertFalse(isset($content[$cPath]));

        $content->delete($aPath->directory());

        self::assertFalse(isset($content[$aPath]));
        self::assertFalse(isset($content[$bPath]));
        self::assertFalse(isset($content[$cPath]));
    }

    public function testReset(): void {
        $path    = new FilePath('b.txt');
        $content = new Content();

        $content[$path] = 'a';

        self::assertTrue($content->changed($path));

        $content->reset($path);

        self::assertFalse($content->changed($path));
        self::assertFalse(isset($content[$path]));
    }

    public function testArrayAccess(): void {
        $content = new Content();
        $path    = new FilePath('b.txt');

        self::assertFalse(isset($content[$path]));
        self::assertNull($content[$path]);

        $content[$path] = 'abc';

        self::assertTrue(isset($content[$path]));
        self::assertSame('abc', $content[$path]);

        unset($content[$path]);

        self::assertFalse(isset($content[$path]));
        self::assertNull($content[$path]);
    }
}
