<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast\Nodes;

use LastDragon_ru\GlobMatcher\Glob\Ast\Cursor;
use LastDragon_ru\GlobMatcher\Glob\Ast\Node;
use LastDragon_ru\GlobMatcher\Glob\Options;
use LastDragon_ru\TextParser\Ast\NodeMergeable;
use Override;

use function str_replace;

readonly class GlobstarNode implements Node, GlobNodeChild, NodeMergeable {
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
        $mark  = 'globstar';
        $name  = NameNode::toRegex($options, new Cursor(new NameNode([new StringNode($mark)])));
        $name  = str_replace(["(?:{$mark})", $mark], '', $name);
        $regex = "(?:(?<=^|/)(?:{$name}[^/]*?)(?:(?:/|$)|(?=/|$)))*?";

        return $regex;
    }

    #[Override]
    public static function merge(NodeMergeable $previous, NodeMergeable $current): NodeMergeable {
        if ($previous::class === $current::class) {
            $current = new static($previous->count + $current->count);
        }

        return $current; // @phpstan-ignore return.type (fixme)
    }
}
