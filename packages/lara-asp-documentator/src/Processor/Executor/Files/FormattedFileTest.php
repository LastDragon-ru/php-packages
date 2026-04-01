<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Executor\Files;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Format;
use LastDragon_ru\LaraASP\Documentator\Processor\Executor\Resolver;
use LastDragon_ru\Path\FilePath;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles;
use PHPUnit\Framework\MockObject\Runtime\PropertyHook;

/**
 * @internal
 */
#[CoversClass(FormattedFile::class)]
#[DisableReturnValueGenerationForTestDoubles]
final class FormattedFileTest extends TestCase {
    public function testProperties(): void {
        $resolver = self::createStub(Resolver::class);
        $content  = 'content';
        $parent   = self::createMock(Base::class);
        $format   = self::createMock(Format::class);
        $path     = new FilePath('/path/to/file.md');
        $file     = new FormattedFile($resolver, $format, $parent);

        $parent
            ->expects(self::exactly(3))
            ->method(PropertyHook::get('path'))
            ->willReturn($path);
        $format
            ->expects(self::once())
            ->method('read')
            ->with($resolver, $parent)
            ->willReturn($content);

        self::assertSame($path, $file->path);
        self::assertSame('file.md', $file->name);
        self::assertSame('md', $file->extension);
        self::assertSame($content, $file->content);
        self::assertSame($content, $file->content);
    }

    public function testSave(): void {
        $resolver  = self::createStub(Resolver::class);
        $formatted = 'formatted';
        $content   = 'content';
        $parent    = self::createMock(Base::class);
        $format    = self::createMock(Format::class);
        $file      = new FormattedFile($resolver, $format, $parent);

        $format
            ->expects(self::once())
            ->method('write')
            ->with($resolver, $file, $formatted)
            ->willReturn($content);
        $parent
            ->expects(self::once())
            ->method('save')
            ->with($content);

        $file->save($formatted);

        self::assertSame($formatted, $file->content);
        self::assertSame($formatted, $file->content);
    }

    public function testDelete(): void {
        $resolver = self::createStub(Resolver::class);
        $parent   = self::createMock(Base::class);
        $format   = self::createStub(Format::class);
        $file     = new FormattedFile($resolver, $format, $parent);

        $parent
            ->expects(self::once())
            ->method('delete');

        $file->delete();
    }
}
