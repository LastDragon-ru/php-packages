<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Utils;

use LastDragon_ru\Path\FilePath;
use LastDragon_ru\PhpUnit\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function file_get_contents;
use function fileperms;
use function mb_substr;
use function sprintf;

use const PHP_OS_FAMILY;

/**
 * @internal
 */
#[CoversClass(TempFile::class)]
final class TempFileTest extends TestCase {
    public function testWithoutParams(): void {
        $file = new TempFile();
        $path = $file->path->path;

        self::assertFileExists($path);
        self::assertSame(
            PHP_OS_FAMILY !== 'Windows' ? '0600' : '0666',
            mb_substr(sprintf('%o', (int) fileperms($path)), -4),
        );

        unset($file);

        self::assertFileDoesNotExist($path);
    }

    public function testWithFilePath(): void {
        $path = new FilePath(__FILE__);
        $file = new TempFile($path);
        $path = $file->path->path;

        self::assertFileEquals(__FILE__, $file->path->path);
        self::assertSame(
            PHP_OS_FAMILY !== 'Windows' ? '0600' : '0666',
            mb_substr(sprintf('%o', (int) fileperms($path)), -4),
        );
    }

    public function testWithContent(): void {
        $content = 'content';
        $file    = new TempFile($content);
        $path    = $file->path->path;

        self::assertSame($content, file_get_contents($file->path->path));
        self::assertSame(
            PHP_OS_FAMILY !== 'Windows' ? '0600' : '0666',
            mb_substr(sprintf('%o', (int) fileperms($path)), -4),
        );
    }
}
