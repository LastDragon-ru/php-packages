<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Casts\Php;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Package\WithProcessor;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles;
use PHPUnit\Framework\MockObject\Runtime\PropertyHook;
use ReflectionProperty;

use function array_first;

/**
 * @internal
 */
#[CoversClass(Parsed::class)]
#[CoversClass(ParsedClass::class)]
#[CoversClass(ParsedCollector::class)]
#[CoversClass(ParsedFile::class)]
#[DisableReturnValueGenerationForTestDoubles]
final class ParsedTest extends TestCase {
    use WithProcessor;

    public function testInvoke(): void {
        $content    = <<<'PHP'
            <?php declare(strict_types = 1);

            namespace LastDragon_ru\LaraASP\Documentator\Processor\Cast;

            use stdClass;
            use LastDragon_ru\LaraASP\Documentator\Processor\Casts\PhpClass;

            /**
             * Description.
             *
             * Summary {@see stdClass} and {@see PhpClass}, {@see https://example.com/}.
             */
            class A {
                // empty
            }
            PHP;
        $filesystem = Mockery::mock(FileSystem::class);

        (new ReflectionProperty($filesystem, 'input'))->setValue($filesystem, new DirectoryPath('/input/'));

        $resolver = $this->getProcessorResolver($filesystem);
        $cast     = $this->app()->make(Parsed::class);
        $path     = new FilePath('/path/to/file.json');
        $file     = self::createMock(File::class);

        $file
            ->expects($this->once())
            ->method(PropertyHook::get('path'))
            ->willReturn($path);
        $file
            ->expects($this->once())
            ->method(PropertyHook::get('content'))
            ->willReturn($content);

        $class = array_first($cast($resolver, $file)->classes);

        self::assertInstanceOf(ParsedClass::class, $class);
        self::assertSame(
            <<<'MARKDOWN'
            Description.

            Summary `\stdClass` and `\LastDragon_ru\LaraASP\Documentator\Processor\Casts\PhpClass`, {@see https://example.com/}.
            MARKDOWN
            ,
            (string) $class->markdown,
        );
    }
}
