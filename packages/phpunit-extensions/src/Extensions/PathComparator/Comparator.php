<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Extensions\PathComparator;

use LastDragon_ru\Path\Path;
use Override;
use PHPUnit\Util\Exporter;
use SebastianBergmann\Comparator\Comparator as AbstractComparator;
use SebastianBergmann\Comparator\ComparisonFailure;

/**
 * Compares {@see Path} via {@see Path::equals()}.
 */
class Comparator extends AbstractComparator {
    #[Override]
    public function accepts(mixed $expected, mixed $actual): bool {
        return $expected instanceof Path && $actual instanceof Path;
    }

    #[Override]
    public function assertEquals(
        mixed $expected,
        mixed $actual,
        float $delta = 0.0,
        bool $canonicalize = false,
        bool $ignoreCase = false,
    ): void {
        // Same?
        if ($expected instanceof Path && $actual instanceof Path && $expected->equals($actual)) {
            return;
        }

        // Nope
        throw new ComparisonFailure(
            $expected,
            $actual,
            Exporter::export($expected),
            Exporter::export($actual),
            'Failed asserting that two values are equal.',
        );
    }
}
