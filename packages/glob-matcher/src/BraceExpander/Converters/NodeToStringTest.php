<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Converters;

use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Node;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Nodes\BraceExpansionNode;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Nodes\CharacterSequenceNode;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Nodes\IntegerSequenceNode;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Nodes\SequenceNode;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Nodes\StringNode;
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
#[CoversClass(NodeToString::class)]
final class NodeToStringTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderInvoke')]
    public function testInvoke(string $expected, Node $node): void {
        $converter = new NodeToString();
        $actual    = $converter($node);

        $this->assertSame($expected, $actual);
    }

    public function testAllNodesSupported(): void {
        $expected = ClassMap::get(Node::class);
        $provider = self::dataProviderInvoke();
        $actual   = array_values(array_unique(array_map(static fn ($case): string => $case[1]::class, $provider)));

        self::assertEqualsCanonicalizing($expected, $actual);
    }
    //</editor-fold>

    // <editor-fold desc="DataProvider">
    // =========================================================================
    /**
     * @return array<string, array{string, Node}>
     */
    public static function dataProviderInvoke(): array {
        return [
            'StringNode'                                                              => [
                'string',
                new StringNode('string'),
            ],
            'StringNode - escape'                                                     => [
                'a\b\{c,d\}',
                new StringNode('a\b\{c,d\}'),
            ],
            'SequenceNode'                                                            => [
                '{a,b}',
                new SequenceNode([
                    new BraceExpansionNode([new StringNode('a')]),
                    new BraceExpansionNode([new StringNode('b')]),
                ]),
            ],
            'IntegerSequenceNode -> without increment'                                => [
                '{1..9}',
                new IntegerSequenceNode('1', '9'),
            ],
            'IntegerSequenceNode -> with one increment'                               => [
                '{1..9}',
                new IntegerSequenceNode('1', '9', 1),
            ],
            'IntegerSequenceNode -> with zero increment'                              => [
                '{1..9}',
                new IntegerSequenceNode('1', '9', 0),
            ],
            'IntegerSequenceNode -> with positive increment'                          => [
                '{1..9..3}',
                new IntegerSequenceNode('1', '9', 3),
            ],
            'IntegerSequenceNode -> with negative increment'                          => [
                '{1..9..-3}',
                new IntegerSequenceNode('1', '9', -3),
            ],
            'IntegerSequenceNode <- without increment'                                => [
                '{9..1}',
                new IntegerSequenceNode('9', '1'),
            ],
            'IntegerSequenceNode <- with one increment'                               => [
                '{9..1}',
                new IntegerSequenceNode('9', '1', 1),
            ],
            'IntegerSequenceNode <- with zero increment'                              => [
                '{9..1}',
                new IntegerSequenceNode('9', '1', 0),
            ],
            'IntegerSequenceNode <- with positive increment'                          => [
                '{9..1..3}',
                new IntegerSequenceNode('9', '1', 3),
            ],
            'IntegerSequenceNode <- with negative increment'                          => [
                '{9..1..-3}',
                new IntegerSequenceNode('9', '1', -3),
            ],
            'IntegerSequenceNode -> max padding should be used'                       => [
                '{01..009..2}',
                new IntegerSequenceNode('01', '009', 2),
            ],
            'IntegerSequenceNode -> padding should be equal to max length'            => [
                '{01..200..20}',
                new IntegerSequenceNode('01', '200', 20),
            ],
            'IntegerSequenceNode -> (negative) max padding should be used'            => [
                '{-01..-009..2}',
                new IntegerSequenceNode('-01', '-009', 2),
            ],
            'IntegerSequenceNode -> (negative) padding should be equal to max length' => [
                '{-01..-200..20}',
                new IntegerSequenceNode('-01', '-200', 20),
            ],
            'CharacterSequenceNode -> without increment'                              => [
                '{a..o}',
                new CharacterSequenceNode('a', 'o'),
            ],
            'CharacterSequenceNode -> with one increment'                             => [
                '{a..o}',
                new CharacterSequenceNode('a', 'o', 1),
            ],
            'CharacterSequenceNode -> with zero increment'                            => [
                '{a..o}',
                new CharacterSequenceNode('a', 'o', 0),
            ],
            'CharacterSequenceNode -> with positive increment'                        => [
                '{a..o..3}',
                new CharacterSequenceNode('a', 'o', 3),
            ],
            'CharacterSequenceNode -> with negative increment'                        => [
                '{a..o..-3}',
                new CharacterSequenceNode('a', 'o', -3),
            ],
            'CharacterSequenceNode <- without increment'                              => [
                '{o..a}',
                new CharacterSequenceNode('o', 'a'),
            ],
            'CharacterSequenceNode <- with one increment'                             => [
                '{o..a}',
                new CharacterSequenceNode('o', 'a', 1),
            ],
            'CharacterSequenceNode <- with zero increment'                            => [
                '{o..a}',
                new CharacterSequenceNode('o', 'a', 0),
            ],
            'CharacterSequenceNode <- with positive increment'                        => [
                '{o..a..3}',
                new CharacterSequenceNode('o', 'a', 3),
            ],
            'CharacterSequenceNode <- with negative increment'                        => [
                '{o..a..-3}',
                new CharacterSequenceNode('o', 'a', -3),
            ],
            'BraceExpansionNode - empty'                                              => [
                '',
                new BraceExpansionNode([]),
            ],
            'BraceExpansionNode - simple'                                             => [
                '{1..2}{1..2}',
                new BraceExpansionNode([
                    new IntegerSequenceNode('1', '2'),
                    new IntegerSequenceNode('1', '2'),
                ]),
            ],
            'BraceExpansionNode - complex'                                            => [
                'path{/to,/from,}/file-{{00..10}.{txt,js},{a..e}.php}.tmp',
                new BraceExpansionNode([
                    new StringNode('path'),
                    new SequenceNode([
                        new BraceExpansionNode([new StringNode('/to')]),
                        new BraceExpansionNode([new StringNode('/from')]),
                        new BraceExpansionNode([]),
                    ]),
                    new StringNode('/file-'),
                    new SequenceNode([
                        new BraceExpansionNode([
                            new IntegerSequenceNode('00', '10'),
                            new StringNode('.'),
                            new SequenceNode([
                                new BraceExpansionNode([new StringNode('txt')]),
                                new BraceExpansionNode([new StringNode('js')]),
                            ]),
                        ]),
                        new BraceExpansionNode([
                            new CharacterSequenceNode('a', 'e'),
                            new StringNode('.php'),
                        ]),
                    ]),
                    new StringNode('.tmp'),
                ]),
            ],
        ];
    }
    // </editor-fold>
}
