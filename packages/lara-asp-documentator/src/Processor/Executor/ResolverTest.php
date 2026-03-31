<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Executor;

use Exception;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Package\WithProcessorDispatcher;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Cast;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Container;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Resolver as Contract;
use LastDragon_ru\LaraASP\Documentator\Processor\Dispatcher;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\PathNotFound;
use LastDragon_ru\LaraASP\Documentator\Processor\Executor\File as FileImpl;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles;
use PHPUnit\Framework\MockObject\Runtime\PropertyHook;

/**
 * @internal
 */
#[CoversClass(Resolver::class)]
#[DisableReturnValueGenerationForTestDoubles]
final class ResolverTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    public function testFile(): void {
        $listener   = self::createStub(Listener::class);
        $container  = self::createStub(Container::class);
        $dispatcher = self::createStub(Dispatcher::class);
        $directory  = new DirectoryPath('/directory/path/');
        $filesystem = self::createMock(FileSystem::class);
        $filepath   = new FilePath('/directory/path/file.txt');

        $filesystem
            ->expects(self::once())
            ->method('exists')
            ->with($filepath)
            ->willReturn(true);
        $filesystem
            ->expects(self::once())
            ->method(PropertyHook::get('input'))
            ->willReturn($directory);

        $resolver = new Resolver($container, $dispatcher, $filesystem, $listener);
        $file     = $resolver->file(new FilePath('file.txt'));

