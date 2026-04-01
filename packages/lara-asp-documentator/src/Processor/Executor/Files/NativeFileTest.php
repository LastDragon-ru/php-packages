<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Executor\Files;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Executor\Resolver;
use LastDragon_ru\Path\FilePath;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles;

/**
 * @internal
 */
#[CoversClass(NativeFile::class)]
#[DisableReturnValueGenerationForTestDoubles]
final class NativeFileTest extends TestCase {
    public function testProperties(): void {
        $resolver = self::createMock(Resolver::class);
        $content  = 'content';
        $path     = new FilePath('/path/to/file.md');
        $file     = new NativeFile($resolver, $path);

        $resolver
            ->expects(self::once())
            ->method('read')
            ->with($path)
            ->willReturn($content);

        self::assertSame('file.md', $file->name);
        self::assertSame('md', $file->extension);
        self::assertSame($content, $file->content);
    }

    public function testSave(): void {
        $resolver = self::createMock(Resolver::class);
        $content  = 'content';
        $path     = new FilePath('/path/to/file.md');
        $file     = new NativeFile($resolver, $path);

        $resolver
            ->expects(self::once())
            ->method('save')
            ->with($path, $content);

        $file->save($content);

        self::assertSame($content, $file->content);
    }

    public function testDelete(): void {
        $resolver = self::createMock(Resolver::class);
        $path     = new FilePath('/path/to/file.md');
        $file     = new NativeFile($resolver, $path);

        $resolver
            ->expects(self::once())
            ->method('delete')
            ->with($path);

        $file->delete();
    }
}
