<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast\Nodes;

use LastDragon_ru\GlobMatcher\Glob\Ast\Cursor;
use LastDragon_ru\GlobMatcher\Glob\Ast\Node;
use LastDragon_ru\GlobMatcher\Glob\Options;
use Override;

use function str_replace;

readonly class GlobstarNode implements Node, GlobNodeChild {
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
}
