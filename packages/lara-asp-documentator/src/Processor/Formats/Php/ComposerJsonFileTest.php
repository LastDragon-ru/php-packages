<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Formats\Php;

use LastDragon_ru\LaraASP\Documentator\Composer\ComposerJson;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Executor\Resolver;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles;
use PHPUnit\Framework\MockObject\Runtime\PropertyHook;

/**
 * @internal
 */
#[CoversClass(ComposerJsonFile::class)]
#[DisableReturnValueGenerationForTestDoubles]
final class ComposerJsonFileTest extends TestCase {
    public function testRead(): void {
        $serializer = self::createMock(Serializer::class);
        $resolver   = self::createStub(Resolver::class);
        $content    = 'content';
        $object     = new ComposerJson('test');
        $file       = self::createMock(File::class);
        $ext        = 'json';
        $format     = new class($serializer) extends ComposerJsonFile {
            // empty
        };

        $file
            ->expects(self::once())
            ->method(PropertyHook::get('content'))
            ->willReturn($content);
        $file
            ->expects(self::once())
            ->method(PropertyHook::get('extension'))
            ->willReturn($ext);
        $serializer
            ->expects(self::once())
            ->method('deserialize')
            ->with($object::class, $content, $ext)
            ->willReturn($object);

        self::assertSame($object, $format->read($resolver, $file));
    }

    public function testWrite(): void {
        $serializer = self::createMock(Serializer::class);
        $resolver   = self::createStub(Resolver::class);
        $content    = 'content';
        $object     = new ComposerJson('test');
        $file       = self::createMock(File::class);
        $ext        = 'json';
        $format     = new class($serializer) extends ComposerJsonFile {
            // empty
        };

        $file
            ->expects(self::once())
            ->method(PropertyHook::get('extension'))
            ->willReturn($ext);
        $serializer
            ->expects(self::once())
            ->method('serialize')
            ->with($object, $ext)
            ->willReturn($content);

        self::assertSame($content, $format->write($resolver, $file, $object));
    }
}
