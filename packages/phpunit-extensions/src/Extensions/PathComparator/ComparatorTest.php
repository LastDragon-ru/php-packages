<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Extensions\PathComparator;

use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\Comparator\ComparisonFailure;

/**
 * @internal
 */
#[CoversClass(Comparator::class)]
final class ComparatorTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderAssertEquals')]
    public function testAssertEquals(bool $equals, mixed $expected, mixed $actual): void {
        if (!$equals) {
            self::expectException(ComparisonFailure::class);
        }

        (new Comparator())->assertEquals($expected, $actual);

        self::assertEquals($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<array-key, mixed>
     */
    public static function dataProviderAssertEquals(): array {
        return [
            'not paths'           => [false, 1, 1],
            'same file'           => [true, new FilePath('file.txt'), new FilePath('file.txt')],
            'same directory'      => [true, new DirectoryPath('directory'), new DirectoryPath('directory')],
            'different file'      => [false, new FilePath('file.txt'), new FilePath('file.md')],
            'different directory' => [false, new DirectoryPath('a'), new DirectoryPath('b')],
            'same but different'  => [false, new DirectoryPath('path/to/a'), new FilePath('path/to/a')],
        ];
    }
    // </editor-fold>
}
