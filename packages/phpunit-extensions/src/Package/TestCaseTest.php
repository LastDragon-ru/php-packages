<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Package;

use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * @internal
 */
#[CoversNothing]
final class TestCaseTest extends TestCase {
    use IntegrationTests;
}
