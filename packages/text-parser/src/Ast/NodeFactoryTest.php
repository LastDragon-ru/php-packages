<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Ast;

use LastDragon_ru\TextParser\Package\TestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(NodeFactory::class)]
final class NodeFactoryTest extends TestCase {
    public function testPush(): void {
        $factory = new NodeFactoryTest_Factory();
        $childA  = new NodeFactoryTest_Child('a');
        $childB  = new NodeFactoryTest_ChildIgnored();
        $childC  = new readonly class('c') extends NodeFactoryTest_Child {
            // empty
        };
        $class   = $childC::class;

        self::assertFalse($factory->push(null));
        self::assertTrue($factory->push($childA));
        self::assertTrue($factory->push($childA));
        self::assertTrue($factory->push($childC));
        self::assertFalse($factory->push(null));
        self::assertFalse($factory->push($childB));
        self::assertFalse($factory->push(null));
        self::assertTrue($factory->push($childC));
        self::assertTrue($factory->push($childC));

        self::assertEquals(
            [
                new NodeFactoryTest_Child('aa'),
                new $class('ccc'),
            ],
            $factory->create()?->children,
        );
    }

    public function testPropertyEmpty(): void {
        $factory = new NodeFactoryTest_Factory();

        self::assertTrue($factory->empty);

        $factory->push(null);

        self::assertTrue($factory->empty);

        $factory->push(new NodeFactoryTest_Child('child'));

        self::assertFalse($factory->empty);
    }

    public function testCreate(): void {
        $child   = new NodeFactoryTest_Child('child');
        $parent  = new NodeFactoryTest_Parent([$child]);
        $factory = new NodeFactoryTest_Factory();

        $factory->push($child);

        self::assertFalse($factory->empty);
        self::assertEquals($parent, $factory->create());
        self::assertTrue($factory->empty);
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
         * @var list<NodeFactoryTest_Node>
         */
        public array $children,
    ) {
        // empty
    }
}

/**
 * @internal
 */
interface NodeFactoryTest_Node {
    // empty
}

/**
 * @internal
 */
readonly class NodeFactoryTest_Child extends NodeString implements NodeFactoryTest_Node {
    // empty
}

/**
 * @internal
 */
readonly class NodeFactoryTest_ChildIgnored implements NodeFactoryTest_Node {
    // empty
}

/**
 * @internal
 * @extends NodeFactory<NodeFactoryTest_Parent, NodeFactoryTest_Node>
 */
class NodeFactoryTest_Factory extends NodeFactory {
    #[Override]
    protected function make(): ?object {
        return new NodeFactoryTest_Parent($this->children);
    }

    #[Override]
    protected function valid(object $node): bool {
        return !($node instanceof NodeFactoryTest_ChildIgnored);
    }
}
