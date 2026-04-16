<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Ast\Nodes;

use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Cursor;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Node;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\NodeParent;
use Override;

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
