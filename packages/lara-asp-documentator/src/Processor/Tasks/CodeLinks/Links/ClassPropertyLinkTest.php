<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Executor\Resolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Formats\Php\PhpFile;
use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeFinder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Runtime\PropertyHook;

use function array_map;

/**
 * @internal
 */
#[CoversClass(ClassPropertyLink::class)]
final class ClassPropertyLinkTest extends TestCase {
    public function testToString(): void {
        self::assertSame('Class::$property', (string) new ClassPropertyLink('Class', 'property'));
        self::assertSame('App\\Class::$property', (string) new ClassPropertyLink('App\\Class', 'property'));
        self::assertSame(
            '\\App\\Class::$property',
            (string) new ClassPropertyLink('\\App\\Class', 'property'),
        );
    }

    public function testGetTitle(): void {
        self::assertSame('Class::$property', (new ClassPropertyLink('Class', 'property'))->getTitle());
        self::assertSame('Class::$property', (new ClassPropertyLink('App\\Class', 'property'))->getTitle());
        self::assertSame(
            'Class::$property',
            (new ClassPropertyLink('\\App\\Class', 'property'))->getTitle(),
        );
    }

    public function testGetTargetNode(): void {
        $file = self::createMock(File::class);
        $file
            ->expects(self::once())
            ->method(PropertyHook::get('content'))
            ->willReturn(
                <<<'PHP'
                <?php declare(strict_types = 1);

                class A {
                    protected int $property = 123;
                }
                PHP,
            );

        $link = new class ('A', 'property') extends ClassPropertyLink {
            #[Override]
            public function getTargetNode(ClassLike $class): ?Node {
                return parent::getTargetNode($class);
            }
        };

        $resolver = self::createStub(Resolver::class);
        $stmts    = (new PhpFile())->read($resolver, $file)->stmts;
        $class    = (new NodeFinder())->findFirstInstanceOf($stmts, ClassLike::class);
        $actual   = $class !== null
            ? $link->getTargetNode($class)
            : null;

        self::assertInstanceOf(Property::class, $actual);
        self::assertEquals(
            ['property'],
            array_map(
                static fn ($p) => $p->name->name,
                $actual->props,
            ),
        );
    }

    public function testGetTargetNodePromoted(): void {
        $file = self::createMock(File::class);
        $file
            ->expects(self::once())
            ->method(PropertyHook::get('content'))
            ->willReturn(
                <<<'PHP'
                <?php declare(strict_types = 1);

                class A {
                    public function __construct(
                        protected int $property = 123,
                    ) {
                        // empty
                    }
                }
                PHP,
            );

        $link = new class ('A', 'property') extends ClassPropertyLink {
            #[Override]
            public function getTargetNode(ClassLike $class): ?Node {
                return parent::getTargetNode($class);
            }
        };

        $resolver = self::createStub(Resolver::class);
        $stmts    = (new PhpFile())->read($resolver, $file)->stmts;
        $class    = (new NodeFinder())->findFirstInstanceOf($stmts, ClassLike::class);
        $actual   = $class !== null
            ? $link->getTargetNode($class)
            : null;

        self::assertInstanceOf(Param::class, $actual);
        self::assertInstanceOf(Variable::class, $actual->var);
        self::assertSame('property', $actual->var->name);
    }
}
