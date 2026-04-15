<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Cursor;

/**
 * @template TNode of object
 * @template TNodeInterface of object
 */
abstract class Cursor {
    final public function __construct(
        /**
         * @var TNode&TNodeInterface
         */
        public readonly object $node,
        /**
         * @var ?self<covariant TNodeInterface, TNodeInterface>
         */
        public readonly ?self $parent = null,
        /**
         * @var ?int<0, max>
         */
        public readonly ?int $offset = null,
    ) {
        // empty
    }

    /**
     * @var ?self<covariant TNodeInterface, TNodeInterface>
     */
    public ?self $next {
        get => $this->offset !== null
            ? $this->children()?->get($this->offset + 1)
            : null;
    }

    /**
     * @var ?self<covariant TNodeInterface, TNodeInterface>
     */
    public ?self $previous {
        get => $this->offset !== null && $this->offset > 1
            ? $this->children()?->get($this->offset - 1)
            : null;
    }

    /**
     * @var Axis<covariant self<covariant TNodeInterface, TNodeInterface>>
     */
    abstract public Axis $children {
        get;
    }

    /**
     * @return ?Offsettable<self<covariant TNodeInterface, TNodeInterface>>
     */
    private function children(): ?Offsettable {
        // Offset?
        if ($this->offset === null) {
            return null;
        }

        // Parent?
        $parent = $this->parent;

        if ($parent === null) {
            return null;
        }

        // Children?
        $children = $parent->children;

        if (!($children instanceof Offsettable)) {
            return null;
        }

        // Return
        return $children;
    }
}
