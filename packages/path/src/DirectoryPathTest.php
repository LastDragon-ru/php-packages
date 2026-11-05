<?php declare(strict_types = 1);

namespace LastDragon_ru\Path;

use LastDragon_ru\Path\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(DirectoryPath::class)]
final class DirectoryPathTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    public function testPropertyName(): void {
        $path       = new DirectoryPath('path/./to/./directory');
        $normalized = $path->normalized();

        self::assertSame('directory', $path->name);
        self::assertSame('directory', $normalized->name);
        self::assertSame('path/to/directory/', (string) $normalized);
        self::assertSame('', (new DirectoryPath('./'))->name);
    }

    public function testFile(): void {
        $relative = (new DirectoryPath('relative/path/to/directory'))->file('file.b');
        $absolute = (new DirectoryPath('/path/to/directory'))->file('/file.b');

        self::assertSame('relative/path/to/directory/file.b', (string) $relative);

        self::assertSame('/file.b', (string) $absolute);
    }

    public function testDirectory(): void {
        $relative = (new DirectoryPath('relative/path/to'))->directory('directory');
        $absolute = (new DirectoryPath('/path/to'))->directory('/directory');
        $null     = (new DirectoryPath('/path/to'))->directory();

        self::assertSame('relative/path/to/directory/', (string) $relative);

        self::assertSame('/directory/', (string) $absolute);

        self::assertSame('/path/to/', (string) $null);
    }

    public function testParent(): void {
        $relative = new DirectoryPath('relative/path/to/file');
        $absolute = new DirectoryPath('/absolute/path/to/file');

        self::assertSame('relative/path/to/', (string) $relative->parent());
        self::assertSame('relative/path/', (string) $relative->parent()->parent());

        self::assertSame('/absolute/path/to/', (string) $absolute->parent());
        self::assertSame('/absolute/path/', (string) $absolute->parent()->parent());
    }

    #[DataProvider('dataProviderContains')]
    public function testContains(bool $expected, string $root, string $path): void {
        self::assertSame($expected, (new DirectoryPath($root))->contains(Path::make($path)));
    }

    public function testNormalized(): void {
        self::assertSame('./', (string) (new DirectoryPath(''))->normalized());
        self::assertSame('./', (string) (new DirectoryPath('.'))->normalized());
        self::assertSame('../', (string) (new DirectoryPath('..'))->normalized());
        self::assertSame('/path/to/', (string) (new DirectoryPath('/path/to'))->normalized());
    }
    //</editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{bool, string, string}>
     */
    public static function dataProviderContains(): array {
        return [
            'self'                                    => [false, '/path/to/directory', '/path/to/directory/'],
            'absolute inside'                         => [true, '/path/to/directory', '/path/to/directory/file.md'],
            'absolute outside'                        => [false, '/path/to/directory', '/path/to/directory/../file.md'],
            'absolute outside (not normalized)'       => [false, '/path/to/directory', '/path/to/file.md'],
            'relative file'                           => [true, '/path/to/directory', 'file.md'],
            'PKSA-g499-cm4q-v35y/GHSA-6prc-h956-hxjq' => [false, '/base', '/base/../x://../secret.txt'],
        ];
    }
    //</editor-fold>
}
