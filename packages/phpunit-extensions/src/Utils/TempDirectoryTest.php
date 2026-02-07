<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Utils;

use LastDragon_ru\PhpUnit\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function fileperms;
use function mb_substr;
use function sprintf;

use const PHP_OS_FAMILY;

/**
 * @internal
 */
#[CoversClass(TempDirectory::class)]
final class TempDirectoryTest extends TestCase {
    public function testWithoutParams(): void {
        $directory = new TempDirectory();
        $path      = $directory->path->path;

        self::assertDirectoryEmpty($path);
        self::assertSame(
            PHP_OS_FAMILY !== 'Windows' ? '0700' : '0777',
            mb_substr(sprintf('%o', (int) fileperms($path)), -4),
        );

        unset($directory);

        self::assertDirectoryDoesNotExist($path);
    }

    public function testWithDirectoryPath(): void {
        $source    = TestData::get()->directory();
        $directory = new TempDirectory($source);
        $path      = $directory->path->path;

        self::assertDirectoryEquals($source, $directory->path);
        self::assertSame(
            PHP_OS_FAMILY !== 'Windows' ? '0700' : '0777',
            mb_substr(sprintf('%o', (int) fileperms($path)), -4),
        );

        unset($directory);

        self::assertDirectoryDoesNotExist($path);
    }
}
