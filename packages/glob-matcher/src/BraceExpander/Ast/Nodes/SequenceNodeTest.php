<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Ast\Nodes;

use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Cursor;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Node;
use LastDragon_ru\GlobMatcher\Package\TestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

use const PHP_INT_MAX;

/**
 * @internal
 */
#[CoversClass(SequenceNode::class)]
final class SequenceNodeTest extends TestCase {
    public function testToCount(): void {
        $node = new SequenceNode([
            new class () implements Node, SequenceNodeChild {
                #[Override]
                public static function toCount(Cursor $cursor): int {
                    return 2;
                }
            },
            new class () implements Node, SequenceNodeChild {
                #[Override]
                public static function toCount(Cursor $cursor): int {
                    return 3;
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
            },
            new class () implements Node, SequenceNodeChild {
                #[Override]
                public static function toCount(Cursor $cursor): int {
                    return 10;
                }
            },
        ]);

        self::assertSame(PHP_INT_MAX, $node::toCount(new Cursor($node)));
    }
}
