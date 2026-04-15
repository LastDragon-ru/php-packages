<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Cursor;

use Countable;

/**
 * @template TCursor of Cursor
 */
interface Offsettable extends Countable {
    /**
     * @param int<0, max> $offset
     *
     * @return ?TCursor
     */
    public function get(int $offset): ?Cursor;
}
