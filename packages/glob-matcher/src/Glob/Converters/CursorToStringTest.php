<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Converters;

use LastDragon_ru\GlobMatcher\Glob\Ast\Cursor;
use LastDragon_ru\GlobMatcher\Glob\Ast\Node;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\AsteriskNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\CharacterClass;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\CharacterClassNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\CharacterCollatingSymbolNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\CharacterEquivalenceClassNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\CharacterNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\GlobNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\GlobstarNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\NameNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\PatternListNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\PatternListQuantifier;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\PatternNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\QuestionNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\SegmentNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\StringNode;
use LastDragon_ru\GlobMatcher\Package\ClassMap;
use LastDragon_ru\GlobMatcher\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function array_map;
use function array_unique;
use function array_values;

/**
 * @internal
 */
#[CoversClass(CursorToString::class)]
final class CursorToStringTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param Cursor<Node> $cursor
     */
    #[DataProvider('dataProviderInvoke')]
    public function testInvoke(string $expected, Cursor $cursor): void {
        $converter = new CursorToString();
        $actual    = $converter($cursor);

        $this->assertSame($expected, $actual);
    }

    public function testAllNodesSupported(): void {
        $expected = ClassMap::get(Node::class);
        $provider = self::dataProviderInvoke();
        $actual   = array_values(
            array_unique(
                array_map(
                    static fn ($case) => $case[1]->node::class,
                    $provider,
                ),
            ),
        );

        self::assertEqualsCanonicalizing($expected, $actual);
    }
    //</editor-fold>

    // <editor-fold desc="DataProvider">
    // =========================================================================
    /**
     * @return array<string, array{string, Cursor<Node>}>
     */
    public static function dataProviderInvoke(): array {
        return [
            'string'                                             => [
                'string',
                new Cursor(
                    new GlobNode([
                        new NameNode([
                            new StringNode('string'),
                        ]),
                    ]),
                ),
            ],
            'AsteriskNode'                                       => [
                '*',
                new Cursor(new AsteriskNode()),
            ],
            'AsteriskNode: count doesnt matter'                  => [
                '*',
                new Cursor(new AsteriskNode(5)),
            ],
            'CharacterClassNode'                                 => [
                '[:alpha:]',
                new Cursor(new CharacterClassNode(CharacterClass::Alpha)),
            ],
            'CharacterCollatingSymbolNode'                       => [
                '[.ch.]',
                new Cursor(new CharacterCollatingSymbolNode('ch')),
            ],
            'CharacterEquivalenceClassNode'                      => [
                '[=a=]',
                new Cursor(new CharacterEquivalenceClassNode('a')),
            ],
            'CharacterNode'                                      => [
                '[[:ascii:][.ch.]abc]',
                new Cursor(
                    new CharacterNode(
                        false,
                        [
                            new CharacterClassNode(CharacterClass::Ascii),
                            new CharacterCollatingSymbolNode('ch'),
                            new StringNode('abc'),
                        ],
                    ),
                ),
            ],
            'CharacterNode: negated'                             => [
                '[![:ascii:][.ch.]abc]',
                new Cursor(
                    new CharacterNode(
                        true,
                        [
                            new CharacterClassNode(CharacterClass::Ascii),
                            new CharacterCollatingSymbolNode('ch'),
                            new StringNode('abc'),
                        ],
                    ),
                ),
            ],
            'GlobNode'                                           => [
                '*.jpg',
                new Cursor(
                    new GlobNode([
                        new NameNode([
                            new AsteriskNode(),
                            new StringNode('.jpg'),
                        ]),
                    ]),
                ),
            ],
            'GlobstarNode'                                       => [
                '**',
                new Cursor(
                    new GlobstarNode(),
                ),
            ],
            'GlobstarNode: count doesnt matter'                  => [
                '**',
                new Cursor(
                    new GlobstarNode(4),
                ),
            ],
            'GlobstarNode: / should be added when next is not /' => [
                '**/*.jpg',
                new Cursor(
                    new GlobNode([
                        new GlobstarNode(),
                        new NameNode([
                            new AsteriskNode(),
                            new StringNode('.jpg'),
                        ]),
                    ]),
                ),
            ],
            'GlobstarNode: / should not be added when next is /' => [
                '**/',
                new Cursor(
                    new GlobNode([
                        new GlobstarNode(),
                        new SegmentNode(),
                    ]),
                ),
            ],
            'GlobstarNode: / should not be added if last'        => [
                '/**',
                new Cursor(
                    new GlobNode([
                        new SegmentNode(),
                        new GlobstarNode(),
                    ]),
                ),
            ],
            'NameNode'                                           => [
                'string',
                new Cursor(
                    new NameNode([
                        new StringNode('string'),
                    ]),
                ),
            ],
            'PatternListNode: zero or one'                       => [
                '?(a|b)',
                new Cursor(
                    new PatternListNode(
                        PatternListQuantifier::ZeroOrOne,
                        [
                            new PatternNode([
                                new StringNode('a'),
                            ]),
                            new PatternNode([
                                new StringNode('b'),
                            ]),
                        ],
                    ),
                ),
            ],
            'PatternListNode: zero or more'                      => [
                '*(a|b)',
                new Cursor(
                    new PatternListNode(
                        PatternListQuantifier::ZeroOrMore,
                        [
                            new PatternNode([
                                new StringNode('a'),
                            ]),
                            new PatternNode([
                                new StringNode('b'),
                            ]),
                        ],
                    ),
                ),
            ],
            'PatternListNode: one or more'                       => [
                '+(a|b)',
                new Cursor(
                    new PatternListNode(
                        PatternListQuantifier::OneOrMore,
                        [
                            new PatternNode([
                                new StringNode('a'),
                            ]),
                            new PatternNode([
                                new StringNode('b'),
                            ]),
                        ],
                    ),
                ),
            ],
            'PatternListNode: one'                               => [
                '@(a|b)',
                new Cursor(
                    new PatternListNode(
                        PatternListQuantifier::OneOf,
                        [
                            new PatternNode([
                                new StringNode('a'),
                            ]),
                            new PatternNode([
                                new StringNode('b'),
                            ]),
                        ],
                    ),
                ),
            ],
            'PatternListNode: not'                               => [
                '!(a|b)',
                new Cursor(
                    new PatternListNode(
                        PatternListQuantifier::Not,
                        [
                            new PatternNode([
                                new StringNode('a'),
                            ]),
                            new PatternNode([
                                new StringNode('b'),
                            ]),
                        ],
                    ),
                ),
            ],
            'PatternNode'                                        => [
                'a*',
                new Cursor(
                    new PatternNode([
                        new StringNode('a'),
                        new AsteriskNode(),
                    ]),
                ),
            ],
            'QuestionNode'                                       => [
                '?',
                new Cursor(new QuestionNode()),
            ],
            'SegmentNode'                                        => [
                '/',
                new Cursor(new SegmentNode()),
            ],
            'StringNode'                                         => [
                'string',
                new Cursor(new StringNode('string')),
            ],
        ];
    }
    // </editor-fold>
}
