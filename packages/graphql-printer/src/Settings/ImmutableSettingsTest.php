<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQL\Printer\Settings;

use LastDragon_ru\GraphQL\Printer\Contracts\Settings;
use LastDragon_ru\GraphQL\Printer\Package\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionClass;
use ReflectionMethod;

/**
 * @internal
 */
#[CoversClass(ImmutableSettings::class)]
final class ImmutableSettingsTest extends TestCase {
    public function testCreateFrom(): void {
        $methods  = (new ReflectionClass(Settings::class))->getMethods(ReflectionMethod::IS_PUBLIC);
        $settings = Mockery::mock(Settings::class);

        foreach ($methods as $method) {
            $settings
                ->shouldReceive($method->getName())
                ->once();
        }

        ImmutableSettings::createFrom($settings);
    }
}
