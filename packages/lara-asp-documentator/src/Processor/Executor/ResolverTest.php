<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Executor;

use Exception;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Cast;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Container;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Event;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Resolver as Contract;
use LastDragon_ru\LaraASP\Documentator\Processor\Dispatcher;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResult;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File as FileImpl;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles;
use PHPUnit\Framework\MockObject\Runtime\PropertyHook;
use UnitEnum;

/**
 * @internal
 */
#[CoversClass(Resolver::class)]
#[DisableReturnValueGenerationForTestDoubles]
final class ResolverTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    public function testFile(): void {
        $run        = self::createStub(ResolverTest__Invokable::class);
        $save       = self::createStub(ResolverTest__Invokable::class);
        $queue      = self::createStub(ResolverTest__Invokable::class);
        $delete     = self::createStub(ResolverTest__Invokable::class);
        $container  = self::createStub(Container::class);
        $dispatcher = self::createStub(Dispatcher::class);
        $directory  = new DirectoryPath('/directory/path/');
        $filesystem = self::createMock(FileSystem::class);
        $resolved   = new FileImpl($filesystem, new FilePath('/file.txt'));
        $resolver   = new Resolver(
            $container,
            $dispatcher,
            $filesystem,
            $run(...),
            $save(...),
            $queue(...),
            $delete(...),
        );

        $filesystem
            ->expects(self::once())
            ->method(PropertyHook::get('directory'))
            ->willReturn($directory);
        $filesystem
            ->expects(self::once())
            ->method('get')
            ->with(new FilePath('/directory/path/file.txt'))
            ->willReturn($resolved);

