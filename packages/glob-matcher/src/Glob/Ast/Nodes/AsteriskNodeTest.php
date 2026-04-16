<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast\Nodes;

use LastDragon_ru\GlobMatcher\Glob\Ast\Cursor;
use LastDragon_ru\GlobMatcher\Glob\Options;
use LastDragon_ru\GlobMatcher\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(AsteriskNode::class)]
final class AsteriskNodeTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param Cursor<covariant AsteriskNode> $cursor
     */
    #[DataProvider('dataProviderToRegex')]
    public function testToRegex(string $expected, Cursor $cursor, Options $options): void {
        self::assertSame($expected, AsteriskNode::toRegex($options, $cursor));
    }

    public function testMerge(): void {
        self::assertEquals(new AsteriskNode(3), AsteriskNode::merge(new AsteriskNode(1), new AsteriskNode(2)));
    }
    // </editor-fold>

    // <editor-fold desc="DataProvider">
    // =========================================================================
    /**
     * @return array<string, array{string, Cursor<covariant AsteriskNode>, Options}>
     */
    public static function dataProviderToRegex(): array {
        return [
            'default'   => [
                '[^/]*?',
                new Cursor(
                    new AsteriskNode(),
                    new Cursor(
                        new NameNode([
                            new StringNode('string'),
                            new StringNode('string'),
                        ]),
                        null,
                        0,
                    ),
                    0,
                ),
                new Options(),
            ],
            'last node' => [
                '[^/]*?/?',
                new Cursor(new AsteriskNode()),
                new Options(),
            ],
        ];
    }
    // </editor-fold>
}
