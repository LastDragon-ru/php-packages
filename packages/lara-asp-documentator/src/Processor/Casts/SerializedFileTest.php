<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Casts;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer;
use LastDragon_ru\Path\FilePath;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles;
use PHPUnit\Framework\MockObject\Runtime\PropertyHook;

/**
 * @internal
 */
#[CoversClass(SerializedFile::class)]
#[DisableReturnValueGenerationForTestDoubles]
final class SerializedFileTest extends TestCase {
    public function testTo(): void {
        $path       = new FilePath('/file.md');
        $file       = self::createMock(File::class);
        $content    = 'content';
        $serializer = Mockery::mock(Serializer::class);
        $serialized = new SerializedFile($serializer, $file);

        $file
            ->expects($this->exactly(2))
            ->method(PropertyHook::get('extension'))
            ->willReturn($path->extension);
        $file
            ->expects($this->exactly(2))
            ->method(PropertyHook::get('content'))
            ->willReturn($content);

        $serializer
            ->shouldReceive('deserialize')
            ->once()
            ->with(SerializedFileTest__SerializableA::class, $content, $path->extension)
            ->andReturn(
                new SerializedFileTest__SerializableA(),
            );
        $serializer
            ->shouldReceive('deserialize')
            ->once()
            ->with(SerializedFileTest__SerializableB::class, $content, $path->extension)
            ->andReturn(
                new SerializedFileTest__SerializableB(),
            );

        self::assertSame(
            $serialized->to(SerializedFileTest__SerializableA::class),
            $serialized->to(SerializedFileTest__SerializableA::class),
        );

        /** @phpstan-ignore staticMethod.alreadyNarrowedType (for test) */
        self::assertNotSame(
            $serialized->to(SerializedFileTest__SerializableA::class),
            $serialized->to(SerializedFileTest__SerializableB::class),
        );
    }

    public function testToString(): void {
        $path       = new FilePath('/file.md');
        $file       = self::createMock(File::class);
        $object     = new SerializedFileTest__SerializableA();
        $content    = 'content';
        $serializer = Mockery::mock(Serializer::class);
        $serialized = new SerializedFile($serializer, $file);

        $file
            ->expects($this->exactly(2))
            ->method(PropertyHook::get('extension'))
            ->willReturn($path->extension);
        $file
            ->expects($this->once())
            ->method(PropertyHook::get('content'))
            ->willReturn($content);

        $serializer
            ->shouldReceive('deserialize')
            ->once()
            ->with(SerializedFileTest__SerializableA::class, $content, $path->extension)
            ->andReturn($object);
        $serializer
            ->shouldReceive('serialize')
            ->once()
            ->with($object, $path->extension)
            ->andReturn($content);

        $value = $serialized->to(SerializedFileTest__SerializableA::class);

        self::assertSame($content, $serialized->toString($object));
        self::assertSame($value, $serialized->to(SerializedFileTest__SerializableA::class));
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class SerializedFileTest__SerializableA implements Serializable {
    // empty
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class SerializedFileTest__SerializableB implements Serializable {
    // empty
}
