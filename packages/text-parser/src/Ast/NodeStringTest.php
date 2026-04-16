<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Ast;

use LastDragon_ru\TextParser\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(NodeString::class)]
final class NodeStringTest extends TestCase {
    public function testMerge(): void {
        self::assertEquals(new NodeString('12'), NodeString::merge(new NodeString('1'), new NodeString('2')));
    }
}
