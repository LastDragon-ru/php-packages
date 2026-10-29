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
use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(NodeToIterable::class)]
final class NodeToIterableTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param list<string> $expected
     */
    #[DataProvider('dataProviderInvoke')]
    public function testInvoke(array $expected, Node $node): void {
        $converter = new NodeToIterable();
        $actual    = iterator_to_array($converter($node));

        $this->assertEquals($expected, $actual);
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
     * @return array<string, array{list<string>, Node}>
     */
    public static function dataProviderInvoke(): array {
        return [
            'StringNode'                                                              => [
                ['string'],
                new StringNode('string'),
            ],
            'StringNode - escape'                                                     => [
                ['a\b\{c,d\}'],
                new StringNode('a\b\{c,d\}'),
            ],
            'SequenceNode'                                                            => [
                ['a', 'b'],
                new SequenceNode([
                    new BraceExpansionNode([new StringNode('a')]),
                    new BraceExpansionNode([new StringNode('b')]),
                ]),
            ],
            'IntegerSequenceNode -> without increment'                                => [
                ['1', '2', '3', '4', '5', '6', '7', '8', '9'],
                new IntegerSequenceNode('1', '9'),
            ],
            'IntegerSequenceNode -> with zero increment'                              => [
                ['1', '2', '3', '4', '5', '6', '7', '8', '9'],
                new IntegerSequenceNode('1', '9', 0),
            ],
            'IntegerSequenceNode -> with positive increment'                          => [
                ['1', '4', '7'],
                new IntegerSequenceNode('1', '9', 3),
            ],
            'IntegerSequenceNode -> with negative increment'                          => [
                ['1', '4', '7'],
                new IntegerSequenceNode('1', '9', -3),
            ],
            'IntegerSequenceNode <- without increment'                                => [
                ['9', '8', '7', '6', '5', '4', '3', '2', '1'],
                new IntegerSequenceNode('9', '1'),
            ],
            'IntegerSequenceNode <- with zero increment'                              => [
                ['9', '8', '7', '6', '5', '4', '3', '2', '1'],
                new IntegerSequenceNode('9', '1', 0),
            ],
            'IntegerSequenceNode <- with positive increment'                          => [
                ['9', '6', '3'],
                new IntegerSequenceNode('9', '1', 3),
            ],
            'IntegerSequenceNode <- with negative increment'                          => [
                ['9', '6', '3'],
                new IntegerSequenceNode('9', '1', -3),
            ],
            'IntegerSequenceNode -> max padding should be used'                       => [
                ['001', '003', '005', '007', '009'],
                new IntegerSequenceNode('01', '009', 2),
            ],
            'IntegerSequenceNode -> padding should be equal to max length'            => [
                ['001', '021', '041', '061', '081', '101', '121', '141', '161', '181'],
                new IntegerSequenceNode('01', '200', 20),
            ],
            'IntegerSequenceNode -> (negative) max padding should be used'            => [
                ['-001', '-003', '-005', '-007', '-009'],
                new IntegerSequenceNode('-01', '-009', 2),
            ],
            'IntegerSequenceNode -> (negative) padding should be equal to max length' => [
                ['-001', '-021', '-041', '-061', '-081', '-101', '-121', '-141', '-161', '-181'],
                new IntegerSequenceNode('-01', '-200', 20),
            ],
            'CharacterSequenceNode -> without increment'                              => [
                ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o'],
                new CharacterSequenceNode('a', 'o'),
            ],
            'CharacterSequenceNode -> with zero increment'                            => [
                ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o'],
                new CharacterSequenceNode('a', 'o', 0),
            ],
            'CharacterSequenceNode -> with positive increment'                        => [
                ['a', 'd', 'g', 'j', 'm'],
                new CharacterSequenceNode('a', 'o', 3),
            ],
            'CharacterSequenceNode -> with negative increment'                        => [
                ['a', 'd', 'g', 'j', 'm'],
                new CharacterSequenceNode('a', 'o', -3),
            ],
            'CharacterSequenceNode <- without increment'                              => [
                ['o', 'n', 'm', 'l', 'k', 'j', 'i', 'h', 'g', 'f', 'e', 'd', 'c', 'b', 'a'],
                new CharacterSequenceNode('o', 'a'),
            ],
            'CharacterSequenceNode <- with zero increment'                            => [
                ['o', 'n', 'm', 'l', 'k', 'j', 'i', 'h', 'g', 'f', 'e', 'd', 'c', 'b', 'a'],
                new CharacterSequenceNode('o', 'a', 0),
            ],
            'CharacterSequenceNode <- with positive increment'                        => [
                ['o', 'l', 'i', 'f', 'c'],
                new CharacterSequenceNode('o', 'a', 3),
            ],
            'CharacterSequenceNode <- with negative increment'                        => [
                ['o', 'l', 'i', 'f', 'c'],
                new CharacterSequenceNode('o', 'a', -3),
            ],
            'BraceExpansionNode - empty'                                              => [
                [''],
                new BraceExpansionNode([]),
            ],
            'BraceExpansionNode - simple'                                             => [
                ['11', '12', '21', '22'],
                new BraceExpansionNode([
                    new IntegerSequenceNode('1', '2'),
                    new IntegerSequenceNode('1', '2'),
                ]),
            ],
            'BraceExpansionNode - complex'                                            => [
                [
                    'path/to/file-00.txt.tmp',
                    'path/to/file-00.js.tmp',
                    'path/to/file-01.txt.tmp',
                    'path/to/file-01.js.tmp',
                    'path/to/file-02.txt.tmp',
                    'path/to/file-02.js.tmp',
                    'path/to/file-03.txt.tmp',
                    'path/to/file-03.js.tmp',
                    'path/to/file-04.txt.tmp',
                    'path/to/file-04.js.tmp',
                    'path/to/file-05.txt.tmp',
                    'path/to/file-05.js.tmp',
                    'path/to/file-06.txt.tmp',
                    'path/to/file-06.js.tmp',
                    'path/to/file-07.txt.tmp',
                    'path/to/file-07.js.tmp',
                    'path/to/file-08.txt.tmp',
                    'path/to/file-08.js.tmp',
                    'path/to/file-09.txt.tmp',
                    'path/to/file-09.js.tmp',
                    'path/to/file-10.txt.tmp',
                    'path/to/file-10.js.tmp',
                    'path/to/file-a.php.tmp',
                    'path/to/file-b.php.tmp',
                    'path/to/file-c.php.tmp',
                    'path/to/file-d.php.tmp',
                    'path/to/file-e.php.tmp',
                    'path/from/file-00.txt.tmp',
                    'path/from/file-00.js.tmp',
                    'path/from/file-01.txt.tmp',
                    'path/from/file-01.js.tmp',
                    'path/from/file-02.txt.tmp',
                    'path/from/file-02.js.tmp',
                    'path/from/file-03.txt.tmp',
                    'path/from/file-03.js.tmp',
                    'path/from/file-04.txt.tmp',
                    'path/from/file-04.js.tmp',
                    'path/from/file-05.txt.tmp',
                    'path/from/file-05.js.tmp',
                    'path/from/file-06.txt.tmp',
                    'path/from/file-06.js.tmp',
                    'path/from/file-07.txt.tmp',
                    'path/from/file-07.js.tmp',
                    'path/from/file-08.txt.tmp',
                    'path/from/file-08.js.tmp',
                    'path/from/file-09.txt.tmp',
                    'path/from/file-09.js.tmp',
                    'path/from/file-10.txt.tmp',
                    'path/from/file-10.js.tmp',
                    'path/from/file-a.php.tmp',
                    'path/from/file-b.php.tmp',
                    'path/from/file-c.php.tmp',
                    'path/from/file-d.php.tmp',
                    'path/from/file-e.php.tmp',
                    'path/file-00.txt.tmp',
                    'path/file-00.js.tmp',
                    'path/file-01.txt.tmp',
                    'path/file-01.js.tmp',
                    'path/file-02.txt.tmp',
                    'path/file-02.js.tmp',
                    'path/file-03.txt.tmp',
                    'path/file-03.js.tmp',
                    'path/file-04.txt.tmp',
                    'path/file-04.js.tmp',
                    'path/file-05.txt.tmp',
                    'path/file-05.js.tmp',
                    'path/file-06.txt.tmp',
                    'path/file-06.js.tmp',
                    'path/file-07.txt.tmp',
                    'path/file-07.js.tmp',
                    'path/file-08.txt.tmp',
                    'path/file-08.js.tmp',
                    'path/file-09.txt.tmp',
                    'path/file-09.js.tmp',
                    'path/file-10.txt.tmp',
                    'path/file-10.js.tmp',
                    'path/file-a.php.tmp',
                    'path/file-b.php.tmp',
                    'path/file-c.php.tmp',
                    'path/file-d.php.tmp',
                    'path/file-e.php.tmp',
                ],
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
