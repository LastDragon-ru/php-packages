<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter;

use LastDragon_ru\LaraASP\Formatter\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(PackageProvider::class)]
final class PackageProviderTest extends TestCase {
    public function testRegister(): void {
        self::assertSame(
            $this->app()->make(Formatter::class),
            $this->app()->make(Formatter::class),
        );
    }

    public function testConfig(): void {
        self::assertConfigurationExportable(PackageConfig::class);
    }
}