        self::assertEquals(new FilePath('/directory/path/file.txt'), $file->path);
        self::assertSame($file, $resolver->file(new FilePath('file.txt')));
        self::assertSame($file, $resolver->file(new FilePath('/directory/path/file.txt')));
    }

    public function testFileNotFound(): void {
        $listener   = self::createStub(Listener::class);
        $container  = self::createStub(Container::class);
        $dispatcher = self::createStub(Dispatcher::class);
        $directory  = new DirectoryPath('/directory/path/');
        $filesystem = self::createMock(FileSystem::class);
        $filepath   = new FilePath('/directory/path/file.txt');

        $filesystem
            ->expects(self::once())
            ->method('exists')
            ->with($filepath)
            ->willReturn(false);
        $filesystem
            ->expects(self::once())
            ->method(PropertyHook::get('input'))
            ->willReturn($directory);

        self::expectExceptionObject(new PathNotFound($filepath));

        $resolver = new Resolver($container, $dispatcher, $filesystem, $listener);

        $resolver->file(new FilePath('file.txt'));
    }

    public function testGet(): void {
        $listener   = self::createMock(Listener::class);
        $container  = self::createStub(Container::class);
        $dispatcher = new WithProcessorDispatcher();
        $directory  = new DirectoryPath('/directory/path/');
        $filesystem = self::createMock(FileSystem::class);
        $filepath   = new FilePath('/directory/path/file.txt');

        $listener
            ->expects(self::once())
            ->method('run')
            ->with($filepath);
        $filesystem
            ->expects(self::once())
            ->method(PropertyHook::get('input'))
            ->willReturn($directory);
        $filesystem
            ->expects(self::once())
            ->method('exists')
            ->with($filepath)
            ->willReturn(true);

        $resolver = new Resolver($container, $dispatcher, $filesystem, $listener);

        self::assertEquals($filepath, $resolver->get(new FilePath('file.txt'))->path);
        self::assertEquals(
            [
                new Dependency($filepath, DependencyResult::Found),
            ],
            $dispatcher->events,
        );
    }

    public function testGetNotFound(): void {
        $listener   = self::createStub(Listener::class);
        $container  = self::createStub(Container::class);
        $dispatcher = new WithProcessorDispatcher();
        $directory  = new DirectoryPath('/directory/path/');
        $filesystem = self::createMock(FileSystem::class);
        $filepath   = new FilePath('/directory/path/file.txt');

        $filesystem
            ->expects(self::once())
            ->method(PropertyHook::get('input'))
            ->willReturn($directory);
        $filesystem
            ->expects(self::once())
            ->method('exists')
            ->with($filepath)
            ->willReturn(false);

        self::expectExceptionObject(new PathNotFound($filepath));

        try {
            $resolver = new Resolver($container, $dispatcher, $filesystem, $listener);

            $resolver->get(new FilePath('file.txt'));
        } finally {
            self::assertEquals(
                [
                    new Dependency($filepath, DependencyResult::NotFound),
                ],
                $dispatcher->events,
            );
        }
    }

    public function testFind(): void {
        $listener   = self::createMock(Listener::class);
        $container  = self::createStub(Container::class);
        $dispatcher = new WithProcessorDispatcher();
        $directory  = new DirectoryPath('/directory/path/');
        $filesystem = self::createMock(FileSystem::class);
        $filepath   = new FilePath('/directory/path/file.txt');

        $listener
            ->expects(self::once())
            ->method('run')
            ->with($filepath);
        $filesystem
            ->expects(self::once())
            ->method(PropertyHook::get('input'))
            ->willReturn($directory);
        $filesystem
            ->expects(self::once())
            ->method('exists')
            ->with($filepath)
            ->willReturn(true);

        $resolver = new Resolver($container, $dispatcher, $filesystem, $listener);

        self::assertEquals($filepath, $resolver->find(new FilePath('file.txt'))->path ?? null);
        self::assertEquals(
            [
                new Dependency($filepath, DependencyResult::Found),
            ],
            $dispatcher->events,
        );
    }

    public function testFindNotFound(): void {
        $listener   = self::createStub(Listener::class);
        $container  = self::createStub(Container::class);
        $dispatcher = new WithProcessorDispatcher();
        $directory  = new DirectoryPath('/directory/path/');
        $filesystem = self::createMock(FileSystem::class);
        $filepath   = new FilePath('/directory/path/file.txt');

        $filesystem
            ->expects(self::once())
            ->method(PropertyHook::get('input'))
            ->willReturn($directory);
        $filesystem
            ->expects(self::once())
            ->method('exists')
            ->with($filepath)
            ->willReturn(false);

        $resolver = new Resolver($container, $dispatcher, $filesystem, $listener);

        self::assertNull($resolver->find($filepath));
        self::assertEquals(
            [
                new Dependency($filepath, DependencyResult::NotFound),
            ],
            $dispatcher->events,
        );
    }

    public function testRead(): void {
        $listener   = self::createStub(Listener::class);
        $container  = self::createStub(Container::class);
        $dispatcher = self::createStub(Dispatcher::class);
        $directory  = new DirectoryPath('/directory/path/');
        $filesystem = self::createMock(FileSystem::class);
        $filepath   = new FilePath('/directory/path/file.txt');
        $content    = 'content';

        $filesystem
            ->expects(self::once())
            ->method(PropertyHook::get('input'))
            ->willReturn($directory);
        $filesystem
            ->expects(self::once())
            ->method('read')
            ->with($filepath)
            ->willReturn($content);

        $resolver = new Resolver($container, $dispatcher, $filesystem, $listener);
        $actual   = $resolver->read(new FilePath('file.txt'));

        self::assertSame($content, $actual);
    }

    public function testSave(): void {
        $listener   = self::createMock(Listener::class);
        $container  = self::createStub(Container::class);
        $dispatcher = new WithProcessorDispatcher();
        $directory  = new DirectoryPath('/directory/path/');
        $filesystem = self::createMock(FileSystem::class);
        $filepath   = new FilePath('/directory/path/file.txt');
        $content    = 'content';

        $listener
            ->expects(self::once())
            ->method('save')
            ->with($filepath);
        $filesystem
            ->expects(self::once())
            ->method(PropertyHook::get('input'))
            ->willReturn($directory);
        $filesystem
            ->expects(self::once())
            ->method('write')
            ->with($filepath, $content);

        $resolver = new Resolver($container, $dispatcher, $filesystem, $listener);

        $resolver->save(new FilePath('file.txt'), $content);

        self::assertEquals(
            [
                new Dependency($filepath, DependencyResult::Saved),
            ],
            $dispatcher->events,
        );
    }

    public function testSaveException(): void {
        $listener   = self::createStub(Listener::class);
        $container  = self::createStub(Container::class);
        $dispatcher = new WithProcessorDispatcher();
        $directory  = new DirectoryPath('/directory/path/');
        $filesystem = self::createMock(FileSystem::class);
        $filepath   = new FilePath('/directory/path/file.txt');
        $content    = 'content';
        $exception  = new Exception();

        $filesystem
            ->expects(self::once())
            ->method(PropertyHook::get('input'))
            ->willReturn($directory);
        $filesystem
            ->expects(self::once())
            ->method('write')
            ->with($filepath, $content)
            ->willThrowException($exception);

        self::expectExceptionObject($exception);

        $resolver = new Resolver($container, $dispatcher, $filesystem, $listener);

        $resolver->save($filepath, $content);

        self::assertEquals(
            [
                new Dependency($filepath, DependencyResult::Saved),
            ],
            $dispatcher->events,
        );
    }

    public function testSaveCastReset(): void {
        $listener   = self::createMock(Listener::class);
        $container  = self::createMock(Container::class);
        $dispatcher = new WithProcessorDispatcher();
        $directory  = new DirectoryPath('/directory/path/');
        $filesystem = self::createMock(FileSystem::class);
        $filepath   = new FilePath('/directory/path/file.txt');
        $content    = 'content';

        $listener
            ->expects(self::atLeastOnce())
            ->method('run')
            ->with($filepath);
        $listener
            ->expects(self::once())
            ->method('save')
            ->with($filepath);
        $container
            ->expects(self::once())
            ->method('make')
            ->with(ResolverTest__Cast::class)
            ->willReturn(new ResolverTest__Cast());
        $filesystem
            ->expects(self::once())
            ->method(PropertyHook::get('input'))
            ->willReturn($directory);
        $filesystem
            ->expects(self::atLeastOnce())
            ->method('exists')
            ->with($filepath)
            ->willReturn(true);
        $filesystem
            ->expects(self::once())
            ->method('write')
            ->with($filepath, $content);

        $resolver = new Resolver($container, $dispatcher, $filesystem, $listener);
        $value    = $resolver->cast($filepath, ResolverTest__Cast::class);

        $resolver->save($filepath, $content);

        self::assertNotSame($value, $resolver->cast($filepath, ResolverTest__Cast::class));
        self::assertEquals(
            [
                new Dependency($filepath, DependencyResult::Found),
                new Dependency($filepath, DependencyResult::Saved),
                new Dependency($filepath, DependencyResult::Found),
            ],
            $dispatcher->events,
        );
    }

    public function testQueue(): void {
        $listener   = self::createMock(Listener::class);
        $container  = self::createStub(Container::class);
        $dispatcher = new WithProcessorDispatcher();
        $directory  = new DirectoryPath('/directory/path/');
        $filesystem = self::createMock(FileSystem::class);
        $filepath   = new FilePath('/directory/path/file.txt');

        $listener
            ->expects(self::once())
            ->method('queue')
            ->with($filepath);
        $filesystem
            ->expects(self::once())
            ->method(PropertyHook::get('input'))
            ->willReturn($directory);

        $resolver = new Resolver($container, $dispatcher, $filesystem, $listener);

        $resolver->queue(new FilePath('file.txt'));

        self::assertEquals(
            [
                new Dependency($filepath, DependencyResult::Queued),
            ],
            $dispatcher->events,
        );
    }

    public function testQueueIterable(): void {
        $listener   = self::createMock(Listener::class);
        $container  = self::createStub(Container::class);
        $dispatcher = new WithProcessorDispatcher();
        $directory  = new DirectoryPath('/directory/path/');
        $filesystem = self::createMock(FileSystem::class);
        $aFilepath  = new FilePath('/directory/path/a.txt');
        $bFilepath  = new FilePath('/directory/path/b.txt');

        $listener
            ->expects(self::exactly(2))
            ->method('queue')
            ->willReturnMap([
                [$aFilepath],
                [$bFilepath],
            ]);
        $filesystem
            ->expects(self::once())
            ->method(PropertyHook::get('input'))
            ->willReturn($directory);

        $resolver = new Resolver($container, $dispatcher, $filesystem, $listener);

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
        $listener   = self::createMock(Listener::class);
        $container  = self::createStub(Container::class);
        $dispatcher = new WithProcessorDispatcher();
        $directory  = new DirectoryPath('/directory/path/');
        $filesystem = self::createMock(FileSystem::class);
        $filepath   = new FilePath('/directory/path/file.txt');

        $listener
            ->expects(self::once())
            ->method('delete')
            ->with($filepath);
        $filesystem
            ->expects(self::once())
            ->method(PropertyHook::get('input'))
            ->willReturn($directory);
        $filesystem
            ->expects(self::once())
            ->method('delete')
            ->with($filepath);

        $resolver = new Resolver($container, $dispatcher, $filesystem, $listener);

        $resolver->delete(new FilePath('file.txt'));

        self::assertEquals(
            [
                new Dependency($filepath, DependencyResult::Deleted),
            ],
            $dispatcher->events,
        );
    }

    public function testDeleteFile(): void {
        $listener   = self::createMock(Listener::class);
        $container  = self::createStub(Container::class);
        $dispatcher = new WithProcessorDispatcher();
        $directory  = new DirectoryPath('/directory/path/');
        $filesystem = self::createMock(FileSystem::class);
        $filepath   = new FilePath('/directory/path/file.txt');

        $listener
            ->expects(self::once())
            ->method('delete')
            ->with($filepath);
        $filesystem
            ->expects(self::once())
            ->method(PropertyHook::get('input'))
            ->willReturn($directory);
        $filesystem
            ->expects(self::once())
            ->method('delete')
            ->with($filepath);

        $resolver = new Resolver($container, $dispatcher, $filesystem, $listener);

        $resolver->delete(new FileImpl($filepath, $resolver));

        self::assertEquals(
            [
                new Dependency($filepath, DependencyResult::Deleted),
            ],
            $dispatcher->events,
        );
    }

    public function testDeleteFilePath(): void {
        $listener   = self::createMock(Listener::class);
        $container  = self::createStub(Container::class);
        $dispatcher = new WithProcessorDispatcher();
        $directory  = new DirectoryPath('/directory/path/');
        $filesystem = self::createMock(FileSystem::class);
        $filepath   = new FilePath('/directory/path/file.txt');

        $listener
            ->expects(self::once())
            ->method('delete')
            ->with($filepath);
        $filesystem
            ->expects(self::once())
            ->method(PropertyHook::get('input'))
            ->willReturn($directory);
        $filesystem
            ->expects(self::once())
            ->method('delete')
            ->with($filepath);

        $resolver = new Resolver($container, $dispatcher, $filesystem, $listener);

        $resolver->delete(new FilePath('file.txt'));

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
        $listener   = self::createMock(Listener::class);
        $container  = self::createStub(Container::class);
        $dispatcher = new WithProcessorDispatcher();
        $directory  = new DirectoryPath('/directory/path/');
        $filesystem = self::createMock(FileSystem::class);

        $listener
            ->expects(self::exactly(2))
            ->method('delete')
            ->willReturnMap([
                [$aPath],
                [$bPath],
            ]);
        $filesystem
            ->expects(self::once())
            ->method(PropertyHook::get('input'))
            ->willReturn($directory);
        $filesystem
            ->expects(self::exactly(2))
            ->method('delete')
            ->willReturnMap([
                [$aPath],
                [$bPath],
            ]);

        $resolver = new Resolver($container, $dispatcher, $filesystem, $listener);

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
        $listener   = self::createStub(Listener::class);
        $container  = self::createStub(Container::class);
        $dispatcher = self::createStub(Dispatcher::class);
        $directory  = new DirectoryPath('/directory/path/');
        $filesystem = self::createMock(FileSystem::class);
        $include    = ['include'];
        $exclude    = ['exclude'];
        $resolved   = [new FilePath('/directory/path/a.txt'), new FilePath('/directory/path/b.txt')];

        $filesystem
            ->expects(self::once())
            ->method(PropertyHook::get('input'))
            ->willReturn($directory);
        $filesystem
            ->expects(self::once())
            ->method('search')
            ->with($directory, $include, $exclude, false)
            ->willReturn($resolved);

        $resolver = new Resolver($container, $dispatcher, $filesystem, $listener);
        $actual   = $resolver->search(include: $include, exclude: $exclude);

        self::assertSame($resolved, $actual);
    }

    public function testSearchDirectory(): void {
        $listener   = self::createStub(Listener::class);
        $container  = self::createStub(Container::class);
        $dispatcher = self::createStub(Dispatcher::class);
        $directory  = new DirectoryPath('/directory/path/');
        $filesystem = self::createMock(FileSystem::class);
        $include    = ['include'];
        $exclude    = ['exclude'];
        $resolved   = [new FilePath('/directory/path/a.txt'), new FilePath('/directory/path/b.txt')];

        $filesystem
            ->expects(self::once())
            ->method(PropertyHook::get('input'))
            ->willReturn($directory);
        $filesystem
            ->expects(self::once())
            ->method('search')
            ->with($directory, $include, $exclude, true)
            ->willReturn($resolved);

        $resolver = new Resolver($container, $dispatcher, $filesystem, $listener);
        $actual   = $resolver->search(new DirectoryPath('.'), $include, $exclude, true);

        self::assertSame($resolved, $actual);
    }

    #[DataProvider('dataProviderPath')]
    public function testPath(DirectoryPath|FilePath $expected, DirectoryPath|FilePath $path): void {
        $listener   = self::createStub(Listener::class);
        $container  = self::createStub(Container::class);
        $dispatcher = self::createStub(Dispatcher::class);
        $filesystem = self::createStub(FileSystem::class);
        $resolver   = new class($container, $dispatcher, $filesystem, $listener) extends Resolver {
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
        };

        $resolver->begin(new FilePath('/directory/file.txt'));

        self::assertEquals($expected->normalized(), $resolver->path($path));
    }

    public function testCast(): void {
        $listener   = self::createStub(Listener::class);
        $container  = self::createMock(Container::class);
        $dispatcher = self::createStub(Dispatcher::class);
        $directory  = new DirectoryPath('/directory/path/');
        $filesystem = self::createMock(FileSystem::class);

        $container
            ->expects(self::once())
            ->method('make')
            ->with(ResolverTest__Cast::class)
            ->willReturn(new ResolverTest__Cast());
        $filesystem
            ->expects(self::once())
            ->method(PropertyHook::get('input'))
            ->willReturn($directory);

        $resolver = new Resolver($container, $dispatcher, $filesystem, $listener);
        $filepath = new FileImpl(new FilePath('/file.txt'), $resolver);

        self::assertSame(
            $resolver->cast($filepath, ResolverTest__Cast::class),
            $resolver->cast($filepath, ResolverTest__Cast::class),
        );
    }

    public function testPropertyDirectory(): void {
        $listener   = self::createStub(Listener::class);
        $container  = self::createStub(Container::class);
        $dispatcher = self::createStub(Dispatcher::class);
        $directory  = new DirectoryPath('/directory/path/');
        $filesystem = self::createMock(FileSystem::class);

        $filesystem
            ->expects(self::atLeastOnce())
            ->method(PropertyHook::get('input'))
            ->willReturn($directory);

        $resolver = new Resolver($container, $dispatcher, $filesystem, $listener);
        $a        = $directory->file('a/file.txt');
        $b        = $directory->file('b/file.txt');

        self::assertEquals($directory, $resolver->directory);

        $resolver->begin($a);

        self::assertEquals($a->directory(), $resolver->directory);

        $resolver->begin($b);

        self::assertEquals($b->directory(), $resolver->directory);

        $resolver->commit();

        self::assertEquals($a->directory(), $resolver->directory);

        $resolver->commit();

        self::assertEquals($directory, $resolver->directory);
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
