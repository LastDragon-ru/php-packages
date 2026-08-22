<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Ast\Nodes;

use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Cursor;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Node;
use LastDragon_ru\GlobMatcher\Package\TestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function count;
use function iterator_to_array;

use const PHP_INT_MAX;

/**
 * @internal
 */
#[CoversClass(BraceExpansionNode::class)]
final class BraceExpansionNodeTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param list<string> $expected
     */
    #[DataProvider('dataProviderToIterable')]
    public function testToCount(array $expected, BraceExpansionNode $node): void {
        self::assertSame(count($expected), $node::toCount(new Cursor($node)));
    }

    public function testToCountOverflow(): void {
        $node = new BraceExpansionNode([
            new class () implements Node, BraceExpansionNodeChild {
                #[Override]
                public static function toCount(Cursor $cursor): int {
                    return PHP_INT_MAX - 1;
                }

                #[Override]
                public static function toIterable(Cursor $cursor): iterable {
                    return [];
                }
            },
            new class () implements Node, BraceExpansionNodeChild {
                #[Override]
                public static function toCount(Cursor $cursor): int {
                    return 2;
                }

                #[Override]
                public static function toIterable(Cursor $cursor): iterable {
                    return [];
                }
            },
        ]);

        self::assertSame(PHP_INT_MAX, $node::toCount(new Cursor($node)));
    }

    /**
     * @param list<string> $expected
     */
    #[DataProvider('dataProviderToIterable')]
    public function testToIterable(array $expected, BraceExpansionNode $node): void {
        self::assertSame($expected, iterator_to_array($node::toIterable(new Cursor($node)), false));
    }
    // </editor-fold>

    // <editor-fold desc="DataProvider">
    // =========================================================================
    /**
     * @return array<string, array{list<string>, BraceExpansionNode}>
     */
    public static function dataProviderToIterable(): array {
        return [
            'empty'   => [
                [''],
                new BraceExpansionNode([]),
            ],
            'simple'  => [
                ['11', '12', '21', '22'],
                new BraceExpansionNode([
                    new IntegerSequenceNode('1', '2'),
                    new IntegerSequenceNode('1', '2'),
                ]),
            ],
            'complex' => [
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
