<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast;

use LastDragon_ru\GlobMatcher\Glob\Options;

interface Node {
    /**
     * @param Cursor<covariant static> $cursor
     */
    public static function toRegex(Options $options, Cursor $cursor): string;
}
