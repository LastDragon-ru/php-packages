<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Extensions\PathComparator;

use LastDragon_ru\Path\FilePath;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Extension::class)]
final class ExtensionTest extends TestCase {
    public function testIntegration(): void {
        self::assertEquals(
            (new FilePath('file.txt'))->normalized(),
            new FilePath('file.txt'),
        );
    }
}
