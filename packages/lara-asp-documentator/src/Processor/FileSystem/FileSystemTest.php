<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use Exception;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Package\WithProcessor;
use LastDragon_ru\LaraASP\Documentator\Package\WithProcessorDispatcher;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Adapter;
use LastDragon_ru\LaraASP\Documentator\Processor\Dispatcher;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemDeleteBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemDeleteEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemDeleteResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemReadBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemReadEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemReadResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemWriteBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemWriteEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemWriteResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\PathNotFound;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\PathNotWritable;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\PathUnavailable;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use LastDragon_ru\PhpUnit\Utils\TestData;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles;

use function array_map;
use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(FileSystem::class)]
#[DisableReturnValueGenerationForTestDoubles]
final class FileSystemTest extends TestCase {
    use WithProcessor;

    // <editor-fold desc="Tests">
    // =========================================================================
    public function testExists(): void {
        $fs   = $this->getFileSystem(__DIR__);
        $base = TestData::get()->directory();

        self::assertTrue($fs->exists($base->file('c.txt')));
        self::assertFalse($fs->exists($base->file('404.txt')));
    }

    public function testSearch(): void {
        $input      = TestData::get()->directory();
        $filesystem = $this->getFileSystem($input);
        $directory  = $input;
        $map        = static function (DirectoryPath|FilePath $path) use ($directory): string {
            return (string) $directory->relative($path);
        };

        self::assertEquals(
            [
                'a/',
                'a/a/',
                'a/a.html',
                'a/a.txt',
                'a/a/aa.txt',
                'a/b/',
                'a/b/ab.txt',
                'b/',
                'b/a/',
                'b/a/ba.txt',
                'b/b/',
                'b/b.html',
                'b/b.txt',
                'b/b/bb.txt',
                'c.html',
                'c.txt',
            ],
            array_map($map, iterator_to_array($filesystem->search($directory), false)),
        );

        self::assertEquals(
            [
                'c.html',
            ],
            array_map($map, iterator_to_array($filesystem->search($directory, ['*.html']), false)),
        );

        self::assertEquals(
            [
                'a/a.html',
                'b/b.html',
                'c.html',
            ],
            array_map($map, iterator_to_array($filesystem->search($directory, ['**/*.html']), false)),
        );

        self::assertEquals(
            [
                'a/',
                'a/a/',
                'a/a.html',
                'a/b/',
                'b/',
                'b/a/',
                'b/b/',
                'b/b.html',
                'c.html',
            ],
            array_map(
                $map,
                iterator_to_array($filesystem->search($directory, exclude: ['*.txt', '**/**/*.txt']), false),
            ),
        );

        self::assertEquals(
            [
                'a/.a.txt',
                'a/a.txt',
                'a/a/aa.txt',
                'a/b/ab.txt',
            ],
            array_map(
                $map,
                iterator_to_array($filesystem->search($directory, include: ['a/**/*.txt'], hidden: true), false),
            ),
        );

        self::assertEquals(
            [
                'b/.b/',
                'b/a/',
                'b/b/',
            ],
            array_map(
                $map,
                iterator_to_array($filesystem->search($directory, include: ['b/*/'], hidden: true), false),
            ),
        );
    }

    public function testSearchDirectoryNotFound(): void {
        self::expectException(PathNotFound::class);

        $fs        = $this->getFileSystem(__DIR__);
        $directory = $fs->input->resolve(new DirectoryPath('not found'));

        iterator_to_array($fs->search($directory));
    }

    public function testRead(): void {
        $content = 'content';
        $input   = TestData::get()->directory();
        $path    = $input->file('file.md');
        $adapter = self::createMock(Adapter::class);
        $adapter
            ->expects($this->once())
            ->method('read')
            ->with($path)
            ->willReturn($content);

        $dispatcher = new WithProcessorDispatcher();
        $filesystem = new FileSystem($adapter, $dispatcher, $input, $input);

        self::assertSame($content, $filesystem->read($path));
        self::assertEquals(
            [
                new FileSystemReadBegin($path),
                new FileSystemReadEnd(FileSystemReadResult::Success, 7),
            ],
            $dispatcher->events,
        );
    }

