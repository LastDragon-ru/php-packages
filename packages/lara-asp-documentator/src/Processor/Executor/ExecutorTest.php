<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Executor;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyUnavailable;
use LastDragon_ru\Path\FilePath;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles;
use ReflectionProperty;

/**
 * @internal
 */
#[CoversClass(Executor::class)]
#[DisableReturnValueGenerationForTestDoubles]
final class ExecutorTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderRun')]
    public function testRun(?bool $expected, State $state): void {
        $path     = new FilePath('/file.md');
        $executor = self::createPartialMock(Executor::class, ['isSkipped', 'file']);

        if ($expected === true) {
            $executor
                ->expects(self::once())
                ->method('isSkipped')
                ->with($path)
                ->willReturn(false);
            $executor
                ->expects(self::once())
                ->method('file');
        } elseif ($expected === false) {
            self::expectException(DependencyUnavailable::class);

            $executor
                ->expects(self::never())
                ->method('isSkipped')
                ->with($path);
            $executor
                ->expects(self::never())
                ->method('file');
        } else {
            $executor
                ->expects(self::once())
                ->method('isSkipped')
                ->with($path)
                ->willReturn(true);
            $executor
                ->expects(self::never())
                ->method('file');
        }

        (new ReflectionProperty(Executor::class, 'state'))->setValue($executor, $state);

        $executor->run($path);
    }

    /**
     * @param 'file'|'push'|null $expected
     * @param non-empty-string   $current
     * @param non-empty-string   $path
     */
    #[DataProvider('dataProviderSave')]
    public function testSave(?string $expected, State $state, string $current, ?bool $skipped, string $path): void {
        $path     = new FilePath($path);
        $executor = self::createPartialMock(Executor::class, ['isSkipped', 'file', 'push']);

        if ($skipped !== null) {
            $executor
                ->expects(self::once())
                ->method('isSkipped')
                ->with($path)
                ->willReturn($skipped);
        } else {
            $executor
                ->expects(self::never())
                ->method('isSkipped');
        }

        if ($expected !== null) {
            $executor
                ->expects(self::once())
                ->method($expected)
                ->with($path);
        } else {
            $executor
                ->expects(self::never())
                ->method('file');
            $executor
                ->expects(self::never())
                ->method('push');
        }

        (new ReflectionProperty(Executor::class, 'state'))->setValue($executor, $state);
        (new ReflectionProperty(Executor::class, 'stack'))->setValue($executor, [$path->file($current)]);
        (new ReflectionProperty(Executor::class, 'processed'))->setValue($executor, [
            $path->path => true,
            $current    => true,
        ]);

        $executor->save($path);

        if ($expected !== null) {
            self::assertSame(
                [$current => true],
                (new ReflectionProperty(Executor::class, 'processed'))->getValue($executor),
            );
        }
    }

    #[DataProvider('dataProviderQueue')]
    public function testQueue(bool $expected, State $state): void {
        $path     = new FilePath('/file.md');
        $executor = self::createPartialMock(Executor::class, ['isSkipped', 'push']);

        if ($expected) {
            $executor
                ->expects(self::once())
                ->method('isSkipped')
                ->with($path)
                ->willReturn(false);
            $executor
                ->expects(self::once())
                ->method('push')
                ->with($path);
        } else {
            self::expectException(DependencyUnavailable::class);

            $executor
                ->expects(self::never())
                ->method('isSkipped');
            $executor
                ->expects(self::never())
                ->method('push');
        }

        (new ReflectionProperty(Executor::class, 'state'))->setValue($executor, $state);

        $executor->queue($path);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{?bool, State}>
     */
    public static function dataProviderRun(): array {
        return [
            State::Preparation->name => [null, State::Preparation],
            State::Iteration->name   => [true, State::Iteration],
            State::Finished->name    => [true, State::Finished],
            State::Created->name     => [false, State::Created],
        ];
    }

    /**
     * @return array<string, array{'file'|'push'|null, State, non-empty-string, ?bool, non-empty-string}>
     */
    public static function dataProviderSave(): array {
        return [
            'equal to the current file'                  => [
                null,
                State::Iteration,
                '/file.txt',
                null,
                '/file.txt',
            ],
            'not equal to the current file'              => [
                'push',
                State::Iteration,
                '/file.txt',
                false,
                '/file.md',
            ],
            'not equal to the current file but finished' => [
                'file',
                State::Finished,
                '/file.txt',
                false,
                '/file.md',
            ],
            'not equal to the current file but skipped'  => [
                null,
                State::Iteration,
                '/file.txt',
                true,
                '/file.md',
            ],
        ];
    }

    /**
     * @return array<string, array{bool, State}>
     */
    public static function dataProviderQueue(): array {
        return [
            State::Preparation->name => [true, State::Preparation],
            State::Iteration->name   => [true, State::Iteration],
            State::Finished->name    => [false, State::Finished],
            State::Created->name     => [true, State::Created],
        ];
    }
    // </editor-fold>
}
