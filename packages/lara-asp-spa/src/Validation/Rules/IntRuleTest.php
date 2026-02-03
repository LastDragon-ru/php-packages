<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Validation\Rules;

use Illuminate\Contracts\Validation\Factory;
use LastDragon_ru\LaraASP\Spa\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use const INF;
use const NAN;

/**
 * @internal
 */
#[CoversClass(IntRule::class)]
final class IntRuleTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    public function testRulePasses(): void {
        $rule      = $this->app()->make(IntRule::class);
        $factory   = $this->app()->make(Factory::class);
        $validator = $factory->make(['value' => 123], ['value' => $rule]);

        self::assertFalse($validator->fails());
        self::assertEquals(
            [
                // empty
            ],
            $validator->errors()->toArray(),
        );
    }

    public function testRuleFails(): void {
        $rule      = $this->app()->make(IntRule::class);
        $factory   = $this->app()->make(Factory::class);
        $validator = $factory->make(['value' => '123'], ['value' => $rule]);

        self::assertTrue($validator->fails());
        self::assertEquals(
            [
                'value' => [
                    'The value is not an integer.',
                ],
            ],
            $validator->errors()->toArray(),
        );
    }

    #[DataProvider('dataProviderIsValid')]
    public function testIsValid(bool $expected, mixed $value): void {
        $rule   = $this->app()->make(IntRule::class);
        $actual = $rule->isValid('attribute', $value);

        self::assertSame($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<array-key, mixed>
     */
    public static function dataProviderIsValid(): array {
        return [
            'true'  => [false, true],
            'false' => [false, false],
            '0'     => [true, 0],
            '1'     => [true, 1],
            '"0"'   => [false, '0'],
            '"1"'   => [false, '1'],
            'float' => [false, 123.23],
            '+inf'  => [false, INF],
            '-inf'  => [false, -INF],
            'nan'   => [false, NAN],
        ];
    }
    // </editor-fold>
}