        self::assertSame($resolved, $resolver->file(new FilePath('file.txt')));
    }

    public function testGet(): void {
        $run        = self::createMock(ResolverTest__Invokable::class);
        $save       = self::createStub(ResolverTest__Invokable::class);
        $queue      = self::createStub(ResolverTest__Invokable::class);
        $delete     = self::createStub(ResolverTest__Invokable::class);
        $container  = self::createStub(Container::class);
        $dispatcher = new ResolverTest__Dispatcher();
        $directory  = new DirectoryPath('/directory/path/');
        $filesystem = self::createMock(FileSystem::class);
        $filepath   = new FilePath('/directory/path/file.txt');
        $resolved   = new FileImpl($filesystem, $filepath);
        $resolver   = new Resolver(
            $container,
            $dispatcher,
            $filesystem,
            $run(...),
            $save(...),
            $queue(...),
            $delete(...),
        );

        $run
            ->expects(self::once())
            ->method('__invoke')
            ->with($filepath);
        $filesystem
            ->expects(self::once())
            ->method(PropertyHook::get('directory'))
            ->willReturn($directory);
        $filesystem
            ->expects(self::once())
            ->method('get')
            ->with($filepath)
            ->willReturn($resolved);

        self::assertSame($resolved, $resolver->get(new FilePath('file.txt')));
        self::assertEquals(
            [
                new Dependency($filepath, DependencyResult::Found),
            ],
            $dispatcher->events,
        );
    }

    public function testGetException(): void {
        $run        = self::createStub(ResolverTest__Invokable::class);
        $save       = self::createStub(ResolverTest__Invokable::class);
        $queue      = self::createStub(ResolverTest__Invokable::class);
        $delete     = self::createStub(ResolverTest__Invokable::class);
        $container  = self::createStub(Container::class);
        $dispatcher = new ResolverTest__Dispatcher();
        $filesystem = self::createMock(FileSystem::class);
        $filepath   = new FilePath('/directory/path/file.txt');
        $exception  = new Exception();
        $resolver   = new Resolver(
            $container,
            $dispatcher,
            $filesystem,
            $run(...),
            $save(...),
            $queue(...),
            $delete(...),
        );

        $filesystem
            ->expects(self::once())
            ->method('get')
            ->with($filepath)
            ->willThrowException($exception);

        self::expectExceptionObject($exception);

        try {
            $resolver->get($filepath);
        } finally {
            self::assertEquals(
                [
                    new Dependency($filepath, DependencyResult::Found),
                ],
                $dispatcher->events,
            );
        }
    }

    public function testFind(): void {
        $run        = self::createMock(ResolverTest__Invokable::class);
        $save       = self::createStub(ResolverTest__Invokable::class);
        $queue      = self::createStub(ResolverTest__Invokable::class);
        $delete     = self::createStub(ResolverTest__Invokable::class);
        $container  = self::createStub(Container::class);
        $dispatcher = new ResolverTest__Dispatcher();
        $directory  = new DirectoryPath('/directory/path/');
        $filesystem = self::createMock(FileSystem::class);
        $filepath   = new FilePath('/directory/path/file.txt');
        $resolved   = new FileImpl($filesystem, $filepath);
        $resolver   = new Resolver(
            $container,
            $dispatcher,
            $filesystem,
            $run(...),
            $save(...),
            $queue(...),
            $delete(...),
        );

        $run
            ->expects(self::once())
            ->method('__invoke')
            ->with($filepath);
        $filesystem
            ->expects(self::once())
            ->method(PropertyHook::get('directory'))
            ->willReturn($directory);
        $filesystem
            ->expects(self::once())
            ->method('exists')
            ->with($filepath)
            ->willReturn(true);
        $filesystem
            ->expects(self::once())
            ->method('get')
            ->with($filepath)
            ->willReturn($resolved);

        self::assertSame($resolved, $resolver->find(new FilePath('file.txt')));
        self::assertEquals(
            [
                new Dependency($filepath, DependencyResult::Found),
            ],
            $dispatcher->events,
        );
    }

    public function testFindNotFound(): void {
        $run        = self::createStub(ResolverTest__Invokable::class);
        $save       = self::createStub(ResolverTest__Invokable::class);
        $queue      = self::createStub(ResolverTest__Invokable::class);
        $delete     = self::createStub(ResolverTest__Invokable::class);
        $container  = self::createStub(Container::class);
        $dispatcher = new ResolverTest__Dispatcher();
        $filesystem = self::createMock(FileSystem::class);
        $filepath   = new FilePath('/directory/path/file.txt');
        $resolver   = new Resolver(
            $container,
            $dispatcher,
            $filesystem,
            $run(...),
            $save(...),
            $queue(...),
            $delete(...),
        );

        $filesystem
            ->expects(self::once())
            ->method('exists')
            ->with($filepath)
            ->willReturn(false);

        self::assertNull($resolver->find($filepath));
        self::assertEquals(
            [
                new Dependency($filepath, DependencyResult::NotFound),
            ],
            $dispatcher->events,
        );
    }

    public function testFindException(): void {
        $run        = self::createStub(ResolverTest__Invokable::class);
        $save       = self::createStub(ResolverTest__Invokable::class);
        $queue      = self::createStub(ResolverTest__Invokable::class);
        $delete     = self::createStub(ResolverTest__Invokable::class);
        $container  = self::createStub(Container::class);
        $dispatcher = new ResolverTest__Dispatcher();
        $filesystem = self::createMock(FileSystem::class);
        $filepath   = new FilePath('/directory/path/file.txt');
        $exception  = new Exception();
        $resolver   = new Resolver(
            $container,
            $dispatcher,
            $filesystem,
            $run(...),
            $save(...),
            $queue(...),
            $delete(...),
        );

        $filesystem
            ->expects(self::once())
            ->method('exists')
            ->with($filepath)
            ->willReturn(true);
        $filesystem
            ->expects(self::once())
            ->method('get')
            ->with($filepath)
            ->willThrowException($exception);

        self::expectExceptionObject($exception);

        try {
            $resolver->find($filepath);
        } finally {
            self::assertEquals(
                [
                    new Dependency($filepath, DependencyResult::Found),
                ],
                $dispatcher->events,
            );
        }
    }

    public function testSave(): void {
        $run        = self::createStub(ResolverTest__Invokable::class);
        $save       = self::createMock(ResolverTest__Invokable::class);
        $queue      = self::createStub(ResolverTest__Invokable::class);
        $delete     = self::createStub(ResolverTest__Invokable::class);
        $container  = self::createStub(Container::class);
        $dispatcher = new ResolverTest__Dispatcher();
        $directory  = new DirectoryPath('/directory/path/');
        $filesystem = self::createMock(FileSystem::class);
        $filepath   = new FilePath('/directory/path/file.txt');
        $resolved   = new FileImpl($filesystem, $filepath);
        $content    = 'content';
        $resolver   = new Resolver(
            $container,
            $dispatcher,
            $filesystem,
            $run(...),
            $save(...),
            $queue(...),
            $delete(...),
        );

        $save
            ->expects(self::once())
            ->method('__invoke')
            ->with($filepath);
        $filesystem
            ->expects(self::once())
            ->method(PropertyHook::get('directory'))
            ->willReturn($directory);
        $filesystem
            ->expects(self::once())
            ->method('write')
            ->with($filepath, $content)
            ->willReturn($resolved);

        $resolver->save(new FilePath('file.txt'), $content);

        self::assertEquals(
            [
                new Dependency($filepath, DependencyResult::Saved),
            ],
            $dispatcher->events,
        );
    }

    public function testSaveException(): void {
        $run        = self::createStub(ResolverTest__Invokable::class);
        $save       = self::createStub(ResolverTest__Invokable::class);
        $queue      = self::createStub(ResolverTest__Invokable::class);
        $delete     = self::createStub(ResolverTest__Invokable::class);
        $container  = self::createStub(Container::class);
        $dispatcher = new ResolverTest__Dispatcher();
        $filesystem = self::createMock(FileSystem::class);
        $filepath   = new FilePath('/directory/path/file.txt');
        $content    = 'content';
        $exception  = new Exception();
        $resolver   = new Resolver(
            $container,
            $dispatcher,
            $filesystem,
            $run(...),
            $save(...),
            $queue(...),
            $delete(...),
        );

        $filesystem
            ->expects(self::once())
            ->method('write')
            ->with($filepath, $content)
            ->willThrowException($exception);

        self::expectExceptionObject($exception);

        $resolver->save($filepath, $content);

        self::assertEquals(
            [
                new Dependency($filepath, DependencyResult::Saved),
            ],
            $dispatcher->events,
        );
    }

    public function testSaveCastReset(): void {
        $run        = self::createStub(ResolverTest__Invokable::class);
        $save       = self::createMock(ResolverTest__Invokable::class);
        $queue      = self::createStub(ResolverTest__Invokable::class);
        $delete     = self::createStub(ResolverTest__Invokable::class);
        $container  = self::createMock(Container::class);
        $dispatcher = new ResolverTest__Dispatcher();
        $filesystem = self::createMock(FileSystem::class);
        $filepath   = new FilePath('/directory/path/file.txt');
        $resolved   = new FileImpl($filesystem, $filepath);
        $content    = 'content';
        $resolver   = new Resolver(
            $container,
            $dispatcher,
            $filesystem,
            $run(...),
            $save(...),
            $queue(...),
            $delete(...),
        );

        $save
            ->expects(self::once())
            ->method('__invoke')
            ->with($filepath);
        $container
            ->expects(self::once())
            ->method('make')
            ->with(ResolverTest__Cast::class)
            ->willReturn(new ResolverTest__Cast());
        $filesystem
            ->expects(self::once())
            ->method('write')
            ->with($filepath, $content)
            ->willReturn($resolved);

        $value = $resolver->cast($resolved, ResolverTest__Cast::class);

        $resolver->save($filepath, $content);

        self::assertNotSame($value, $resolver->cast($resolved, ResolverTest__Cast::class));
        self::assertEquals(
            [
                new Dependency($filepath, DependencyResult::Saved),
            ],
            $dispatcher->events,
        );
    }

    public function testQueue(): void {
        $run        = self::createStub(ResolverTest__Invokable::class);
        $save       = self::createStub(ResolverTest__Invokable::class);
        $queue      = self::createMock(ResolverTest__Invokable::class);
        $delete     = self::createStub(ResolverTest__Invokable::class);
        $container  = self::createStub(Container::class);
        $dispatcher = new ResolverTest__Dispatcher();
        $directory  = new DirectoryPath('/directory/path/');
        $filesystem = self::createMock(FileSystem::class);
        $filepath   = new FilePath('/directory/path/file.txt');
        $resolver   = new Resolver(
            $container,
            $dispatcher,
            $filesystem,
            $run(...),
            $save(...),
            $queue(...),
            $delete(...),
        );

        $queue
            ->expects(self::once())
            ->method('__invoke')
            ->with($filepath);
        $filesystem
            ->expects(self::once())
            ->method(PropertyHook::get('directory'))
            ->willReturn($directory);

        $resolver->queue(new FilePath('file.txt'));

        self::assertEquals(
            [
                new Dependency($filepath, DependencyResult::Queued),
            ],
            $dispatcher->events,
        );
    }

    public function testQueueIterable(): void {
        $run        = self::createStub(ResolverTest__Invokable::class);
        $save       = self::createStub(ResolverTest__Invokable::class);
        $queue      = self::createMock(ResolverTest__Invokable::class);
        $delete     = self::createStub(ResolverTest__Invokable::class);
        $container  = self::createStub(Container::class);
        $dispatcher = new ResolverTest__Dispatcher();
        $filesystem = self::createStub(FileSystem::class);
        $aFilepath  = new FilePath('/directory/path/a.txt');
        $bFilepath  = new FilePath('/directory/path/b.txt');
        $resolver   = new Resolver(
            $container,
            $dispatcher,
            $filesystem,
            $run(...),
            $save(...),
            $queue(...),
            $delete(...),
        );

        $queue
            ->expects(self::exactly(2))
            ->method('__invoke')
            ->willReturnMap([
                [$aFilepath],
                [$bFilepath],
            ]);

        $resolver->queue([$aFilepath, $bFilepath]);

        self::assertEquals(
            [
                new Dependency($aFilepath, DependencyResult::Queued),
                new Dependency($bFilepath, DependencyResult::Queued),
            ],
            $dispatcher->events,
        );
    }

    public function testDelete(): void {
        $run        = self::createStub(ResolverTest__Invokable::class);
        $save       = self::createStub(ResolverTest__Invokable::class);
        $queue      = self::createStub(ResolverTest__Invokable::class);
        $delete     = self::createMock(ResolverTest__Invokable::class);
        $container  = self::createStub(Container::class);
        $dispatcher = new ResolverTest__Dispatcher();
        $directory  = new DirectoryPath('/directory/path/');
        $filesystem = self::createMock(FileSystem::class);
        $filepath   = new FilePath('/directory/path/file.txt');
        $resolver   = new Resolver(
            $container,
            $dispatcher,
            $filesystem,
            $run(...),
            $save(...),
            $queue(...),
            $delete(...),
        );

        $delete
            ->expects(self::once())
            ->method('__invoke')
            ->with($filepath);
        $filesystem
            ->expects(self::once())
            ->method(PropertyHook::get('directory'))
            ->willReturn($directory);
        $filesystem
            ->expects(self::once())
            ->method('delete')
            ->with($filepath);

        $resolver->delete(new FilePath('file.txt'));

        self::assertEquals(
            [
                new Dependency($filepath, DependencyResult::Deleted),
            ],
            $dispatcher->events,
        );
    }

    public function testDeleteFile(): void {
        $run        = self::createStub(ResolverTest__Invokable::class);
        $save       = self::createStub(ResolverTest__Invokable::class);
        $queue      = self::createStub(ResolverTest__Invokable::class);
        $delete     = self::createMock(ResolverTest__Invokable::class);
        $container  = self::createStub(Container::class);
        $dispatcher = new ResolverTest__Dispatcher();
        $filesystem = self::createMock(FileSystem::class);
        $filepath   = new FilePath('/directory/path/file.txt');
        $resolver   = new Resolver(
            $container,
            $dispatcher,
            $filesystem,
            $run(...),
            $save(...),
            $queue(...),
            $delete(...),
        );

        $delete
            ->expects(self::once())
            ->method('__invoke')
            ->with($filepath);
        $filesystem
            ->expects(self::once())
            ->method('delete')
            ->with($filepath);

        $resolver->delete(new FileImpl($filesystem, $filepath));

        self::assertEquals(
            [
                new Dependency($filepath, DependencyResult::Deleted),
            ],
            $dispatcher->events,
        );
    }

    public function testDeleteIterable(): void {
        $aPath      = new FilePath('/a.txt');
        $bPath      = new DirectoryPath('/a/aa');
        $run        = self::createStub(ResolverTest__Invokable::class);
        $save       = self::createStub(ResolverTest__Invokable::class);
        $queue      = self::createStub(ResolverTest__Invokable::class);
        $delete     = self::createMock(ResolverTest__Invokable::class);
        $container  = self::createStub(Container::class);
        $dispatcher = new ResolverTest__Dispatcher();
        $filesystem = self::createMock(FileSystem::class);
        $resolver   = new Resolver(
            $container,
            $dispatcher,
            $filesystem,
            $run(...),
            $save(...),
            $queue(...),
            $delete(...),
        );

        $delete
            ->expects(self::exactly(2))
            ->method('__invoke')
            ->willReturnMap([
                [$aPath],
                [$bPath],
            ]);
        $filesystem
            ->expects(self::exactly(2))
            ->method('delete')
            ->willReturnMap([
                [$aPath],
                [$bPath],
            ]);

        $resolver->delete([$aPath, $bPath]);

        self::assertEquals(
            [
                new Dependency($aPath, DependencyResult::Deleted),
                new Dependency($bPath, DependencyResult::Deleted),
            ],
            $dispatcher->events,
        );
    }

    public function testSearchNull(): void {
        $run        = self::createStub(ResolverTest__Invokable::class);
        $save       = self::createStub(ResolverTest__Invokable::class);
        $queue      = self::createStub(ResolverTest__Invokable::class);
        $delete     = self::createStub(ResolverTest__Invokable::class);
        $container  = self::createStub(Container::class);
        $dispatcher = self::createStub(Dispatcher::class);
        $directory  = new DirectoryPath('/directory/path/');
        $filesystem = self::createMock(FileSystem::class);
        $include    = ['include'];
        $exclude    = ['exclude'];
        $resolved   = [new FilePath('/directory/path/a.txt'), new FilePath('/directory/path/b.txt')];
        $resolver   = new Resolver(
            $container,
            $dispatcher,
            $filesystem,
            $run(...),
            $save(...),
            $queue(...),
            $delete(...),
        );

        $filesystem
            ->expects(self::once())
            ->method(PropertyHook::get('directory'))
            ->willReturn($directory);
        $filesystem
            ->expects(self::once())
            ->method('search')
            ->with($directory, $include, $exclude, false)
            ->willReturn($resolved);

        self::assertSame($resolved, $resolver->search(include: $include, exclude: $exclude));
    }

    public function testSearchDirectory(): void {
        $run        = self::createStub(ResolverTest__Invokable::class);
        $save       = self::createStub(ResolverTest__Invokable::class);
        $queue      = self::createStub(ResolverTest__Invokable::class);
        $delete     = self::createStub(ResolverTest__Invokable::class);
        $container  = self::createStub(Container::class);
        $dispatcher = self::createStub(Dispatcher::class);
        $directory  = new DirectoryPath('/directory/path/');
        $filesystem = self::createMock(FileSystem::class);
        $include    = ['include'];
        $exclude    = ['exclude'];
        $resolved   = [new FilePath('/directory/path/a.txt'), new FilePath('/directory/path/b.txt')];
        $resolver   = new Resolver(
            $container,
            $dispatcher,
            $filesystem,
            $run(...),
            $save(...),
            $queue(...),
            $delete(...),
        );

        $filesystem
            ->expects(self::once())
            ->method(PropertyHook::get('directory'))
            ->willReturn($directory);
        $filesystem
            ->expects(self::once())
            ->method('search')
            ->with($directory, $include, $exclude, true)
            ->willReturn($resolved);

        self::assertSame($resolved, $resolver->search(new DirectoryPath('.'), $include, $exclude, true));
    }

    #[DataProvider('dataProviderPath')]
    public function testPath(DirectoryPath|FilePath $expected, DirectoryPath|FilePath $path): void {
        $run        = (new ResolverTest__Invokable())(...);
        $save       = (new ResolverTest__Invokable())(...);
        $queue      = (new ResolverTest__Invokable())(...);
        $delete     = (new ResolverTest__Invokable())(...);
        $container  = self::createStub(Container::class);
        $dispatcher = self::createStub(Dispatcher::class);
        $filesystem = self::createStub(FileSystem::class);
        $resolver   = new class($container, $dispatcher, $filesystem, $run, $queue, $save, $delete) extends Resolver {
            #[Override]
            public function path(DirectoryPath|FilePath $path): DirectoryPath|FilePath {
                return parent::path($path);
            }

            public DirectoryPath $input {
                get => new DirectoryPath('/input');
            }

            public DirectoryPath $output {
                get => new DirectoryPath('/output');
            }

            public DirectoryPath $directory {
                get => new DirectoryPath('/directory');
            }
        };

        self::assertEquals($expected->normalized(), $resolver->path($path));
    }

    public function testCast(): void {
        $run        = (new ResolverTest__Invokable())(...);
        $save       = (new ResolverTest__Invokable())(...);
        $queue      = (new ResolverTest__Invokable())(...);
        $delete     = (new ResolverTest__Invokable())(...);
        $container  = self::createMock(Container::class);
        $dispatcher = self::createStub(Dispatcher::class);
        $filesystem = self::createStub(FileSystem::class);
        $resolver   = new Resolver($container, $dispatcher, $filesystem, $run, $save, $queue, $delete);
        $file       = new FileImpl($filesystem, new FilePath('/file.txt'));

        $container
            ->expects(self::once())
            ->method('make')
            ->with(ResolverTest__Cast::class)
            ->willReturn(new ResolverTest__Cast());

        self::assertSame(
            $resolver->cast($file, ResolverTest__Cast::class),
            $resolver->cast($file, ResolverTest__Cast::class),
        );
    }
    //</editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{DirectoryPath|FilePath, DirectoryPath|FilePath}>
     */
    public static function dataProviderPath(): array {
        return [
            'relative directory' => [new DirectoryPath('/directory/relative'), new DirectoryPath('relative')],
            'relative file'      => [new FilePath('/directory/file.txt'), new FilePath('file.txt')],
            'absolute directory' => [new DirectoryPath('/absolute'), new DirectoryPath('/absolute')],
            'absolute file'      => [new FilePath('/file.txt'), new FilePath('/file.txt')],
        ];
    }
    //</editor-fold>
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class ResolverTest__Invokable {
    public function __invoke(FilePath|DirectoryPath $path): void {
        // empty
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 *
 * @implements Cast<object>
 */
class ResolverTest__Cast implements Cast {
    #[Override]
    public function __invoke(Contract $resolver, File $file): object {
        return new class($file->path->path) {
            public function __construct(
                public string $path,
            ) {
                // empty
            }
        };
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class ResolverTest__Dispatcher extends Dispatcher {
    /**
     * @var list<Event>
     */
    public array $events = [];

    public function __construct() {
        parent::__construct(null);
    }

    #[Override]
    public function __invoke(Event $event, ?UnitEnum $result = null): ?UnitEnum {
        $result         = parent::__invoke($event, $result);
        $this->events[] = $event;

        return $result;
    }
}
