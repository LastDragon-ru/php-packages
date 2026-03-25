<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Casts;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown as MarkdownContract;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Executor\Resolver;
use LastDragon_ru\Path\FilePath;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles;
use PHPUnit\Framework\MockObject\Runtime\PropertyHook;

/**
 * @internal
 */
#[CoversClass(Markdown::class)]
#[DisableReturnValueGenerationForTestDoubles]
final class MarkdownTest extends TestCase {
    public function testInvoke(): void {
        $resolver = Mockery::mock(Resolver::class);
        $markdown = Mockery::mock(MarkdownContract::class);
        $document = Mockery::mock(Document::class);
        $content  = 'content';
        $cast     = new Markdown($markdown);
        $path     = new FilePath('/path/to/file.md');
        $file     = self::createMock(File::class);

        $file
            ->expects($this->once())
            ->method(PropertyHook::get('path'))
            ->willReturn($path);
        $file
            ->expects($this->once())
            ->method(PropertyHook::get('content'))
            ->willReturn($content);

        $markdown
            ->shouldReceive('parse')
            ->with($content, $path)
            ->once()
            ->andReturn($document);

        self::assertSame($document, $cast($resolver, $file));
    }
}
