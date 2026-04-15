<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Cursor\Axes;

use LastDragon_ru\TextParser\Cursor\Cursor;
use LastDragon_ru\TextParser\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function count;
use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(ArrayAxis::class)]
final class ArrayAxisTest extends TestCase {
    public function testGetIterator(): void {
        $a      = new class() implements ArrayAxisTest_Node {
            // empty
        };
        $b      = new class() implements ArrayAxisTest_Node {
            // empty
        };
        $parent = new ArrayAxisTest_Parent([$a, $b]);
        $cursor = new ArrayAxisTest_Cursor($parent);

        self::assertEquals(
            [
                new ArrayAxisTest_Cursor($a, $cursor, 0),
                new ArrayAxisTest_Cursor($b, $cursor, 1),
            ],
            iterator_to_array($cursor->children),
        );
    }

    public function testCount(): void {
        $a      = new class() implements ArrayAxisTest_Node {
            // empty
        };
        $b      = new class() implements ArrayAxisTest_Node {
            // empty
        };
        $parent = new ArrayAxisTest_Parent([$a, $b]);
        $cursor = new ArrayAxisTest_Cursor($parent);

        self::assertEquals(2, count($cursor->children));
    }

    public function testGet(): void {
        $a      = new class() implements ArrayAxisTest_Node {
            // empty
        };
        $b      = new class() implements ArrayAxisTest_Node {
            // empty
        };
        $parent = new ArrayAxisTest_Parent([$a, $b]);
        $cursor = new ArrayAxisTest_Cursor($parent);

        self::assertSame($a, $cursor->children->get(0)->node ?? null);
        self::assertSame($b, $cursor->children->get(1)->node ?? null);
        self::assertNull($cursor->children->get(2));
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 */
interface ArrayAxisTest_Node {
    // empty
}

/**
 * @internal
 */
class ArrayAxisTest_Parent implements ArrayAxisTest_Node {
    public function __construct(
        /**
         * @var list<ArrayAxisTest_Node>
         */
        public array $children,
    ) {
        // empty
    }
}

/**
 * @internal
 * @template TNode of ArrayAxisTest_Node
 * @extends Cursor<TNode, ArrayAxisTest_Node>
 */
class ArrayAxisTest_Cursor extends Cursor {
    /**
     * @var ArrayAxis<covariant self<covariant ArrayAxisTest_Node>>
     */
    public ArrayAxis $children {
        get => new ArrayAxis(
            fn ($node, $offset) => new static($node, $this, $offset),
            $this->node instanceof ArrayAxisTest_Parent ? $this->node->children : [],
        );
    }
}
