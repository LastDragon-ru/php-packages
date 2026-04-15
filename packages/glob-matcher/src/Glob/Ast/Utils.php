<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast;

use LastDragon_ru\GlobMatcher\Glob\Options;

use function array_filter;
use function array_first;
use function count;
use function implode;

class Utils {
    /**
     * @param Cursor<covariant Node&NodeParent<covariant Node>> $cursor
     */
    public static function toRegex(Options $options, Cursor $cursor, string $separator = ''): string {
        $regex = [];

        foreach ($cursor->children as $child) {
            $regex[] = $child->node::toRegex($options, $child);
        }

        $regex = array_filter($regex, static fn ($s) => $s !== '');
        $regex = count($regex) > 1
            ? '(?:'.implode("){$separator}(?:", $regex).')'
            : (string) array_first($regex);

        return $regex;
    }
}
