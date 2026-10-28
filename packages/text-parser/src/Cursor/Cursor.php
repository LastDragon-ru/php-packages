<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Cursor;

use Override;

/**
 * @template TNodeInterface of object
 * @template TNode of TNodeInterface
 *
 * @implements CursorConstructor<TNodeInterface, TNode>
 */
abstract class Cursor implements CursorConstructor {
    #[Override]
    public function __construct(
        /**
         * @var TNode
         */
        public readonly object $node,
        /**
         * @var ?static<TNodeInterface, *>
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
     * @var ?static<TNodeInterface, *>
     */
    public ?self $next {
        get => $this->offset !== null
            ? $this->children()?->get($this->offset + 1)
            : null;
    }

    /**
     * @var ?static<TNodeInterface, *>
     */
    public ?self $previous {
        get => $this->offset !== null && $this->offset > 1
            ? $this->children()?->get($this->offset - 1)
            : null;
    }

    /**
     * @var Axis<covariant static<TNodeInterface, *>>
     */
    abstract public Axis $children {
        get;
    }

    /**
     * @return ?Offsettable<static<TNodeInterface, *>>
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
