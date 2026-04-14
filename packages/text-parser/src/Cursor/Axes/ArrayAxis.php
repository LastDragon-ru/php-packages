<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Cursor\Axes;

use Closure;
use LastDragon_ru\TextParser\Cursor\Axis;
use LastDragon_ru\TextParser\Cursor\Cursor;
use LastDragon_ru\TextParser\Cursor\Offsettable;
use Override;
use Traversable;

use function count;

/**
 * @template TCursor of Cursor
 *
 * @implements Axis<TCursor>
 * @implements Offsettable<TCursor>
 */
class ArrayAxis implements Axis, Offsettable {
    public function __construct(
        /**
         * @var Closure(template-type<TCursor, Cursor, 'TNodeInterface'>, int<0, max>): TCursor
         */
        protected readonly Closure $factory,
        /**
         * @var list<template-type<TCursor, Cursor, 'TNodeInterface'>>
         */
        protected readonly array $nodes,
    ) {
        // empty
    }

    #[Override]
    public function getIterator(): Traversable {
        foreach ($this->nodes as $offset => $child) {
            yield $offset => ($this->factory)($child, $offset);
        }
    }

    #[Override]
    public function count(): int {
        return count($this->nodes);
    }

    #[Override]
    public function get(int $offset): ?Cursor {
        return isset($this->nodes[$offset])
            ? ($this->factory)($this->nodes[$offset], $offset)
            : null;
    }
}
