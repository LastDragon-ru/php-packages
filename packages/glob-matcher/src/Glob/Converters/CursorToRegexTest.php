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
#[CoversClass(CursorToRegex::class)]
final class CursorToRegexTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param Cursor<*> $cursor
     */
    #[DataProvider('dataProviderInvoke')]
    public function testInvoke(string $expected, ?CursorToRegexOptions $options, Cursor $cursor): void {
        $converter = new CursorToRegex($options);
        $actual    = $converter($cursor);

        $this->assertSame($expected, $actual);
    }

    public function testAllNodesSupported(): void {
        $expected = ClassMap::get(Node::class);
        $provider = self::dataProviderInvoke();
        $actual   = array_values(
            array_unique(
                array_map(
                    static fn ($case) => $case[2]->node::class,
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
     * @return array<string, array{string, ?CursorToRegexOptions, Cursor<Node>}>
     */
    public static function dataProviderInvoke(): array {
        return [
            '*: matchCase = true'                       => [
                '#^(?!\.)(?:(?=.)file\.txt)$#us',
                new CursorToRegexOptions(matchCase: true),
                new Cursor(
                    new GlobNode([new NameNode([new StringNode('file.txt')])]),
                ),
            ],
            '*: matchCase = false'                      => [
                '#^(?!\.)(?:(?=.)file\.txt)$#usi',
                new CursorToRegexOptions(matchCase: false),
                new Cursor(
                    new GlobNode([new NameNode([new StringNode('file.txt')])]),
                ),
            ],
            'AsteriskNode (not last node)'              => [
                '#^[^/]*?$#us',
                null,
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
            ],
            'AsteriskNode (last node)'                  => [
                '#^[^/]*?/?$#us',
                null,
                new Cursor(new AsteriskNode()),
            ],
            'CharacterClassNode'                        => [
                '#^[:ascii:]$#us',
                null,
                new Cursor(new CharacterClassNode(CharacterClass::Ascii)),
            ],
            'CharacterCollatingSymbolNode'              => [
                '#^[.ch.]$#us',
                null,
                new Cursor(new CharacterCollatingSymbolNode('ch')),
            ],
            'CharacterEquivalenceClassNode'             => [
                '#^[=a=]$#us',
                null,
                new Cursor(new CharacterEquivalenceClassNode('a')),
            ],
            'CharacterNode: default'                    => [
                '#^[abc[:alpha:]]$#us',
                null,
                new Cursor(
                    new CharacterNode(
                        false,
                        [
                            new StringNode('abc'),
                            new CharacterClassNode(CharacterClass::Alpha),
                        ],
                    ),
                ),
            ],
            'CharacterNode: negated'                    => [
                '#^[^negated/]$#us',
                null,
                new Cursor(
                    new CharacterNode(
                        true,
                        [
                            new StringNode('negated'),
                        ],
                    ),
                ),
            ],
            'CharacterNode: negated (with /)'           => [
                '#^[^/negated]$#us',
                null,
                new Cursor(
                    new CharacterNode(
                        true,
                        [
                            new StringNode('/negated'),
                        ],
                    ),
                ),
            ],
            'CharacterNode: range'                      => [
                '#^[a-c[:alpha:]]$#us',
                null,
                new Cursor(
                    new CharacterNode(
                        false,
                        [
                            new StringNode('a-c'),
                            new CharacterClassNode(CharacterClass::Alpha),
                        ],
                    ),
                ),
            ],
            'CharacterNode: not range'                  => [
                '#^[a\\\\-c[:alpha:]]$#us',
                null,
                new Cursor(
                    new CharacterNode(
                        false,
                        [
                            new StringNode('a\\-c'),
                            new CharacterClassNode(CharacterClass::Alpha),
                        ],
                    ),
                ),
            ],
            'CharacterNode: escape'                     => [
                '#^[\\]\\\\\\]\\^\\\\\\^\\:\\[]$#us',
                null,
                new Cursor(
                    new CharacterNode(
                        false,
                        [
                            new StringNode(']\\]^\\^:['),
                        ],
                    ),
                ),
            ],
            'GlobNode: default'                         => [
                '#^(?!\.)(?:(?=.)file\.txt)$#us',
                null,
                new Cursor(
                    new GlobNode([new NameNode([new StringNode('file.txt')])]),
                ),
            ],
            'GlobNode: matchHidden = true'              => [
                '#^(?!\.{1,2}(?:/|$))(?:(?=.)file\.txt)$#us',
                new CursorToRegexOptions(matchHidden: true),
                new Cursor(
                    new GlobNode([new NameNode([new StringNode('file.txt')])]),
                ),
            ],
            'GlobNode: matchHidden = false'             => [
                '#^(?!\.)(?:(?=.)file\.txt)$#us',
                new CursorToRegexOptions(matchHidden: false),
                new Cursor(
                    new GlobNode([new NameNode([new StringNode('file.txt')])]),
                ),
            ],
            'GlobNode: starts with dot'                 => [
                '#^(?!\.{1,2}(?:/|$))(?:(?=.)\.txt)$#us',
                null,
                new Cursor(
                    new GlobNode([new NameNode([new StringNode('.txt')])]),
                ),
            ],
            'GlobNode: ../../..'                        => [
                '#^(?:\.\.)(?:/)(?:\.\.)(?:/)(?:\.\.)$#us',
                null,
                new Cursor(
                    new GlobNode([
                        new NameNode([new StringNode('..')]),
                        new SegmentNode(),
                        new NameNode([new StringNode('..')]),
                        new SegmentNode(),
                        new NameNode([new StringNode('..')]),
                    ]),
                ),
            ],
            'GlobNode: */.* and matchHidden = true'     => [
                '#^(?:(?!\.{1,2}(?:/|$))(?:(?=.)[^/]*?))(?:/)(?:(?!\.{1,2}(?:/|$))(?:(?=.)(?:\.)(?:[^/]*?/?)))$#us',
                new CursorToRegexOptions(matchHidden: true),
                new Cursor(
                    new GlobNode([
                        new NameNode([new AsteriskNode(1)]),
                        new SegmentNode(),
                        new NameNode([
                            new StringNode('.'),
                            new AsteriskNode(1),
                        ]),
                    ]),
                ),
            ],
            'GlobNode: */'                              => [
                '#^(?:(?!\.)(?:(?=.)[^/]*?))(?:/)$#us',
                null,
                new Cursor(
                    new GlobNode([
                        new NameNode([new AsteriskNode(1)]),
                        new SegmentNode(),
                    ]),
                ),
            ],
            'GlobstarNode: default'                     => [
                '#^(?:(?<=^|/)(?:(?!\.)(?:(?=.))[^/]*?)(?:(?:/|$)|(?=/|$)))*?$#us',
                null,
                new Cursor(new GlobstarNode()),
            ],
            'GlobstarNode: matchHidden = true'          => [
                '#^(?:(?<=^|/)(?:(?!\.{1,2}(?:/|$))(?:(?=.))[^/]*?)(?:(?:/|$)|(?=/|$)))*?$#us',
                new CursorToRegexOptions(matchHidden: true),
                new Cursor(new GlobstarNode()),
            ],
            'GlobstarNode: matchHidden = false'         => [
                '#^(?:(?<=^|/)(?:(?!\.)(?:(?=.))[^/]*?)(?:(?:/|$)|(?=/|$)))*?$#us',
                new CursorToRegexOptions(matchHidden: false),
                new Cursor(new GlobstarNode()),
            ],
            'NameNode: `.` must be matched explicitly'  => [
                '#^\\.$#us',
                null,
                new Cursor(
                    new NameNode([new StringNode('.')]),
                ),
            ],
            'NameNode: `..` must be matched explicitly' => [
                '#^\\.\\.$#us',
                null,
                new Cursor(
                    new NameNode([new StringNode('..')]),
                ),
            ],
            'NameNode: matchHidden = default'           => [
                '#^(?!\.)(?:(?=.)abc)$#us',
                null,
                new Cursor(
                    new NameNode([new StringNode('abc')]),
                ),
            ],
            'NameNode: matchHidden = false'             => [
                '#^(?!\.)(?:(?=.)abc)$#us',
                new CursorToRegexOptions(matchHidden: false),
                new Cursor(
                    new NameNode([new StringNode('abc')]),
                ),
            ],
            'NameNode: matchHidden = true'              => [
                '#^(?!\.{1,2}(?:/|$))(?:(?=.)abc)$#us',
                new CursorToRegexOptions(matchHidden: true),
                new Cursor(
                    new NameNode([new StringNode('abc')]),
                ),
            ],
            'NameNode: matchHidden = explicit'          => [
                '#^(?!\.{1,2}(?:/|$))(?:(?=.)\.abc)$#us',
                new CursorToRegexOptions(matchHidden: false),
                new Cursor(
                    new NameNode([new StringNode('.abc')]),
                ),
            ],
            'PatternListNode: ZeroOrOne'                => [
                '#^(?:(?:a)|(?:b)|(?:c))?$#us',
                null,
                new Cursor(
                    new PatternListNode(
                        PatternListQuantifier::ZeroOrOne,
                        [
                            new PatternNode([new StringNode('a')]),
                            new PatternNode([new StringNode('b')]),
                            new PatternNode([new StringNode('c')]),
                        ],
                    ),
                ),
            ],
            'PatternListNode: ZeroOrMore'               => [
                '#^(?:(?:a)|(?:b)|(?:c))*$#us',
                null,
                new Cursor(
                    new PatternListNode(
                        PatternListQuantifier::ZeroOrMore,
                        [
                            new PatternNode([new StringNode('a')]),
                            new PatternNode([new StringNode('b')]),
                            new PatternNode([new StringNode('c')]),
                        ],
                    ),
                ),
            ],
            'PatternListNode: OneOrMore'                => [
                '#^(?:(?:a)|(?:b)|(?:c))+$#us',
                null,
                new Cursor(
                    new PatternListNode(
                        PatternListQuantifier::OneOrMore,
                        [
                            new PatternNode([new StringNode('a')]),
                            new PatternNode([new StringNode('b')]),
                            new PatternNode([new StringNode('c')]),
                        ],
                    ),
                ),
            ],
            'PatternListNode: OneOf'                    => [
                '#^(?:(?:a)|(?:b)|(?:c))$#us',
                null,
                new Cursor(
                    new PatternListNode(
                        PatternListQuantifier::OneOf,
                        [
                            new PatternNode([new StringNode('a')]),
                            new PatternNode([new StringNode('b')]),
                            new PatternNode([new StringNode('c')]),
                        ],
                    ),
                ),
            ],
            'PatternListNode: Not'                      => [
                '#^(?:(?:a)(?:(?:(?:b)|(?:(?:c)(?:(?:(?!(?:(?:d)|(?:e))(?:(?:f)(?:h)))[^/]*?))(?:f))|(?:g)))(?:h))$#us',
                null,
                new Cursor(
                    new PatternListNode(
                        PatternListQuantifier::OneOf,
                        [
                            new PatternNode([
                                new StringNode('a'),
                                new PatternListNode(
                                    PatternListQuantifier::OneOf,
                                    [
                                        new PatternNode([new StringNode('b')]),
                                        new PatternNode([
                                            new StringNode('c'),
                                            new PatternListNode(
                                                PatternListQuantifier::Not,
                                                [
                                                    new PatternNode([new StringNode('d')]),
                                                    new PatternNode([new StringNode('e')]),
                                                ],
                                            ),
                                            new StringNode('f'),
                                        ]),
                                        new PatternNode([new StringNode('g')]),
                                    ],
                                ),
                                new StringNode('h'),
                            ]),
                        ],
                    ),
                ),
            ],
            'PatternListNode: empty'                    => [
                '#^$#us',
                null,
                new Cursor(
                    new PatternListNode(PatternListQuantifier::Not, []),
                ),
            ],
            // todo(glob-matcher): PatternListNode: Not not
            'PatternNode'                               => [
                '#^(?:a)(?:b)$#us',
                null,
                new Cursor(
                    new PatternNode([new StringNode('a'), new StringNode('b')]),
                ),
            ],
            'QuestionNode'                              => [
                '#^[^/]$#us',
                null,
                new Cursor(new QuestionNode()),
            ],
            'SegmentNode'                               => [
                '#^/$#us',
                null,
                new Cursor(new SegmentNode()),
            ],
            'SegmentNode: custom delimiter'             => [
                '/^\\/$/us',
                new CursorToRegexOptions(delimiter: '/'),
                new Cursor(new SegmentNode()),
            ],
            'StringNode'                                => [
                '#^/string \\# \\[ \\. \\] \\? \\*/$#us',
                null,
                new Cursor(new StringNode('/string # [ . ] ? */')),
            ],
            'StringNode: custom delimiter'              => [
                '/^\\/string \\# \\[ \\. \\] \\? \\*\\/$/us',
                new CursorToRegexOptions(delimiter: '/'),
                new Cursor(new StringNode('/string # [ . ] ? */')),
            ],
        ];
    }
    // </editor-fold>
}
