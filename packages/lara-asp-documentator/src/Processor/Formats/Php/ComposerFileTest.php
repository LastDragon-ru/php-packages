<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Formats\Php;

use LastDragon_ru\LaraASP\Documentator\Composer\ComposerJson;
use LastDragon_ru\LaraASP\Documentator\Composer\Package;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FormatNotWritable;
use LastDragon_ru\LaraASP\Documentator\Processor\Executor\Resolver;
use LastDragon_ru\Path\FilePath;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles;
use PHPUnit\Framework\MockObject\Runtime\PropertyHook;

/**
 * @internal
 */
#[CoversClass(ComposerFile::class)]
#[DisableReturnValueGenerationForTestDoubles]
final class ComposerFileTest extends TestCase {
    public function testRead(): void {
        $resolver = self::createStub(Resolver::class);
        $object   = new ComposerJson('test');
        $file     = self::createMock(File::class);
        $json     = self::createMock(File::class);
        $format   = new ComposerFile();

        $file
            ->expects(self::once())
            ->method('as')
            ->with(ComposerJsonFile::class)
            ->willReturn($json);
        $json
            ->expects(self::once())
            ->method(PropertyHook::get('content'))
            ->willReturn($object);

        self::assertSame($object, $format->read($resolver, $file)->json);
    }

    public function testWrite(): void {
        $resolver = self::createStub(Resolver::class);
        $object   = new Package(new ComposerJson('test'));
        $path     = new FilePath('/path/to/file.md');
        $file     = self::createMock(File::class);
        $format   = new ComposerFile();

        $file
            ->expects(self::once())
            ->method(PropertyHook::get('path'))
            ->willReturn($path);

        self::expectExceptionObject(new FormatNotWritable(ComposerFile::class, $path));

        $format->write($resolver, $file, $object);
    }
}
