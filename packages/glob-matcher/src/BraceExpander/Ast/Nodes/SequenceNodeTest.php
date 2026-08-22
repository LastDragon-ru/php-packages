<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Ast\Nodes;

use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Cursor;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Node;
use LastDragon_ru\GlobMatcher\Package\TestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

use function iterator_to_array;

use const PHP_INT_MAX;

/**
 * @internal
 */
#[CoversClass(SequenceNode::class)]
final class SequenceNodeTest extends TestCase {
    public function testToIterable(): void {
        $node = new SequenceNode([
            new class () implements Node, SequenceNodeChild {
                #[Override]
                public static function toCount(Cursor $cursor): int {
                    return 0;
                }

                #[Override]
                public static function toIterable(Cursor $cursor): iterable {
                    return ['aa', 'ab'];
                }
            },
            new class () implements Node, SequenceNodeChild {
                #[Override]
                public static function toCount(Cursor $cursor): int {
                    return 0;
                }

                #[Override]
                public static function toIterable(Cursor $cursor): iterable {
                    return ['ba', 'bb', 'bc'];
                }
            },
        ]);

        self::assertSame(
            ['aa', 'ab', 'ba', 'bb', 'bc'],
            iterator_to_array($node::toIterable(new Cursor($node)), false),
        );
    }

    public function testToCount(): void {
        $node = new SequenceNode([
            new class () implements Node, SequenceNodeChild {
                #[Override]
                public static function toCount(Cursor $cursor): int {
                    return 2;
                }

                #[Override]
                public static function toIterable(Cursor $cursor): iterable {
                    return [];
                }
            },
            new class () implements Node, SequenceNodeChild {
                #[Override]
                public static function toCount(Cursor $cursor): int {
                    return 3;
                }

                #[Override]
                public static function toIterable(Cursor $cursor): iterable {
                    return [];
                }
            },
        ]);

        self::assertSame(5, $node::toCount(new Cursor($node)));
    }

    public function testToCountOverflow(): void {
        $node = new SequenceNode([
            new class () implements Node, SequenceNodeChild {
                #[Override]
                public static function toCount(Cursor $cursor): int {
                    return PHP_INT_MAX - 1;
                }

                #[Override]
                public static function toIterable(Cursor $cursor): iterable {
                    return [];
                }
            },
            new class () implements Node, SequenceNodeChild {
                #[Override]
                public static function toCount(Cursor $cursor): int {
                    return 10;
                }

                #[Override]
                public static function toIterable(Cursor $cursor): iterable {
                    return [];
                }
            },
        ]);

        self::assertSame(PHP_INT_MAX, $node::toCount(new Cursor($node)));
    }
}
