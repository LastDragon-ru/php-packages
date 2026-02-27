<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Serializer\Normalizers;

use LastDragon_ru\LaraASP\Serializer\Package\TestCase;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use LastDragon_ru\Path\Path;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

use function sprintf;

/**
 * @internal
 */
#[CoversClass(PathNormalizer::class)]
final class PathNormalizerTest extends TestCase {
    public function testNormalize(): void {
        $normalizer = new PathNormalizer();
        $filePath   = new FilePath('file.md');
        $dirPath    = new DirectoryPath('/path/to/directory');

        self::assertSame((string) $filePath->normalized(), $normalizer->normalize($filePath));
        self::assertSame((string) $dirPath->normalized(), $normalizer->normalize($dirPath));
    }

    public function testNormalizeNotPath(): void {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage(
            sprintf(
                'The `$object` expected to be a `%s`, `%s` given.',
                Path::class,
                stdClass::class,
            ),
        );

        (new PathNormalizer())->normalize(new stdClass());
    }

    public function testDenormalize(): void {
        $normalizer = new PathNormalizer();
        $filePath   = (new FilePath('file.md'))->normalized();
        $dirPath    = (new DirectoryPath('/path/to/directory'))->normalized();

        self::assertEquals($filePath, $normalizer->denormalize((string) $filePath, FilePath::class));
        self::assertEquals($dirPath, $normalizer->denormalize((string) $dirPath, DirectoryPath::class));
    }

    public function testDenormalizeNotPath(): void {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage(
            sprintf(
                'The `$type` expected to be a `%s`, `%s` given.',
                Path::class,
                stdClass::class,
            ),
        );

        (new PathNormalizer())->denormalize('value', stdClass::class);
    }
}
