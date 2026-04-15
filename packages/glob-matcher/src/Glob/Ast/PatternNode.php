<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast;

use LastDragon_ru\GlobMatcher\Glob\Options;
use Override;

/**
 * @implements NodeParent<Node&NameNodeChild>
 */
class PatternNode implements Node, NodeParent, PatternListNodeChild {
    public function __construct(
        /**
         * @var list<Node&NameNodeChild>
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
