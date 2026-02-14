<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Package;

use Exception;
use LastDragon_ru\PhpUnit\Extensions\StrictScalarComparator\Extension;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

use function sprintf;

/**
 * @internal
 * @mixin PHPUnitTestCase
 * @phpstan-require-extends PHPUnitTestCase
 */
trait IntegrationTests {
    public function testIntegrationStrictScalarComparator(): void {
        $exception = null;

        try {
            self::assertEquals(1, true);
        } catch (Exception $exception) {
            // empty
        }

        self::assertInstanceOf(
            ExpectationFailedException::class,
            $exception,
            sprintf(
                'Extension `%s` is not registered?',
                Extension::class,
            ),
        );
    }
}
