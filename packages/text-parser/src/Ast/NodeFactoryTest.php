<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Ast;

use LastDragon_ru\TextParser\Package\TestCase;
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(NodeFactory::class)]
final class NodeFactoryTest extends TestCase {
    public function testPush(): void {
        $factory = new NodeFactoryTest_Factory();
        $childA  = new NodeFactoryTest_Child();
        $childB  = new NodeFactoryTest_ChildIgnored();
        $childC  = new class() extends NodeFactoryTest_Child {
            // empty
        };

        self::assertFalse($factory->push(null));
        self::assertTrue($factory->push($childA));
        self::assertTrue($factory->push($childA));
        self::assertFalse($factory->push(null));
        self::assertFalse($factory->push($childB));
        self::assertFalse($factory->push(null));
        self::assertTrue($factory->push($childC));
        self::assertTrue($factory->push($childC));

        self::assertEquals(
            [
                $childA,
                $childC,
            ],
            $factory->create()?->children,
        );
    }

    public function testIsEmpty(): void {
        $factory = new NodeFactoryTest_Factory();

        self::assertTrue($factory->isEmpty());

        $factory->push(null);

        self::assertTrue($factory->isEmpty());

        $factory->push(new NodeFactoryTest_Child());

        self::assertFalse($factory->isEmpty());
    }

    public function testCreate(): void {
        $child   = new NodeFactoryTest_Child();
        $parent  = new NodeFactoryTest_Parent([$child]);
        $factory = Mockery::mock(NodeFactoryTest_Factory::class);
        $factory->shouldAllowMockingProtectedMethods();
        $factory->makePartial();
        $factory
            ->shouldReceive('onCreate')
            ->with([$child])
            ->once()
            ->andReturn($parent);

        $factory->push($child);

        self::assertEquals($parent, $factory->create());
        self::assertTrue($factory->isEmpty());
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 */
class NodeFactoryTest_Parent {
    public function __construct(
        /**
         * @var list<NodeFactoryTest_Child>
         */
        public array $children,
    ) {
        // empty
    }
}

/**
 * @internal
 */
class NodeFactoryTest_Child implements NodeMergeable {
    #[Override]
    public static function merge(NodeMergeable $previous, NodeMergeable $current): NodeMergeable {
        if ($previous::class === $current::class) {
            $current = $previous;
        }

        return $current;
    }
}

/**
 * @internal
 */
class NodeFactoryTest_ChildIgnored extends NodeFactoryTest_Child {
    // empty
}

/**
 * @internal
 * @extends NodeFactory<NodeFactoryTest_Parent, NodeFactoryTest_Child>
 */
class NodeFactoryTest_Factory extends NodeFactory {
    #[Override]
    protected function onCreate(array $children): ?object {
        return new NodeFactoryTest_Parent($children);
    }

    #[Override]
    protected function onPush(array $children, ?object $node): bool {
        return !($node instanceof NodeFactoryTest_ChildIgnored);
    }
}
