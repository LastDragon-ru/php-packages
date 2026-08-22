<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Ast\Nodes;

use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Cursor;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Node;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\NodeParent;
use Override;

use function is_int;

use const PHP_INT_MAX;

/**
 * @implements NodeParent<Node&SequenceNodeChild>
 */
readonly class SequenceNode implements Node, NodeParent, BraceExpansionNodeChild, SequenceNodeChild {
    public function __construct(
        /**
         * @var list<Node&SequenceNodeChild>
         */
        public array $children,
    ) {
        // empty
    }

    #[Override]
    public static function toCount(Cursor $cursor): int {
        $count = 0;

        foreach ($cursor->children as $child) {
            $count += $child->node::toCount($child);

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
        foreach ($cursor->children as $child) {
            yield from $child->node::toIterable($child);
        }
    }
}