    public function testReadError(): void {
        $input   = TestData::get()->directory();
        $path    = $input->file('file.md');
        $adapter = self::createMock(Adapter::class);
        $adapter
            ->expects($this->once())
            ->method('read')
            ->with($path)
            ->willThrowException(new Exception());

        $dispatcher = new WithProcessorDispatcher();
        $filesystem = new FileSystem($adapter, $dispatcher, $input, $input);

        self::expectException(Exception::class);

        try {
            $filesystem->read($path);
        } finally {
            self::assertEquals(
                [
                    new FileSystemReadBegin($path),
                    new FileSystemReadEnd(FileSystemReadResult::Error, 0),
                ],
                $dispatcher->events,
            );
        }
    }

    public function testWriteNew(): void {
        $content = 'content';
        $input   = TestData::get()->directory();
        $path    = $input->file('file.md');
        $adapter = self::createMock(Adapter::class);
        $adapter
            ->expects($this->once())
            ->method('exists')
            ->with($path)
            ->willReturn(false);
        $adapter
            ->expects($this->once())
            ->method('write')
            ->with($path, $content);
        $adapter
            ->expects($this->once())
            ->method('read')
            ->with($path)
            ->willReturn($content);

        $dispatcher = new WithProcessorDispatcher();
        $filesystem = new FileSystem($adapter, $dispatcher, $input, $input);

        $filesystem->write($path, $content);

        self::assertSame($content, $filesystem->read($path));
        self::assertEquals(
            [
                new FileSystemWriteBegin($path),
                new FileSystemWriteEnd(FileSystemWriteResult::Success, 7),
                new FileSystemReadBegin($path),
                new FileSystemReadEnd(FileSystemReadResult::Success, 7),
            ],
            $dispatcher->events,
        );
    }

    public function testWriteExisting(): void {
        $content = 'content';
        $input   = TestData::get()->directory();
        $path    = $input->file('file.md');
        $adapter = self::createMock(Adapter::class);
        $adapter
            ->expects($this->once())
            ->method('exists')
            ->with($path)
            ->willReturn(true);

        $dispatcher = new WithProcessorDispatcher();
        $filesystem = new FileSystem($adapter, $dispatcher, $input, $input);

        $filesystem->write($path, $content);

        self::assertSame($content, $filesystem->read($path));
        self::assertEquals(
            [
                // empty
            ],
            $dispatcher->events,
        );
    }

    public function testWriteError(): void {
        self::expectException(Exception::class);

        $content = 'content';
        $input   = TestData::get()->directory();
        $path    = $input->file('file.md');
        $adapter = self::createMock(Adapter::class);
        $adapter
            ->expects($this->once())
            ->method('exists')
            ->with($path)
            ->willReturn(false);
        $adapter
            ->expects($this->once())
            ->method('write')
            ->with($path, $content)
            ->willThrowException(new Exception());

        $dispatcher = new WithProcessorDispatcher();
        $filesystem = new FileSystem($adapter, $dispatcher, $input, $input);

        try {
            $filesystem->write($path, $content);
        } finally {
            self::assertEquals(
                [
                    new FileSystemWriteBegin($path),
                    new FileSystemWriteEnd(FileSystemWriteResult::Error, 0),
                ],
                $dispatcher->events,
            );
        }
    }

    public function testWriteExternal(): void {
        self::expectException(PathUnavailable::class);

        $input      = TestData::get()->directory();
        $adapter    = self::createStub(Adapter::class);
        $dispatcher = self::createStub(Dispatcher::class);
        $filesystem = new FileSystem($adapter, $dispatcher, $input, $input);

        $filesystem->write(new FilePath('../Processor.php'), 'external');
    }

    public function testWriteOutsideOutput(): void {
        self::expectException(PathNotWritable::class);

        $base       = TestData::get()->directory();
        $input      = $base->directory('input');
        $output     = $base->directory('output');
        $adapter    = self::createStub(Adapter::class);
        $dispatcher = self::createStub(Dispatcher::class);
        $filesystem = new FileSystem($adapter, $dispatcher, $input, $output);

        $filesystem->write($input->file('file.txt'), 'input');
    }

