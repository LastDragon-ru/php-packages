<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast\Nodes;

use LastDragon_ru\GlobMatcher\Glob\Ast\Cursor;
use LastDragon_ru\GlobMatcher\Glob\Ast\Node;
use LastDragon_ru\GlobMatcher\Glob\Ast\NodeParent;
use LastDragon_ru\GlobMatcher\Glob\Ast\Utils;
use LastDragon_ru\GlobMatcher\Glob\Options;
use Override;

/**
 * @implements NodeParent<Node&GlobNodeChild>
 */
readonly class GlobNode implements Node, NodeParent {
    public function __construct(
        /**
         * @var list<Node&GlobNodeChild>
         */
        public array $children,
    ) {
        // empty
    }

    #[Override]
    public static function toRegex(Options $options, Cursor $cursor): string {
        return Utils::toRegex($options, $cursor);
    }
}
