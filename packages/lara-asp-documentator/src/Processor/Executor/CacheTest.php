<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Executor;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\Path\FilePath;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles;

/**
 * @internal
 */
#[CoversClass(Cache::class)]
#[DisableReturnValueGenerationForTestDoubles]
final class CacheTest extends TestCase {
    public function testArrayAccess(): void {
        $cache = new Cache(1);
        $aPath = new FilePath('file.txt');
        $aFile = self::createStub(File::class);

        self::assertFalse(isset($cache[$aPath]));

        $cache[$aPath] = $aFile;

        self::assertTrue(isset($cache[$aPath]));
        self::assertSame($aFile, $cache[$aPath] ?? null);
        self::assertTrue(isset($cache[new FilePath('file.txt')]));
        self::assertFalse(isset($cache[new FilePath('another.txt')]));

        unset($cache[new FilePath('file.txt')]);

        self::assertFalse(isset($cache[$aPath]));
    }

    public function testCleanup(): void {
        $resolver = self::createStub(Resolver::class);
        $cache    = new Cache(1);
        $aPath    = new FilePath('/a.txt');
        $aFile    = new File($resolver, $aPath);
        $bPath    = new FilePath('/b.txt');
        $bFile    = new File($resolver, $bPath);
        $cPath    = new FilePath('/c.txt');

        $cache[$aPath] = $aFile;
        $cache[$bPath] = $bFile;
        $cache[$cPath] = new File(self::createStub(Resolver::class), $cPath);

        $cache->cleanup();

        self::assertTrue(isset($cache[$aPath]));
        self::assertTrue(isset($cache[$bPath]));
        self::assertTrue(isset($cache[$cPath]));

        self::assertSame($bFile, $cache[$bPath]);

        $cache->cleanup();
        $cache->cleanup();

        self::assertTrue(isset($cache[$aPath]));  // exists because var
        self::assertTrue(isset($cache[$bPath]));  // exists because var
        self::assertFalse(isset($cache[$cPath])); // no var

        unset($aFile);

        $cache->cleanup();

        self::assertFalse(isset($cache[$aPath])); // var was unset
        self::assertTrue(isset($cache[$bPath]));  // var still exists
    }

    public function testDelete(): void {
        $resolver = self::createStub(Resolver::class);
        $cache    = new Cache(1);
        $aPath    = new FilePath('/a/a.txt');
        $bPath    = new FilePath('/a/b.txt');
        $cPath    = new FilePath('/c.txt');

        $cache[$aPath] = new File($resolver, $aPath);
        $cache[$bPath] = new File($resolver, $bPath);
        $cache[$cPath] = new File($resolver, $cPath);

        self::assertTrue(isset($cache[$aPath]));
        self::assertTrue(isset($cache[$bPath]));
        self::assertTrue(isset($cache[$cPath]));

        $cache->delete($aPath->directory());

        self::assertFalse(isset($cache[$aPath]));
        self::assertFalse(isset($cache[$bPath]));
        self::assertTrue(isset($cache[$cPath]));

        $cache->delete($cPath);

        self::assertFalse(isset($cache[$cPath]));
    }
}