    public function testDelete(): void {
        $input      = TestData::get()->directory();
        $path       = $input->file('file.md');
        $adapter    = self::createMock(Adapter::class);
        $dispatcher = new WithProcessorDispatcher();
        $filesystem = new FileSystem($adapter, $dispatcher, $input, $input);

        $adapter
            ->expects($this->once())
            ->method('delete')
            ->with($path);

        $filesystem->delete($path);

        self::assertEquals(
            [
                new FileSystemDeleteBegin($path),
                new FileSystemDeleteEnd(FileSystemDeleteResult::Success),
            ],
            $dispatcher->events,
        );
    }

    public function testDeleteError(): void {
        self::expectException(Exception::class);

        $input      = TestData::get()->directory();
        $path       = $input->file('file.md');
        $adapter    = self::createMock(Adapter::class);
        $dispatcher = new WithProcessorDispatcher();
        $filesystem = new FileSystem($adapter, $dispatcher, $input, $input);

        $adapter
            ->expects($this->once())
            ->method('delete')
            ->with($path)
            ->willThrowException(new Exception());

        try {
            $filesystem->delete($path);
        } finally {
            self::assertEquals(
                [
                    new FileSystemDeleteBegin($path),
                    new FileSystemDeleteEnd(FileSystemDeleteResult::Error),
                ],
                $dispatcher->events,
            );
        }
    }

    public function testDeleteExternal(): void {
        self::expectException(PathUnavailable::class);

        $input      = TestData::get()->directory();
        $adapter    = self::createStub(Adapter::class);
        $dispatcher = self::createStub(Dispatcher::class);
        $filesystem = new FileSystem($adapter, $dispatcher, $input, $input);

        $filesystem->delete(new FilePath('../Processor.php'));
    }

    public function testDeleteOutsideOutput(): void {
        self::expectException(PathNotWritable::class);

        $base       = TestData::get()->directory();
        $input      = $base->directory('input');
        $output     = $base->directory('output');
        $adapter    = self::createStub(Adapter::class);
        $dispatcher = self::createStub(Dispatcher::class);
        $filesystem = new FileSystem($adapter, $dispatcher, $input, $output);

        $filesystem->delete($input->file('file.txt'));
    }

    public function testPropertyDirectory(): void {
        $base       = TestData::get()->directory();
        $input      = $base->directory('input');
        $output     = $base->directory('output');
        $adapter    = self::createStub(Adapter::class);
        $dispatcher = self::createStub(Dispatcher::class);
        $filesystem = new FileSystem($adapter, $dispatcher, $input, $output);
        $a          = $filesystem->input->directory('a');
        $b          = $filesystem->input->directory('b');

        self::assertSame($filesystem->input, $filesystem->directory);

        $filesystem->begin($a);

        self::assertSame($a, $filesystem->directory);

        $filesystem->begin($b);

        self::assertSame($b, $filesystem->directory);

        $filesystem->commit();

        self::assertSame($a, $filesystem->directory);

        $filesystem->commit();

        self::assertSame($filesystem->input, $filesystem->directory);
    }

    #[DataProvider('dataProviderPath')]
    public function testPath(Exception|DirectoryPath|FilePath $expected, DirectoryPath|FilePath $path): void {
        $dispatcher = self::createStub(Dispatcher::class);
        $adapter    = self::createStub(Adapter::class);
        $output     = new DirectoryPath('/output');
        $input      = new DirectoryPath('/input');
        $fs         = new class($adapter, $dispatcher, $input, $output) extends FileSystem {
            #[Override]
            public function path(DirectoryPath|FilePath $path): DirectoryPath|FilePath {
                return parent::path($path);
            }
        };

        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        $actual = $fs->path($path);

        self::assertInstanceOf($expected::class, $actual);
        self::assertNotInstanceOf(Exception::class, $expected);
        self::assertSame($expected->path, $actual->path);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{Exception|DirectoryPath|FilePath, DirectoryPath|FilePath}>
     */
    public static function dataProviderPath(): array {
        return [
            'relative'      => [
                new PathUnavailable(new FilePath('file.txt')),
                new FilePath('file.txt'),
            ],
            'external'      => [
                new PathUnavailable(new FilePath('/file.txt')),
                new FilePath('/file.txt'),
            ],
            'inside input'  => [
                new FilePath('/input/file.txt'),
                new FilePath('/input/file.txt'),
            ],
            'inside output' => [
                new DirectoryPath('/output/directory/'),
                new DirectoryPath('/output/directory'),
            ],
        ];
    }
    // </editor-fold>
}
