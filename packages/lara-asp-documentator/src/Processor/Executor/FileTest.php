<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Executor;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\Path\FilePath;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles;

/**
 * @internal
 */
#[CoversClass(File::class)]
#[DisableReturnValueGenerationForTestDoubles]
final class FileTest extends TestCase {
    public function testProperties(): void {
        $resolver = self::createMock(Resolver::class);
        $content  = 'content';
        $path     = new FilePath('/path/to/file.md');
        $file     = new File($path, $resolver);

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
        $file     = new File($path, $resolver);

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
        $file     = new File($path, $resolver);

        $resolver
            ->expects(self::once())
            ->method('delete')
            ->with($path);

        $file->delete();
    }
}
