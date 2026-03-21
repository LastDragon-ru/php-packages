<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQL\Printer\Package;

use LastDragon_ru\PhpUnit\GraphQL\Assertions;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * @internal
 */
abstract class TestCase extends PHPUnitTestCase {
    use Assertions;
    use MockeryPHPUnitIntegration;
}
