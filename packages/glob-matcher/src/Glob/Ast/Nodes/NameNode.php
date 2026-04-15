<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast\Nodes;

use LastDragon_ru\GlobMatcher\Glob\Ast\Cursor;
use LastDragon_ru\GlobMatcher\Glob\Ast\Node;
use LastDragon_ru\GlobMatcher\Glob\Ast\NodeParent;
use LastDragon_ru\GlobMatcher\Glob\Ast\Utils;
use LastDragon_ru\GlobMatcher\Glob\Options;
use Override;

use function count;
use function str_starts_with;

/**
 * @implements NodeParent<Node&NameNodeChild>
 */
class NameNode implements Node, NodeParent, GlobNodeChild {
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
        // `.` and `..` must always be matched explicitly
        $children   = $cursor->children;
        $firstChild = $children->get(0);

        if (count($children) === 1 && $firstChild !== null && self::isDot($firstChild->node)) {
            return $firstChild->node::toRegex($options, $firstChild);
        }

        // Regex
        $regex = '(?=.)'.Utils::toRegex($options, $cursor);

        // By default, the `.` at the start of a path or immediately
        // following a slash must be matched explicitly.
        if ($options->hidden || ($firstChild !== null && self::isExplicitDot($firstChild->node))) {
            $regex = "(?!\\.{1,2}(?:/|$))(?:{$regex})";
        } else {
            $regex = "(?!\\.)(?:{$regex})";
        }

        // Return
        return $regex;
    }

    private static function isDot(?Node $node): bool {
        return $node instanceof StringNode
            && ($node->string === '.' || $node->string === '..');
    }

    private static function isExplicitDot(?Node $node): bool {
        return $node instanceof StringNode
            && str_starts_with($node->string, '.');
    }
}
