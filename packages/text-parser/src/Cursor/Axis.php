<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Cursor;

use IteratorAggregate;

/**
 * @template TCursor of Cursor
 *
 * @extends IteratorAggregate<int<0, max>, TCursor>
 */
interface Axis extends IteratorAggregate {
    // empty
}
