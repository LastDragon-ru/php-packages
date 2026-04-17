<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast\Nodes;

use LastDragon_ru\GlobMatcher\Glob\Ast\Cursor;
use LastDragon_ru\GlobMatcher\Glob\Ast\Node;
use LastDragon_ru\GlobMatcher\Glob\Options;
use Override;

readonly class AsteriskNode implements Node, NameNodeChild {
    final public function __construct(
        /**
         * @var positive-int
         */
        public int $count = 1,
    ) {
        // empty
    }

    #[Override]
    public static function toRegex(Options $options, Cursor $cursor): string {
        return '[^/]*?'.(self::isLast($cursor) ? '/?' : '');
    }

    /**
     * @param Cursor<covariant static> $cursor
     */
    private static function isLast(Cursor $cursor): bool {
        $last   = true;
        $parent = $cursor;

        while ($parent !== null) {
            if ($parent->next !== null) {
                $last = false;
                break;
            }

            $parent = $parent->parent;
        }

        return $last;
    }
}
