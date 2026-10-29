<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Converters;

use LastDragon_ru\GlobMatcher\Package;

use function abs;
use function filter_var;
use function intval;
use function max;
use function mb_ltrim;
use function mb_ord;
use function mb_strpos;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_INT;

/**
 * @internal
 */
trait Sequence {
    /**
     * @return array{int, int, int, int<0, max>}
     */
    private function sequence(int $start, int $end, ?int $increment): array {
        $increment = max(abs($increment ?? 1), 1);
        $increment = $start < $end ? $increment : -$increment;
        $steps     = intval(abs(($end - $start) / $increment)) + 1;
        $steps     = max(0, $steps);

        return [$start, $end, $increment, $steps];
    }

    private function charToInt(string $string): int {
        return mb_ord($string, Package::Encoding);
    }

    private function stringToInt(string $string): int {
        $negative = mb_strpos($string, '-', 0, Package::Encoding) === 0;
        $trimmed  = mb_ltrim($string, '-0', Package::Encoding);
        $trimmed  = ($negative ? '-' : '').($trimmed !== '' ? $trimmed : '0');
        $integer  = (int) filter_var($trimmed, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

        return $integer;
    }
}
