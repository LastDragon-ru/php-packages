<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Ast\Nodes;

use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Cursor;
use LastDragon_ru\GlobMatcher\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(StringNode::class)]
final class StringNodeTest extends TestCase {
    public function testToCount(): void {
        $node   = new StringNode('string');
        $cursor = new Cursor($node);

        self::assertSame(1, $node::toCount($cursor));
    }
}
