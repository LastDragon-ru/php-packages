<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Formats;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Executor\Resolver;
use LastDragon_ru\Path\FilePath;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles;
use PHPUnit\Framework\MockObject\Runtime\PropertyHook;

/**
 * @internal
 */
#[CoversClass(MarkdownFile::class)]
#[DisableReturnValueGenerationForTestDoubles]
final class MarkdownFileTest extends TestCase {
    public function testRead(): void {
        $resolver = self::createStub(Resolver::class);
        $markdown = self::createMock(Markdown::class);
        $document = self::createStub(Document::class);
        $content  = 'content';
        $path     = new FilePath('/path/to/file.md');
        $file     = self::createMock(File::class);
        $format   = new MarkdownFile($markdown);

        $file
            ->expects(self::once())
            ->method(PropertyHook::get('content'))
            ->willReturn($content);
        $file
            ->expects(self::once())
            ->method(PropertyHook::get('path'))
            ->willReturn($path);
        $markdown
            ->expects(self::once())
            ->method('parse')
            ->with($content)
            ->willReturn($document);

        self::assertSame($document, $format->read($resolver, $file));
    }

    public function testWrite(): void {
        $resolver = self::createStub(Resolver::class);
        $markdown = self::createStub(Markdown::class);
        $document = self::createMock(Document::class);
        $content  = 'content';
        $path     = new FilePath('/path/to/file.md');
        $file     = self::createMock(File::class);
        $format   = new MarkdownFile($markdown);

        $file
            ->expects(self::once())
            ->method(PropertyHook::get('path'))
            ->willReturn($path);
        $document
            ->expects(self::once())
            ->method(PropertyHook::set('path'))
            ->with($path);
        $document
            ->expects(self::once())
            ->method('__toString')
            ->willReturn($content);

        self::assertSame($content, $format->write($resolver, $file, $document));
    }
}
