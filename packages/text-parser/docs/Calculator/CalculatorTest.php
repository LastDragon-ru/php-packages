<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Docs\Calculator;

use LastDragon_ru\TextParser\Docs\Calculator\Ast\Nodes\ExpressionNode;
use LastDragon_ru\TextParser\Docs\Calculator\Ast\Nodes\NumberNode;
use LastDragon_ru\TextParser\Docs\Calculator\Ast\Nodes\OperatorAdditionNode;
use LastDragon_ru\TextParser\Docs\Calculator\Ast\Nodes\OperatorDivisionNode;
use LastDragon_ru\TextParser\Docs\Calculator\Ast\Nodes\OperatorMultiplicationNode;
use LastDragon_ru\TextParser\Docs\Calculator\Ast\Nodes\OperatorNode;
use LastDragon_ru\TextParser\Docs\Calculator\Ast\Nodes\OperatorSubtractionNode;
use LastDragon_ru\TextParser\Package\TestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function is_nan;
use function iterator_to_array;

use const NAN;

/**
 * @internal
 */
#[CoversClass(Calculator::class)]
final class CalculatorTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderCalculate')]
    public function testCalculate(float|int $expected, string $expression): void {
        $actual = (new Calculator())->calculate($expression);

        if (!is_nan($expected)) {
            self::assertSame($expected, $actual);
        } else {
            self::assertNan($actual);
        }
    }

    /**
     * @param list<OperatorNode|float|int> $expected
     */
    #[DataProvider('dataProviderRpn')]
    public function testRpn(array $expected, ExpressionNode $node): void {
        $calculator = new class() extends Calculator {
            #[Override]
            public function rpn(ExpressionNode $node): iterable {
                return parent::rpn($node);
            }
        };

        self::assertEquals($expected, iterator_to_array($calculator->rpn($node), false));
    }
    //</editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{float|int, string}>
     */
    public static function dataProviderCalculate(): array {
        return [
            '<empty>'               => [NAN, ''],
            '1 + 1'                 => [2, '1 + 1'],
            '10 - 2 * 3'            => [4, '10 - 2 * 3'],
            '- 2 - 2'               => [-4, '- 2 - 2'],
            '-'                     => [NAN, '-'],
            '2 2'                   => [NAN, '2 2'],
            '3 + 4 * 2 / ( 1 - 5 )' => [1, '3 + 4 * 2 / ( 1 - 5 )'],
        ];
    }

    /**
     * @return array<string, array{list<OperatorNode|float|int>, ExpressionNode}>
     */
    public static function dataProviderRpn(): array {
        return [
            '(1 + 1)'                 => [
                [1, 1, new OperatorAdditionNode()],
                new ExpressionNode([
                    new NumberNode(1),
                    new OperatorAdditionNode(),
                    new NumberNode(1),
                ]),
            ],
            '(1 - 2 * 3)'             => [
                [1, 2, 3, new OperatorMultiplicationNode(), new OperatorSubtractionNode()],
                new ExpressionNode([
                    new NumberNode(1),
                    new OperatorSubtractionNode(),
                    new NumberNode(2),
                    new OperatorMultiplicationNode(),
                    new NumberNode(3),
                ]),
            ],
            '(- 1)'                   => [
                [0, 1, new OperatorSubtractionNode()],
                new ExpressionNode([
                    new OperatorSubtractionNode(),
                    new NumberNode(1),
                ]),
            ],
            '(3 + 4 * 2 / ( 1 - 5 ))' => [
                [
                    3,
                    4,
                    2,
                    new OperatorMultiplicationNode(),
                    1,
                    5,
                    new OperatorSubtractionNode(),
                    new OperatorDivisionNode(),
                    new OperatorAdditionNode(),
                ],
                new ExpressionNode([
                    new NumberNode(3),
                    new OperatorAdditionNode(),
                    new NumberNode(4),
                    new OperatorMultiplicationNode(),
                    new NumberNode(2),
                    new OperatorDivisionNode(),
                    new ExpressionNode([
                        new NumberNode(1),
                        new OperatorSubtractionNode(),
                        new NumberNode(5),
                    ]),
                ]),
            ],
        ];
    }
    // </editor-fold>
}
