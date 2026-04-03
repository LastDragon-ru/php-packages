<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Executor\Files;

use Exception;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Format;
use LastDragon_ru\LaraASP\Documentator\Processor\Executor\Resolver;
use LastDragon_ru\Path\FilePath;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles;

/**
 * @internal
 */
#[CoversClass(Base::class)]
#[DisableReturnValueGenerationForTestDoubles]
final class BaseTest extends TestCase {
    public function testProperties(): void {
        $resolver = self::createStub(Resolver::class);
        $content  = 'content';
        $path     = new FilePath('/path/to/file.md');
        $file     = new class($resolver, $path, $content) extends Base {
            public function __construct(
                Resolver $resolver,
                public readonly FilePath $path,
                public readonly mixed $content,
            ) {
                parent::__construct($resolver);
            }

            #[Override]
            public function save(mixed $content): void {
                // empty
            }

            #[Override]
            public function delete(): void {
                // empty
            }
        };

        self::assertSame('file.md', $file->name);
        self::assertSame('md', $file->extension);
    }

    public function testAs(): void {
        $resolver = self::createMock(Resolver::class);
        $content  = 'content';
        $format   = self::createStub(Format::class);
        $path     = new FilePath('/path/to/file.md');
        $file     = new class($resolver, $path, $content) extends Base {
            public function __construct(
                Resolver $resolver,
                public readonly FilePath $path,
                public readonly mixed $content,
            ) {
                parent::__construct($resolver);
            }

            #[Override]
            public function save(mixed $content): void {
                // empty
            }

            #[Override]
            public function delete(): void {
                // empty
            }
        };

        $resolver
            ->expects(self::exactly(2))
            ->method('format')
            ->with($format::class)
            ->willReturn($format);

        $actual = $file->as($format::class);

        self::assertInstanceOf(FormattedFile::class, $actual);
        self::assertSame($actual, $file->as($format::class));
    }

    public function testReset(): void {
        $resolver = self::createMock(Resolver::class);
        $content  = 'content';
        $format   = self::createStub(Format::class);
        $path     = new FilePath('/path/to/file.md');
        $file     = new class($resolver, $path, $content) extends Base {
            public function __construct(
                Resolver $resolver,
                public readonly FilePath $path,
                public readonly mixed $content,
            ) {
                parent::__construct($resolver);
            }

            #[Override]
            public function save(mixed $content): void {
                // empty
            }

            #[Override]
            public function delete(): void {
                // empty
            }

            #[Override]
            public function reset(Format $except): void {
                parent::reset($except);
            }
        };

        $resolver
            ->expects(self::exactly(2))
            ->method('format')
            ->with($format::class)
            ->willReturn($format);

        $actual = $file->as($format::class);

        $file->reset(self::createStub(Format::class));

        self::assertNotSame($actual, $file->as($format::class));
    }

    public function testResetExcept(): void {
        $resolver = self::createMock(Resolver::class);
        $content  = 'content';
        $path     = new FilePath('/path/to/file.md');
        $file     = new class($resolver, $path, $content) extends Base {
            public function __construct(
                Resolver $resolver,
                public readonly FilePath $path,
                public readonly mixed $content,
            ) {
                parent::__construct($resolver);
            }

            #[Override]
            public function save(mixed $content): void {
                // empty
            }

            #[Override]
            public function delete(): void {
                // empty
            }

            #[Override]
            public function reset(Format $except): void {
                parent::reset($except);
            }
        };

        $aFormat = self::createStub(Format::class);
        $bFormat = self::createStub(Format::class);

        $resolver
            ->expects(self::exactly(4))
            ->method('format')
            ->willReturnCallback(static function (string $format) use ($aFormat, $bFormat) {
                return match (true) {
                    $format === $aFormat::class => $aFormat,
                    $format === Format::class   => $bFormat,
                    default                     => throw new Exception('Not a valid format.'),
                };
            });

        $a = $file->as($aFormat::class);
        $b = $file->as(Format::class);

        $file->reset($aFormat);

        self::assertSame($a, $file->as($aFormat::class));
        self::assertNotSame($b, $file->as(Format::class));
    }
}
