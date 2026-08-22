<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Ast\Nodes;

use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Cursor;
use LastDragon_ru\GlobMatcher\Package;
use Override;

use function abs;
use function floor;
use function max;
use function mb_chr;
use function mb_ord;

readonly class CharacterSequenceNode extends IncrementalSequenceNode {
    public function __construct(
        /**
         * @var non-empty-string
         */
        public string $start,
        /**
         * @var non-empty-string
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
        [$start, $inc, $steps] = self::prepare($cursor);

        for ($code = $start; $steps > 0; $steps--, $code += $inc) {
            yield mb_chr($code, Package::Encoding);
        }
    }

    /**
     * @param Cursor<covariant static> $cursor
     *
     * @return array{int, int, int<0, max>}
     */
    protected static function prepare(Cursor $cursor): array {
        $start = mb_ord($cursor->node->start, Package::Encoding);
        $end   = mb_ord($cursor->node->end, Package::Encoding);
        $inc   = max(abs($cursor->node->increment ?? 1), 1);
        $inc   = $start < $end ? $inc : -$inc;
        $steps = (int) floor(abs(($end - $start) / $inc)) + 1;
        $steps = max(0, $steps);

        return [$start, $inc, $steps];
    }
}
