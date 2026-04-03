<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Executor\Resolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Formats\Php\PhpFile;
use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeFinder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles;
use PHPUnit\Framework\MockObject\Runtime\PropertyHook;

/**
 * @internal
 */
#[CoversClass(ClassMethodLink::class)]
#[DisableReturnValueGenerationForTestDoubles]
final class ClassMethodLinkTest extends TestCase {
    public function testToString(): void {
        self::assertSame('Class::method()', (string) new ClassMethodLink('Class', 'method'));
        self::assertSame('App\\Class::method()', (string) new ClassMethodLink('App\\Class', 'method'));
        self::assertSame('\\App\\Class::method()', (string) new ClassMethodLink('\\App\\Class', 'method'));
    }

    public function testGetTitle(): void {
        self::assertSame('Class::method()', (new ClassMethodLink('Class', 'method'))->getTitle());
        self::assertSame('Class::method()', (new ClassMethodLink('App\\Class', 'method'))->getTitle());
        self::assertSame('Class::method()', (new ClassMethodLink('\\App\\Class', 'method'))->getTitle());
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
                    protected function method(): void {
                        // empty
                    }
                }
                PHP,
            );

        $link = new class ('A', 'method') extends ClassMethodLink {
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

        self::assertInstanceOf(ClassMethod::class, $actual);
        self::assertSame('method', $actual->name->name);
    }
}
