<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Ast\Nodes;

use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Cursor;
use LastDragon_ru\GlobMatcher\Package;
use Override;

use function abs;
use function filter_var;
use function floor;
use function max;
use function mb_ltrim;
use function mb_str_pad;
use function mb_strlen;
use function mb_strpos;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_INT;
use const STR_PAD_LEFT;

readonly class IntegerSequenceNode extends IncrementalSequenceNode {
    public function __construct(
        /**
         * @var numeric-string
         */
        public string $start,
        /**
         * @var numeric-string
         */
        public string $end,
        public ?int $increment = null,
    ) {
        // empty
    }

    #[Override]
    public static function toCount(Cursor $cursor): int {
        return self::prepare($cursor)[2];
    }

    #[Override]
    public static function toIterable(Cursor $cursor): iterable {
        [$start, $inc, $steps, $length] = self::prepare($cursor);

        for ($value = $start; $steps > 0; $steps--, $value += $inc) {
            yield $value < 0
                ? '-'.mb_str_pad((string) abs($value), $length - 1, '0', STR_PAD_LEFT, Package::Encoding)
                : mb_str_pad((string) $value, $length, '0', STR_PAD_LEFT, Package::Encoding);
        }
    }

    protected static function parse(string $string): int {
        $negative = mb_strpos($string, '-', 0, Package::Encoding) === 0;
        $trimmed  = mb_ltrim($string, '-0', Package::Encoding);
        $trimmed  = ($negative ? '-' : '').($trimmed !== '' ? $trimmed : '0');
        $integer  = (int) filter_var($trimmed, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

        return $integer;
    }

    /**
     * @param Cursor<covariant static> $cursor
     *
     * @return array{int, int, int<0, max>, int<0, max>}
     */
    private static function prepare(Cursor $cursor): array {
        $start  = static::parse($cursor->node->start);
        $end    = static::parse($cursor->node->end);
        $inc    = abs($cursor->node->increment ?? 1);
        $inc    = $start < $end ? $inc : -$inc;
        $steps  = (int) floor(abs(($end - $start) / $inc)) + 1;
        $steps  = max(0, $steps);
        $length = $cursor->node->start !== (string) $start || $cursor->node->end !== (string) $end
            ? max(
                mb_strlen($cursor->node->start, Package::Encoding),
                mb_strlen($cursor->node->end, Package::Encoding),
            )
            : 0;

        return [$start, $inc, $steps, $length];
    }
}
