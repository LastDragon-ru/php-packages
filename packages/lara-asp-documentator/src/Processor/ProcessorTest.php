<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor;

use Closure;
use Exception;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Container;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Event;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Resolver as ResolverContract;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\FileTask;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\HookTask;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemDeleteBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemDeleteEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemDeleteResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\HookBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\HookEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\HookResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyCircularDependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyUnavailable;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\PathNotFound;
use LastDragon_ru\LaraASP\Documentator\Processor\Executor\Executor;
use LastDragon_ru\LaraASP\Documentator\Processor\Executor\Iterator;
use LastDragon_ru\LaraASP\Documentator\Processor\Executor\Resolver;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Adapters\SymfonyFileSystem;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use LastDragon_ru\PhpUnit\Utils\TempDirectory;
use LastDragon_ru\PhpUnit\Utils\TestData;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles;
use Symfony\Component\Finder\Finder;

use function array_map;
use function basename;
use function file_put_contents;

/**
 * @internal
 */
#[CoversClass(Processor::class)]
#[CoversClass(Executor::class)]
#[CoversClass(Iterator::class)]
#[CoversClass(Resolver::class)]
#[DisableReturnValueGenerationForTestDoubles]
final class ProcessorTest extends TestCase {
    public function testRun(): void {
        $input = TestData::get()->directory();
        $taskA = new class() extends ProcessorTest__Task {
            #[Override]
            public static function glob(): string {
                return '*.htm';
            }
        };
        $taskB = new class() extends ProcessorTest__Task {
            #[Override]
            public function dependencies(): array {
                return [
                    'a/a.txt'    => [
                        '../b/b/bb.txt',
                        '../c.txt',
                        '../c.html',
                        'excluded.txt',
                    ],
                    'b/b/bb.txt' => [
                        '../../b/a/ba.txt',
                        '../../c.txt',
                    ],
                ];
            }
        };
        $taskC = new class() extends ProcessorTest__Task {
            #[Override]
            public static function glob(): string {
                return '*.htm';
            }

            #[Override]
            public function __invoke(ResolverContract $resolver, File $file): void {
                parent::__invoke($resolver, $file);

                $resolver->queue(
                    new FilePath('../'.basename(__FILE__)),
                );
            }
        };
        $taskD = new class() implements HookTask {
            #[Override]
            public static function hook(): Hook {
                return Hook::File;
            }

            #[Override]
            public function __invoke(ResolverContract $resolver, File $file, Hook $hook): void {
                // empty
            }
        };

        $container = self::createMock(Container::class);
        $container
            ->expects(self::exactly(4))
            ->method('make')
            ->willReturnCallback(
                static function (string $class) use ($taskA, $taskB, $taskC, $taskD): object {
                    return match ($class) {
                        $taskA::class => $taskA,
                        $taskB::class => $taskB,
                        $taskC::class => $taskC,
                        $taskD::class => $taskD,
                        default       => throw new Exception('Should not be called.'),
                    };
                },
            );

        $processor = new Processor($container, new ProcessorTest__Adapter());
        $processor->task($taskA::class);
        $processor->task($taskB::class);
        $processor->task($taskC::class);
        $processor->task($taskD::class);

        $events = [];
        $result = $processor(
            $input,
            $input->parent(),
            ['excluded.txt', '**/**/excluded.txt'],
            static function (Event $event) use (&$events): void {
                $events[] = $event;
            },
        );

        self::assertTrue($result);
        self::assertEquals(
            [
                new ProcessBegin(
                    $input,
                    $input->parent(),
                    [
                        '**/*.htm',
                        '**/*.txt',
                        '**/*.md',
                    ],
                    [
                        'excluded.txt',
                        '**/**/excluded.txt',
                    ],
                ),
                new FileBegin($input->file('a/a.txt')),
                new TaskBegin($taskB::class),
                new Dependency($input->file('b/b/bb.txt'), DependencyResult::Found),
                new FileBegin($input->file('b/b/bb.txt')),
                new TaskBegin($taskB::class),
                new Dependency($input->file('b/a/ba.txt'), DependencyResult::Found),
                new FileBegin($input->file('b/a/ba.txt')),
                new TaskBegin($taskB::class),
                new TaskEnd(TaskResult::Success),
                new TaskBegin($taskD::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new Dependency($input->file('c.txt'), DependencyResult::Found),
                new FileBegin($input->file('c.txt')),
                new TaskBegin($taskB::class),
                new TaskEnd(TaskResult::Success),
                new TaskBegin($taskD::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new TaskEnd(TaskResult::Success),
                new TaskBegin($taskD::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new Dependency($input->file('c.txt'), DependencyResult::Found),
                new Dependency($input->file('c.html'), DependencyResult::Found),
                new FileBegin($input->file('c.html')),
                new TaskBegin($taskD::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new Dependency($input->file('a/excluded.txt'), DependencyResult::Found),
                new TaskEnd(TaskResult::Success),
                new TaskBegin($taskD::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new FileBegin($input->file('a/a/aa.txt')),
                new TaskBegin($taskB::class),
                new TaskEnd(TaskResult::Success),
                new TaskBegin($taskD::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new FileBegin($input->file('a/b/ab.txt')),
                new TaskBegin($taskB::class),
                new TaskEnd(TaskResult::Success),
                new TaskBegin($taskD::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new FileBegin($input->file('b/b.txt')),
                new TaskBegin($taskB::class),
                new TaskEnd(TaskResult::Success),
                new TaskBegin($taskD::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new FileBegin($input->file('c.htm')),
                new TaskBegin($taskA::class),
                new TaskEnd(TaskResult::Success),
                new TaskBegin($taskC::class),
                new Dependency($input->file('../ProcessorTest.php'), DependencyResult::Queued),
                new TaskEnd(TaskResult::Success),
                new TaskBegin($taskD::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new ProcessEnd(ProcessResult::Success),
            ],
            $events,
        );
        self::assertEquals(
            [
                [
                    (string) $input->file('c.htm'),
                    [],
                ],
            ],
            $taskA->processed,
        );
        self::assertEquals(
            [
                [
                    (string) $input->file('b/a/ba.txt'),
                    [],
                ],
                [
                    (string) $input->file('c.txt'),
                    [],
                ],
                [
                    (string) $input->file('b/b/bb.txt'),
                    [
                        '../../b/a/ba.txt' => (string) $input->file('b/a/ba.txt'),
                        '../../c.txt'      => (string) $input->file('c.txt'),
                    ],
                ],
                [
                    (string) $input->file('a/a.txt'),
                    [
                        '../b/b/bb.txt' => (string) $input->file('b/b/bb.txt'),
                        '../c.txt'      => (string) $input->file('c.txt'),
                        '../c.html'     => (string) $input->file('c.html'),
                        'excluded.txt'  => (string) $input->file('a/excluded.txt'),
                    ],
                ],
                [
                    (string) $input->file('a/a/aa.txt'),
                    [],
                ],
                [
                    (string) $input->file('a/b/ab.txt'),
                    [],
                ],
                [
                    (string) $input->file('b/b.txt'),
                    [],
                ],
            ],
            $taskB->processed,
        );
    }

    public function testRunFile(): void {
        $task      = new ProcessorTest__Task();
        $input     = TestData::get()->file('excluded.txt');
        $events    = [];
        $container = self::createMock(Container::class);
        $container
            ->expects(self::exactly(1))
            ->method('make')
            ->willReturnCallback(
                static function (string $class) use ($task): object {
                    return match ($class) {
                        $task::class => $task,
                        default      => throw new Exception('Should not be called.'),
                    };
                },
            );

        $processor = new Processor($container, new ProcessorTest__Adapter());

        $processor->task($task::class);

        $processor($input, onEvent: static function (Event $event) use (&$events): void {
            $events[] = $event;
        });

        self::assertEquals(
            [
                new ProcessBegin($input->directory(), $input->directory(), ['**/*.txt', '**/*.md'], []),
                new FileBegin($input->file('excluded.txt')),
                new TaskBegin(ProcessorTest__Task::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new ProcessEnd(ProcessResult::Success),
            ],
            $events,
        );
        self::assertEquals(
            [
                [
                    (string) $input->file('excluded.txt'),
                    [
                        // empty
                    ],
                ],
            ],
            $task->processed,
        );
    }

    public function testRunEach(): void {
        $taskA     = new ProcessorTest__Task();
        $taskB     = new class() extends ProcessorTest__Task {
            #[Override]
            public static function glob(): string {
                return '*';
            }
        };
        $taskC     = new class() extends ProcessorTest__Task {
            #[Override]
            public static function glob(): string {
                return '*';
            }
        };
        $input     = TestData::get()->file('excluded.txt');
        $events    = [];
        $container = self::createMock(Container::class);
        $container
            ->expects(self::exactly(3))
            ->method('make')
            ->willReturnCallback(
                static function (string $class) use ($taskA, $taskB, $taskC): object {
                    return match ($class) {
                        $taskA::class => $taskA,
                        $taskB::class => $taskB,
                        $taskC::class => $taskC,
                        default       => throw new Exception('Should not be called.'),
                    };
                },
            );

        $processor = new Processor($container, new ProcessorTest__Adapter());

        $processor->task($taskA::class);
        $processor->task($taskB::class);
        $processor->task($taskC::class, -1);

        $processor($input, onEvent: static function (Event $event) use (&$events): void {
            $events[] = $event;
        });

        self::assertEquals(
            [
                new ProcessBegin($input->directory(), $input->directory(), ['**/*.txt', '**/*.md', '**/*'], []),
                new FileBegin($input->file('excluded.txt')),
                new TaskBegin($taskC::class),
                new TaskEnd(TaskResult::Success),
                new TaskBegin($taskA::class),
                new TaskEnd(TaskResult::Success),
                new TaskBegin($taskB::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new ProcessEnd(ProcessResult::Success),
            ],
            $events,
        );
        self::assertEquals(
            [
                [
                    (string) $input->file('excluded.txt'),
                    [
                        // empty
                    ],
                ],
            ],
            $taskA->processed,
        );
    }

    public function testRunWildcard(): void {
        $input     = TestData::get()->directory();
        $events    = [];
        $taskA     = new class() extends ProcessorTest__Task {
            #[Override]
            public function dependencies(): array {
                return [
                    'b/b.html' => [
                        '../a/excluded.txt',
                    ],
                ];
            }

            #[Override]
            public static function glob(): string {
                return '*.html';
            }
        };
        $taskB     = new class() extends ProcessorTest__Task {
            #[Override]
            public static function glob(): string {
                return '*';
            }
        };
        $container = self::createMock(Container::class);
        $container
            ->expects(self::exactly(2))
            ->method('make')
            ->willReturnCallback(
                static function (string $class) use ($taskA, $taskB): object {
                    return match ($class) {
                        $taskA::class => $taskA,
                        $taskB::class => $taskB,
                        default       => throw new Exception('Should not be called.'),
                    };
                },
            );

        $processor = new Processor($container, new ProcessorTest__Adapter());

        $processor->task($taskA::class);
        $processor->task($taskB::class);

        $processor(
            $input,
            null,
            ['excluded.txt', '**/**/excluded.txt'],
            static function (Event $event) use (&$events): void {
                $events[] = $event;
            },
        );

        self::assertEquals(
            [
                new ProcessBegin(
                    $input,
                    $input,
                    [
                        '**/*.html',
                        '**/*',
                    ],
                    [
                        'excluded.txt',
                        '**/**/excluded.txt',
                    ],
                ),
                new FileBegin($input->file('a/a.html')),
                new TaskBegin($taskA::class),
                new TaskEnd(TaskResult::Success),
                new TaskBegin($taskB::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new FileBegin($input->file('a/a.txt')),
                new TaskBegin($taskB::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new FileBegin($input->file('a/a/aa.txt')),
                new TaskBegin($taskB::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new FileBegin($input->file('a/b/ab.txt')),
                new TaskBegin($taskB::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new FileBegin($input->file('b/a/ba.txt')),
                new TaskBegin($taskB::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new FileBegin($input->file('b/b.html')),
                new TaskBegin($taskA::class),
                new Dependency($input->file('a/excluded.txt'), DependencyResult::Found),
                new TaskEnd(TaskResult::Success),
                new TaskBegin($taskB::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new FileBegin($input->file('b/b.txt')),
                new TaskBegin($taskB::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new FileBegin($input->file('b/b/bb.txt')),
                new TaskBegin($taskB::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new FileBegin($input->file('c.htm')),
                new TaskBegin($taskB::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new FileBegin($input->file('c.html')),
                new TaskBegin($taskA::class),
                new TaskEnd(TaskResult::Success),
                new TaskBegin($taskB::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new FileBegin($input->file('c.txt')),
                new TaskBegin($taskB::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new ProcessEnd(ProcessResult::Success),
            ],
            $events,
        );
        self::assertEquals(
            [
                [
                    (string) $input->file('a/a.html'),
                    [],
                ],
                [
                    (string) $input->file('b/b.html'),
                    [
                        '../a/excluded.txt' => (string) $input->file('a/excluded.txt'),
                    ],
                ],
                [
                    (string) $input->file('c.html'),
                    [],
                ],
            ],
            $taskA->processed,
        );
        self::assertEquals(
            [
                [
                    (string) $input->file('a/a.html'),
                    [],
                ],
                [
                    (string) $input->file('a/a.txt'),
                    [],
                ],
                [
                    (string) $input->file('a/a/aa.txt'),
                    [],
                ],
                [
                    (string) $input->file('a/b/ab.txt'),
                    [],
                ],
                [
                    (string) $input->file('b/a/ba.txt'),
                    [],
                ],
                [
                    (string) $input->file('b/b.html'),
                    [],
                ],
                [
                    (string) $input->file('b/b.txt'),
                    [],
                ],
                [
                    (string) $input->file('b/b/bb.txt'),
                    [],
                ],
                [
                    (string) $input->file('c.htm'),
                    [],
                ],
                [
                    (string) $input->file('c.html'),
                    [],
                ],
                [
                    (string) $input->file('c.txt'),
                    [],
                ],
            ],
            $taskB->processed,
        );
    }

    public function testRunOutputInsideInput(): void {
        $input     = TestData::get()->directory();
        $output    = $input->directory('a');
        $events    = [];
        $task      = new class() extends ProcessorTest__Task {
            #[Override]
            public function dependencies(): array {
                return [
                    'b/a/ba.txt' => [
                        '../../a/a.txt',
                    ],
                ];
            }
        };
        $container = self::createMock(Container::class);
        $container
            ->expects(self::exactly(1))
            ->method('make')
            ->willReturnCallback(
                static function (string $class) use ($task): object {
                    return match ($class) {
                        $task::class => $task,
                        default      => throw new Exception('Should not be called.'),
                    };
                },
            );

        $processor = new Processor($container, new ProcessorTest__Adapter());

        $processor->task($task::class);

        $processor(
            $input,
            $output,
            ['excluded.txt', '**/**/excluded.txt'],
            static function (Event $event) use (&$events): void {
                $events[] = $event;
            },
        );

        self::assertEquals(
            [
                new ProcessBegin(
                    $input,
                    $output,
                    [
                        '**/*.txt',
                        '**/*.md',
                    ],
                    [
                        'excluded.txt',
                        '**/**/excluded.txt',
                        'a/**',
                    ],
                ),
                new FileBegin($input->file('b/a/ba.txt')),
                new TaskBegin($task::class),
                new Dependency($output->file('a.txt'), DependencyResult::Found),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new FileBegin($input->file('b/b.txt')),
                new TaskBegin($task::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new FileBegin($input->file('b/b/bb.txt')),
                new TaskBegin($task::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new FileBegin($input->file('c.txt')),
                new TaskBegin($task::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new ProcessEnd(ProcessResult::Success),
            ],
            $events,
        );
        self::assertEquals(
            [
                [
                    (string) $input->file('b/a/ba.txt'),
                    [
                        '../../a/a.txt' => (string) $input->file('a/a.txt'),
                    ],
                ],
                [
                    (string) $input->file('b/b.txt'),
                    [],
                ],
                [
                    (string) $input->file('b/b/bb.txt'),
                    [],
                ],
                [
                    (string) $input->file('c.txt'),
                    [],
                ],
            ],
            $task->processed,
        );
    }

    public function testRunFileNotFound(): void {
        $input     = TestData::get()->directory();
        $task      = new class() extends ProcessorTest__Task {
            #[Override]
            public function dependencies(): array {
                return ['*' => ['404.html']];
            }
        };
        $path      = $input->file('a/404.html');
        $container = self::createMock(Container::class);
        $container
            ->expects(self::exactly(1))
            ->method('make')
            ->willReturnCallback(
                static function (string $class) use ($task): object {
                    return match ($class) {
                        $task::class => $task,
                        default      => throw new Exception('Should not be called.'),
                    };
                },
            );

        $processor = new Processor($container, new ProcessorTest__Adapter());

        $processor->task($task::class);

        self::expectExceptionObject(new PathNotFound($path));

        $processor($input);
    }

    public function testRunCircularDependency(): void {
        $input     = TestData::get()->directory();
        $task      = new class() extends ProcessorTest__Task {
            #[Override]
            public function dependencies(): array {
                return [
                    'a/a.txt'    => ['../b/b.txt'],
                    'b/b.txt'    => ['../b/a/ba.txt'],
                    'b/a/ba.txt' => ['../../c.txt'],
                    'c.txt'      => ['a/a.txt'],
                ];
            }
        };
        $container = self::createMock(Container::class);
        $container
            ->expects(self::exactly(1))
            ->method('make')
            ->willReturnCallback(
                static function (string $class) use ($task): object {
                    return match ($class) {
                        $task::class => $task,
                        default      => throw new Exception('Should not be called.'),
                    };
                },
            );

        $processor = new Processor($container, new ProcessorTest__Adapter());
        $processor->task($task::class);

        self::expectException(DependencyCircularDependency::class);
        self::expectExceptionMessage(
            <<<MESSAGE
            Circular Dependency detected:

            * {$input->file('a/a.txt')}
            * {$input->file('b/b.txt')}
            * {$input->file('b/a/ba.txt')}
            * {$input->file('c.txt')}
            ! {$input->file('a/a.txt')}
            MESSAGE,
        );

        $processor($input);
    }

    public function testRunCircularDependencySelf(): void {
        $input     = TestData::get()->directory('a/a');
        $task      = new class() extends ProcessorTest__Task {
            #[Override]
            public function dependencies(): array {
                return [
                    'aa.txt' => ['aa.txt'],
                ];
            }
        };
        $container = self::createMock(Container::class);
        $container
            ->expects(self::exactly(1))
            ->method('make')
            ->willReturnCallback(
                static function (string $class) use ($task): object {
                    return match ($class) {
                        $task::class => $task,
                        default      => throw new Exception('Should not be called.'),
                    };
                },
            );

        $processor = new Processor($container, new ProcessorTest__Adapter());

        $processor->task($task::class);

        $processor($input);

        self::assertEquals(
            [
                [
                    (string) $input->file('aa.txt'),
                    [
                        'aa.txt' => (string) $input->file('aa.txt'),
                    ],
                ],
                [
                    (string) $input->file('excluded.txt'),
                    [
                        // empty
                    ],
                ],
            ],
            $task->processed,
        );
    }

    public function testRunCircularDependencySelfThrough(): void {
        $input     = TestData::get()->directory('a/a');
        $task      = new class() extends ProcessorTest__Task {
            #[Override]
            public function dependencies(): array {
                return [
                    'aa.txt'       => ['excluded.txt'],
                    'excluded.txt' => ['aa.txt'],
                ];
            }
        };
        $container = self::createMock(Container::class);
        $container
            ->expects(self::exactly(1))
            ->method('make')
            ->willReturnCallback(
                static function (string $class) use ($task): object {
                    return match ($class) {
                        $task::class => $task,
                        default      => throw new Exception('Should not be called.'),
                    };
                },
            );

        $processor = new Processor($container, new ProcessorTest__Adapter());

        $processor->task($task::class);

        self::expectException(DependencyCircularDependency::class);
        self::expectExceptionMessage(
            <<<MESSAGE
            Circular Dependency detected:

            * {$input->file('aa.txt')}
            * {$input->file('excluded.txt')}
            ! {$input->file('aa.txt')}
            MESSAGE,
        );

        $processor($input);
    }

    public function testRunCircularDependencyNotWritable(): void {
        $events    = [];
        $output    = TestData::get()->directory('b');
        $input     = TestData::get()->directory('a');
        $task      = new class() extends ProcessorTest__Task {
            #[Override]
            public function dependencies(): array {
                return [
                    'a/aa.txt' => ['../a.txt'],
                ];
            }
        };
        $container = self::createMock(Container::class);
        $container
            ->expects(self::exactly(1))
            ->method('make')
            ->willReturnCallback(
                static function (string $class) use ($task): object {
                    return match ($class) {
                        $task::class => $task,
                        default      => throw new Exception('Should not be called.'),
                    };
                },
            );

        $processor = new Processor($container, new ProcessorTest__Adapter());

        $processor->task($task::class);

        $processor(
            $input,
            $output,
            ['excluded.txt', '**/**/excluded.txt'],
            static function (Event $event) use (&$events): void {
                $events[] = $event;
            },
        );

        self::assertEquals(
            [
                new ProcessBegin(
                    $input,
                    $output,
                    [
                        '**/*.txt',
                        '**/*.md',
                    ],
                    [
                        'excluded.txt',
                        '**/**/excluded.txt',
                    ],
                ),
                new FileBegin($input->file('a.txt')),
                new TaskBegin($task::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new FileBegin($input->file('a/aa.txt')),
                new TaskBegin($task::class),
                new Dependency($input->file('a.txt'), DependencyResult::Found),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new FileBegin($input->file('b/ab.txt')),
                new TaskBegin($task::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new ProcessEnd(ProcessResult::Success),
            ],
            $events,
        );
        self::assertEquals(
            [
                [
                    (string) $input->file('a.txt'),
                    [],
                ],
                [
                    (string) $input->file('a/aa.txt'),
                    [
                        '../a.txt' => (string) $input->file('a.txt'),
                    ],
                ],
                [
                    (string) $input->file('b/ab.txt'),
                    [],
                ],
            ],
            $task->processed,
        );
    }

    public function testRunHookBeforeProcessing(): void {
        $events    = [];
        $input     = TestData::get()->file('excluded.txt');
        $task      = new class() implements HookTask {
            #[Override]
            public static function hook(): array {
                return [Hook::Before];
            }

            #[Override]
            public function __invoke(ResolverContract $resolver, File $file, Hook $hook): void {
                $resolver->get(new FilePath('c.txt'));
                $resolver->queue(new FilePath('c.htm'));
            }
        };
        $container = self::createMock(Container::class);
        $container
            ->expects(self::exactly(1))
            ->method('make')
            ->willReturnCallback(
                static function (string $class) use ($task): object {
                    return match ($class) {
                        $task::class => $task,
                        default      => throw new Exception('Should not be called.'),
                    };
                },
            );

        $processor = new Processor($container, new ProcessorTest__Adapter());

        $processor->task($task::class);

        $processor($input, onEvent: static function (Event $event) use (&$events): void {
            $events[] = $event;
        });

        self::assertEquals(
            [
                new ProcessBegin($input->directory(), $input->directory(), [], []),
                new HookBegin(Hook::Before, $input->file('excluded.txt')),
                new TaskBegin($task::class),
                new Dependency($input->file('c.txt'), DependencyResult::Found),
                new Dependency($input->file('c.htm'), DependencyResult::Queued),
                new TaskEnd(TaskResult::Success),
                new HookEnd(HookResult::Success),
                new FileBegin($input->file('excluded.txt')),
                new FileEnd(FileResult::Skipped),
                new ProcessEnd(ProcessResult::Success),
            ],
            $events,
        );
    }

    public function testRunHookAfterProcessing(): void {
        $events    = [];
        $input     = TestData::get()->file('excluded.txt');
        $task      = new class() implements HookTask {
            #[Override]
            public static function hook(): Hook {
                return Hook::After;
            }

            #[Override]
            public function __invoke(ResolverContract $resolver, File $file, Hook $hook): void {
                $resolver->get(new FilePath('c.txt'));
            }
        };
        $container = self::createMock(Container::class);
        $container
            ->expects(self::exactly(1))
            ->method('make')
            ->willReturnCallback(
                static function (string $class) use ($task): object {
                    return match ($class) {
                        $task::class => $task,
                        default      => throw new Exception('Should not be called.'),
                    };
                },
            );

        $processor = new Processor($container, new ProcessorTest__Adapter());

        $processor->task($task::class);

        $processor($input, onEvent: static function (Event $event) use (&$events): void {
            $events[] = $event;
        });

        self::assertEquals(
            [
                new ProcessBegin($input->directory(), $input->directory(), [], []),
                new FileBegin($input->file('excluded.txt')),
                new FileEnd(FileResult::Skipped),
                new HookBegin(Hook::After, $input->file('excluded.txt')),
                new TaskBegin($task::class),
                new Dependency($input->file('c.txt'), DependencyResult::Found),
                new TaskEnd(TaskResult::Success),
                new HookEnd(HookResult::Success),
                new ProcessEnd(ProcessResult::Success),
            ],
            $events,
        );
    }

    public function testRunHookAfterProcessingQueue(): void {
        $input     = TestData::get()->file('excluded.txt');
        $task      = new class() implements HookTask {
            #[Override]
            public static function hook(): array {
                return [Hook::After];
            }

            #[Override]
            public function __invoke(ResolverContract $resolver, File $file, Hook $hook): void {
                $resolver->queue(new FilePath('c.txt'));
            }
        };
        $container = self::createMock(Container::class);
        $container
            ->expects(self::exactly(1))
            ->method('make')
            ->willReturnCallback(
                static function (string $class) use ($task): object {
                    return match ($class) {
                        $task::class => $task,
                        default      => throw new Exception('Should not be called.'),
                    };
                },
            );

        $processor = new Processor($container, new ProcessorTest__Adapter());

        $processor->task($task::class);

        self::expectException(DependencyUnavailable::class);

        $processor($input);
    }

    public function testRunOnError(): void {
        $exception = null;
        $events    = [];
        $input     = TestData::get()->file('excluded.txt');
        $task      = new class() implements HookTask {
            #[Override]
            public static function hook(): array {
                return [Hook::Before];
            }

            #[Override]
            public function __invoke(ResolverContract $resolver, File $file, Hook $hook): void {
                throw new Exception();
            }
        };
        $container = self::createMock(Container::class);
        $container
            ->expects(self::exactly(1))
            ->method('make')
            ->willReturnCallback(
                static function (string $class) use ($task): object {
                    return match ($class) {
                        $task::class => $task,
                        default      => throw new Exception('Should not be called.'),
                    };
                },
            );

        $processor = new Processor($container, new ProcessorTest__Adapter());

        $processor->task($task::class);

        $result = $processor(
            $input,
            onEvent: static function (Event $event) use (&$events): void {
                $events[] = $event;
            },
            onError: static function (Exception $e) use (&$exception): void {
                $exception = $e;
            },
        );

        self::assertFalse($result);
        self::assertInstanceOf(Exception::class, $exception);
        self::assertEquals(
            [
                new ProcessBegin($input->directory(), $input->directory(), [], []),
                new HookBegin(Hook::Before, $input->file('excluded.txt')),
                new TaskBegin($task::class),
                new TaskEnd(TaskResult::Error),
                new HookEnd(HookResult::Error),
                new ProcessEnd(ProcessResult::Error),
            ],
            $events,
        );
    }

    public function testRunDelete(): void {
        $temp  = new TempDirectory();
        $input = $temp->path;
        $taskA = new class() implements FileTask {
            #[Override]
            public static function glob(): string {
                return '*.htm';
            }

            #[Override]
            public function __invoke(ResolverContract $resolver, File $file): void {
                $file->save('new content');
            }
        };
        $taskB = new class() implements FileTask {
            #[Override]
            public static function glob(): string {
                return '*.htm';
            }

            #[Override]
            public function __invoke(ResolverContract $resolver, File $file): void {
                $file->delete();
            }
        };
        $taskC = new class() implements FileTask {
            #[Override]
            public static function glob(): string {
                return '*.htm';
            }

            #[Override]
            public function __invoke(ResolverContract $resolver, File $file): void {
                // empty
            }
        };
        $taskD = new class() implements HookTask {
            #[Override]
            public static function hook(): Hook {
                return Hook::After;
            }

            #[Override]
            public function __invoke(ResolverContract $resolver, File $file, Hook $hook): void {
                // empty
            }
        };

        $file = $input->file('file.htm');

        self::assertNotFalse(file_put_contents($file->path, 'content'));

        $container = self::createMock(Container::class);
        $container
            ->expects(self::exactly(4))
            ->method('make')
            ->willReturnCallback(
                static function (string $class) use ($taskA, $taskB, $taskC, $taskD): object {
                    return match ($class) {
                        $taskA::class => $taskA,
                        $taskB::class => $taskB,
                        $taskC::class => $taskC,
                        $taskD::class => $taskD,
                        default       => throw new Exception('Should not be called.'),
                    };
                },
            );

        $processor = new Processor($container, new ProcessorTest__Adapter());
        $processor->task($taskA::class);
        $processor->task($taskB::class);
        $processor->task($taskC::class);
        $processor->task($taskD::class);

        $events = [];
        $result = $processor(
            $input,
            $input,
            [],
            static function (Event $event) use (&$events): void {
                $events[] = $event;
            },
        );

        self::assertTrue($result);
        self::assertEquals(
            [
                new ProcessBegin(
                    $input,
                    $input,
                    [
                        '**/*.htm',
                    ],
                    [
                        // empty
                    ],
                ),
                new FileBegin($file),
                new TaskBegin($taskA::class),
                new Dependency($file, DependencyResult::Saved),
                new TaskEnd(TaskResult::Success),
                new TaskBegin($taskB::class),
                new Dependency($file, DependencyResult::Deleted),
                new FileSystemDeleteBegin($file),
                new FileSystemDeleteEnd(FileSystemDeleteResult::Success),
                new TaskEnd(TaskResult::Success),
                new TaskBegin($taskC::class),
                new TaskEnd(TaskResult::Skipped),
                new FileEnd(FileResult::Success),
                new HookBegin(Hook::After, $file),
                new TaskBegin($taskD::class),
                new TaskEnd(TaskResult::Skipped),
                new HookEnd(HookResult::Success),
                new ProcessEnd(ProcessResult::Success),
            ],
            $events,
        );
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class ProcessorTest__Task implements FileTask {
    /**
     * @var array<array-key, array{string, array<string, mixed>}>
     */
    public array $processed = [];

    public function __construct() {
        // empty
    }

    /**
     * @return array<string, list<non-empty-string>>
     */
    public function dependencies(): array {
        return [];
    }

    #[Override]
    public static function glob(): array|string {
        return ['*.txt', '*.md'];
    }

    #[Override]
    public function __invoke(ResolverContract $resolver, File $file): void {
        $resolved     = [];
        $relative     = ($resolver->input->relative($file->path) ?? $file->path)->path;
        $dependencies = $this->dependencies();
        $dependencies = $dependencies[$relative] ?? $dependencies['*'] ?? [];

        foreach ($dependencies as $dependency) {
            $resolved[$dependency] = $resolver->get(new FilePath($dependency));
        }

        $this->processed[] = [
            (string) $file->path,
            array_map(
                static function (File $file): string {
                    return (string) $file->path;
                },
                $resolved,
            ),
        ];
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class ProcessorTest__Adapter extends SymfonyFileSystem {
    #[Override]
    protected function getFinder(
        DirectoryPath $directory,
        ?Closure $include,
        ?Closure $exclude,
        bool $hidden,
    ): Finder {
        return parent::getFinder($directory, $include, $exclude, $hidden)
            ->sortByName(true);
    }
}
