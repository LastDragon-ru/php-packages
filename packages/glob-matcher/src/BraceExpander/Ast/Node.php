<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Ast;

interface Node {
    /**
     * @param Cursor<covariant static> $cursor
     *
     * @return int<0, max>
     */
    public static function toCount(Cursor $cursor): int;

    /**
     * @param Cursor<covariant static> $cursor
     *
     * @return iterable<mixed, string>
     */
    public static function toIterable(Cursor $cursor): iterable;
}
