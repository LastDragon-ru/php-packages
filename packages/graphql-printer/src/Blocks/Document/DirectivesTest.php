<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQL\Printer\Blocks\Document;

use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\Parser;
use LastDragon_ru\GraphQL\Printer\Contracts\Settings;
use LastDragon_ru\GraphQL\Printer\Misc\Collector;
use LastDragon_ru\GraphQL\Printer\Misc\Context;
use LastDragon_ru\GraphQL\Printer\Package\TestCase;
use LastDragon_ru\PhpUnit\GraphQL\PrinterSettings;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(Directives::class)]
final class DirectivesTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param list<DirectiveNode> $directives
     */
    #[DataProvider('dataProviderSerialize')]
    public function testSerialize(
        string $expected,
        Settings $settings,
        int $level,
        int $used,
        array $directives,
    ): void {
        $collector = new Collector();
        $context   = new Context($settings, null, null);
        $actual    = (new Directives($context, $directives))->serialize($collector, $level, $used);

        Parser::directives($actual);

        self::assertSame($expected, $actual);
    }

    public function testStatistics(): void {
        $a         = Parser::directive('@a');
        $b         = Parser::directive('@b');
        $settings  = (new PrinterSettings())->setPrintDirectives(true);
        $context   = new Context($settings, null, null);
        $collector = new Collector();
        $block     = new Directives($context, [$a, $b]);
        $content   = $block->serialize($collector, 0, 0);

        self::assertNotEmpty($content);
        self::assertEquals([], $collector->getUsedTypes());
        self::assertEquals(['@a' => '@a', '@b' => '@b'], $collector->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, list<DirectiveNode>}>
     */
    public static function dataProviderSerialize(): array {
        $settings = (new PrinterSettings())
            ->setNormalizeDirectives(false)
            ->setAlwaysMultilineDirectives(true)
            ->setAlwaysMultilineArguments(false);

        return [
            'empty'                 => [
                '',
                $settings,
                0,
                0,
                [],
            ],
            'directives'            => [
                <<<'STRING'
                @b(b: 123)
                @a
                STRING,
                $settings,
                0,
                0,
                [
                    Parser::directive('@b(b: 123)'),
                    Parser::directive('@a'),
                ],
            ],
            'line length'           => [
                <<<'STRING'
                @a(
                    a: "very very very long text"
                )
                @b(b: 123)
                STRING,
                $settings,
                0,
                70,
                [
                    Parser::directive('@a(a: "very very very long text")'),
                    Parser::directive('@b(b: 123)'),
                ],
            ],
            'indent'                => [
                <<<'STRING'
                @a(
                        a: "very very very long text"
                    )
                    @b(b: 123)
                STRING,
                $settings,
                1,
                70,
                [
                    Parser::directive('@a(a: "very very very long text")'),
                    Parser::directive('@b(b: 123)'),
                ],
            ],
            'filter'                => [
                <<<'STRING'
                @a(a: 123)
                STRING,
                $settings->setDirectiveFilter(static function (string $directive): bool {
                    return $directive === 'a';
                }),
                0,
                0,
                [
                    Parser::directive('@a(a: 123)'),
                    Parser::directive('@b(b: 1234567890)'),
                    Parser::directive('@c'),
                ],
            ],
            'args always multiline' => [
                <<<'STRING'
                @a(
                    a: 123
                )
                STRING,
                $settings
                    ->setAlwaysMultilineArguments(true),
                0,
                0,
                [
                    Parser::directive('@a(a: 123)'),
                ],
            ],
            'one line'              => [
                <<<'STRING'
                @b(b: 123) @a
                STRING,
                $settings
                    ->setAlwaysMultilineDirectives(false),
                0,
                0,
                [
                    Parser::directive('@b(b: 123)'),
                    Parser::directive('@a'),
                ],
            ],
            'one line too long'     => [
                <<<'STRING'
                @b(
                    b: 123
                )
                @a
                STRING,
                $settings
                    ->setLineLength(5)
                    ->setAlwaysMultilineDirectives(false),
                0,
                0,
                [
                    Parser::directive('@b(b: 123)'),
                    Parser::directive('@a'),
                ],
            ],
            'normalized'            => [
                <<<'STRING'
                @a
                @b(b: 123)
                STRING,
                $settings
                    ->setNormalizeDirectives(true),
                0,
                0,
                [
                    Parser::directive('@b(b: 123)'),
                    Parser::directive('@a'),
                ],
            ],
        ];
    }
    // </editor-fold>
}
