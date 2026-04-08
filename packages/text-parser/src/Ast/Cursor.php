<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Ast;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use LastDragon_ru\TextParser\Exceptions\OffsetReadonly;
use Override;
use Traversable;

/**
 * @see NodeParent
 * @see NodeChild
 *
 * @template TNode of object
 *
 * @implements IteratorAggregate<int, (TNode is NodeParent<covariant object> ? self<template-type<TNode, NodeParent, 'TChild'>> : null)>
 * @implements ArrayAccess<int<0, max>, (TNode is NodeParent<covariant object> ? self<template-type<TNode, NodeParent, 'TChild'>> : null)>
 */
class Cursor implements IteratorAggregate, ArrayAccess, Countable {
    final public function __construct(
        /**
         * @var TNode
         */
        public readonly object $node,
        /**
         * @var (TNode is NodeChild<object> ? self<template-type<TNode, NodeChild, 'TParent'>>|null : null)
         */
        public readonly ?self $parent = null,
        public readonly ?int $index = null,
    ) {
        // empty
    }

    /**
     * @var ?$this
     */
    public ?self $next {
        // @phpstan-ignore return.type (the types are not great, so waiting for refactor)
        get => $this->index !== null ? ($this->parent[$this->index + 1] ?? null) : null;
    }

    /**
     * @var ?$this
     */
    public ?self $previous {
        // @phpstan-ignore return.type (the types are not great, so waiting for refactor)
        get => $this->index !== null ? ($this->parent[$this->index - 1] ?? null) : null;
    }

    #[Override]
    public function count(): int {
        return $this->node instanceof NodeParent
            ? $this->node->count()
            : 0;
    }

    #[Override]
    public function getIterator(): Traversable {
        if ($this->node instanceof NodeParent) {
            foreach ($this->node as $key => $child) {
                yield $key => new static($child, $this, $key);
            }
        } else {
            yield from [];
        }
    }

    #[Override]
    public function offsetExists(mixed $offset): bool {
        return $this->node instanceof NodeParent
            && $this->node->offsetExists($offset);
    }

    #[Override]
    public function offsetGet(mixed $offset): mixed {
        $child = $this->node instanceof NodeParent
            ? $this->node->offsetGet($offset)
            : null;
        $child = $child !== null
            ? new static($child, $this, $offset)
            : null;

        return $child;
    }

    #[Override]
    public function offsetSet(mixed $offset, mixed $value): void {
        throw new OffsetReadonly($offset);
    }

    #[Override]
    public function offsetUnset(mixed $offset): void {
        throw new OffsetReadonly($offset);
    }
}
