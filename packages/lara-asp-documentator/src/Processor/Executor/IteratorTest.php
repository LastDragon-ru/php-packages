<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Executor;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use PHPUnit\Framework\Attributes\CoversClass;

use function count;
use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(Iterator::class)]
final class IteratorTest extends TestCase {
    public function testGetIterator(): void {
        $aPath    = new FilePath('a.txt');
        $bPath    = new FilePath('b.txt');
        $cPath    = new FilePath('c.txt');
        $iterator = new Iterator([$aPath, $bPath]);

        $iterator->push($cPath);

        self::assertEquals(
            [
                $aPath,
                $cPath,
                $bPath,
            ],
            iterator_to_array($iterator, false),
        );
    }

    public function testDeleteFile(): void {
        $aPath    = new FilePath('a.txt');
        $bPath    = new FilePath('b.txt');
        $cPath    = new FilePath('c.txt');
        $iterator = new Iterator([$aPath]);

        $iterator->push($bPath);
        $iterator->push($cPath);

        $iterator->delete($bPath);

        self::assertEquals(
            [
                $aPath,
                $cPath,
            ],
            iterator_to_array($iterator, false),
        );
    }

    public function testDeleteDirectory(): void {
        $aPath    = new FilePath('a.txt');
        $bPath    = new FilePath('/b/b.txt');
        $cPath    = new FilePath('/c/c.txt');
        $iterator = new Iterator([$aPath]);

        $iterator->push($bPath);
        $iterator->push($cPath);

        $iterator->delete(new DirectoryPath('/b'));

        self::assertEquals(
            [
                $aPath,
                $cPath,
            ],
            iterator_to_array($iterator, false),
        );
    }

    public function testDeleteWhileIteration(): void {
        $aPath    = new FilePath('a.txt');
        $bPath    = new FilePath('b.txt');
        $cPath    = new FilePath('c.txt');
        $dPath    = new FilePath('d.txt');
        $iterator = new Iterator([$aPath]);

        $iterator->push($bPath);
        $iterator->push($cPath);
        $iterator->push($dPath);

        $actual = [];

        foreach ($iterator as $path) {
            $actual[] = $path;

            if (count($actual) === 2) {
                $iterator->delete($cPath);
            }
        }

        self::assertEquals(
            [
                $aPath,
                $dPath,
                $bPath,
            ],
            $actual,
        );
    }
}
