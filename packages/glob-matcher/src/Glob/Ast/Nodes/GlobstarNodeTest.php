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
#[CoversClass(GlobstarNode::class)]
final class GlobstarNodeTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderToRegex')]
    public function testToRegex(string $expected, GlobstarNode $node, Options $options): void {
        self::assertSame($expected, $node::toRegex($options, new Cursor($node)));
    }

    public function testMerge(): void {
        self::assertEquals(new GlobstarNode(3), GlobstarNode::merge(new GlobstarNode(1), new GlobstarNode(2)));
    }
    // </editor-fold>

    // <editor-fold desc="DataProvider">
    // =========================================================================
    /**
     * @return array<string, array{string, GlobstarNode, Options}>
     */
    public static function dataProviderToRegex(): array {
        return [
            'default'     => [
                '(?:(?<=^|/)(?:(?!\.)(?:(?=.))[^/]*?)(?:(?:/|$)|(?=/|$)))*?',
                new GlobstarNode(),
                new Options(),
            ],
            'dot = true'  => [
                '(?:(?<=^|/)(?:(?!\.{1,2}(?:/|$))(?:(?=.))[^/]*?)(?:(?:/|$)|(?=/|$)))*?',
                new GlobstarNode(),
                new Options(hidden: true),
            ],
            'dot = false' => [
                '(?:(?<=^|/)(?:(?!\.)(?:(?=.))[^/]*?)(?:(?:/|$)|(?=/|$)))*?',
                new GlobstarNode(),
                new Options(hidden: false),
            ],
        ];
    }
    // </editor-fold>
}
