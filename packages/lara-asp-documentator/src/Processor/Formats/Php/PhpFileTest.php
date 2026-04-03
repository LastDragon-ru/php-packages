<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Formats\Php;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FormatNotWritable;
use LastDragon_ru\LaraASP\Documentator\Processor\Executor\Resolver;
use LastDragon_ru\Path\FilePath;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Declare_;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles;
use PHPUnit\Framework\MockObject\Runtime\PropertyHook;

/**
 * @internal
 */
#[CoversClass(PhpFile::class)]
#[DisableReturnValueGenerationForTestDoubles]
final class PhpFileTest extends TestCase {
    public function testRead(): void {
        $resolver = self::createStub(Resolver::class);
        $file     = self::createMock(File::class);
        $format   = new PhpFile();

        $file
            ->expects(self::once())
            ->method(PropertyHook::get('content'))
            ->willReturn(
                <<<'PHP'
                <?php declare(strict_types = 1);

                namespace Test;

                class MyClass {
                    // empty
                }
                PHP,
            );

        $php = $format->read($resolver, $file);

        self::assertSame('Test\MyClass', $php->context->getResolvedClassName(new Name('MyClass'))->name);
        self::assertInstanceOf(Declare_::class, $php->stmts[0] ?? null);
    }

    public function testWrite(): void {
        $resolver = self::createStub(Resolver::class);
        $object   = self::createStub(Content::class);
        $path     = new FilePath('/path/to/file.md');
        $file     = self::createMock(File::class);
        $format   = new PhpFile();

        $file
            ->expects(self::once())
            ->method(PropertyHook::get('path'))
            ->willReturn($path);

        self::expectExceptionObject(new FormatNotWritable(PhpFile::class, $path));

        $format->write($resolver, $file, $object);
    }
}
