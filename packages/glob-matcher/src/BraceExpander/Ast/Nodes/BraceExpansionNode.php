<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Ast\Nodes;

use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Cursor;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Node;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\NodeParent;
use Override;

use function is_int;

use const PHP_INT_MAX;

/**
 * @implements NodeParent<Node&BraceExpansionNodeChild>
 */
readonly class BraceExpansionNode implements Node, NodeParent, SequenceNodeChild {
    public function __construct(
        /**
         * @var list<Node&BraceExpansionNodeChild>
         */
        public array $children,
    ) {
        // empty
    }

    #[Override]
    public static function toCount(Cursor $cursor): int {
        $count = 1;

        foreach ($cursor->children as $child) {
            $value = $child->node::toCount($child);
            $count = ($value > 0 ? $value : 1) * $count;

            // @phpstan-ignore function.alreadyNarrowedType (if overflow it will be float)
            if (!is_int($count)) {
                $count = PHP_INT_MAX;
                break;
            }
        }

        return $count;
    }

    #[Override]
    public static function toIterable(Cursor $cursor): iterable {
        yield from self::iterate($cursor, 0, '');
    }

    /**
     * @param Cursor<covariant static> $cursor
     * @param int<0, max>              $offset
     *
     * @return iterable<mixed, string>
     */
    private static function iterate(Cursor $cursor, int $offset, string $prefix): iterable {
        $child = $cursor->children->get($offset);

        if ($child !== null) {
            $iterable = $child->node::toIterable($child);

            foreach ($iterable as $string) {
                yield from self::iterate($cursor, $offset + 1, $prefix.$string);
            }
        } else {
            yield $prefix;
        }
    }
}
