<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa;

use LastDragon_ru\LaraASP\Spa\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(PackageProvider::class)]
final class PackageProviderTest extends TestCase {
    public function testConfig(): void {
        self::assertConfigurationExportable(PackageConfig::class);
    }
}
