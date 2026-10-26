<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Converters;

use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Node;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Nodes\BraceExpansionNode;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Nodes\IntegerSequenceNode;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Nodes\SequenceNode;
use LastDragon_ru\GlobMatcher\Package\ClassMap;
use LastDragon_ru\GlobMatcher\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function array_map;
use function array_unique;
use function array_values;
use function array_walk;
use function count;

use const PHP_INT_MAX;

/**
 * @internal
 */
#[CoversClass(NodeToCount::class)]
final class NodeToCountTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderInvoke')]
    #[DataProvider('dataProviderInvokeOverflow')]
    public function testInvoke(int $expected, Node $node): void {
        $converter = new NodeToCount();
        $actual    = $converter($node);

        $this->assertSame($expected, $actual);
    }

    public function testAllNodesSupported(): void {
        $expected = ClassMap::get(Node::class);
        $provider = self::dataProviderInvoke();
        $actual   = array_values(array_unique(array_map(static fn ($case): string => $case[1]::class, $provider)));

        self::assertEqualsCanonicalizing($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProvider">
    // =========================================================================
    /**
     * @return array<string, array{int, Node}>
     */
    public static function dataProviderInvoke(): array {
        $data = NodeToIterableTest::dataProviderInvoke();

        array_walk($data, static function (array &$case): void {
            $case[0] = count($case[0]);
        });

        return $data;
    }

    /**
     * @return array<string, array{int, Node}>
     */
    public static function dataProviderInvokeOverflow(): array {
        return [
            'BraceExpansionNode - overflow' => [
                PHP_INT_MAX,
                new BraceExpansionNode([
                    new IntegerSequenceNode('1', (string) (int) (PHP_INT_MAX / 2)),
                    new IntegerSequenceNode('1', (string) (int) (PHP_INT_MAX / 2)),
                    new IntegerSequenceNode('1', (string) (int) (PHP_INT_MAX / 2)),
                    new IntegerSequenceNode('1', (string) (int) (PHP_INT_MAX / 2)),
                    new IntegerSequenceNode('1', (string) (int) (PHP_INT_MAX / 2)),
                ]),
            ],
            'SequenceNode - overflow'       => [
                PHP_INT_MAX,
                new SequenceNode([
                    new IntegerSequenceNode('2', (string) PHP_INT_MAX),
                    new IntegerSequenceNode('2', (string) PHP_INT_MAX),
                    new IntegerSequenceNode('2', (string) PHP_INT_MAX),
                    new IntegerSequenceNode('2', (string) PHP_INT_MAX),
                    new IntegerSequenceNode('2', (string) PHP_INT_MAX),
                ]),
            ],
        ];
    }
    // </editor-fold>
}
