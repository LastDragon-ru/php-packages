<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQL\Printer;

use GraphQL\Type\Schema;
use LastDragon_ru\GraphQL\Printer\Contracts\Settings;
use LastDragon_ru\GraphQL\Printer\Package\TestCase;
use LastDragon_ru\GraphQL\Printer\Settings\GraphQLSettings;
use LastDragon_ru\PhpUnit\GraphQL\PrinterSettings;
use LastDragon_ru\PhpUnit\Utils\TestData;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(IntrospectionPrinter::class)]
final class IntrospectionPrinterTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param non-empty-string $expected
     */
    #[DataProvider('dataProviderPrint')]
    public function testPrint(string $expected, Settings $settings, int $level): void {
        $directory = 'lowest';
        $expected  = TestData::get()->content("{$directory}/{$expected}");
        $printer   = (new IntrospectionPrinter())->setSettings($settings);
        $schema    = new Schema([]);
        $actual    = $printer->print($schema, $level);

        self::assertSame($expected, (string) $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{non-empty-string, Settings, int}>
     */
    public static function dataProviderPrint(): array {
        return [
            GraphQLSettings::class            => [
                'GraphQLSettings.graphql',
                new GraphQLSettings(),
                0,
            ],
            PrinterSettings::class            => [
                'PrinterSettings.graphql',
                new PrinterSettings(),
                0,
            ],
            PrinterSettings::class.' (level)' => [
                'PrinterSettings-level.graphql',
                new PrinterSettings(),
                1,
            ],
        ];
    }
    // </editor-fold>
}
