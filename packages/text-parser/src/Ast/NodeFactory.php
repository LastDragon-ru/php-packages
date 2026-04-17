<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Ast;

use function array_last;
use function array_pop;

/**
 * @template TParent of object
 * @template TChild of object
 */
abstract class NodeFactory {
    /**
     * @var list<covariant TChild>
     */
    protected array $children = [];

    public function __construct() {
        // empty
    }

    public bool $empty {
        get => $this->children === [];
    }

    /**
     * @return ?TParent
     */
    public function create(): ?object {
        $node           = $this->make();
        $this->children = [];

        return $node;
    }

    /**
     * @return ?TParent
     */
    abstract protected function make(): ?object;

    /**
     * @param ?TChild $node
     */
    public function push(?object $node): bool {
        // Null/Invalid? Skip
        if ($node === null || !$this->valid($node)) {
            return false;
        }

        // Merge?
        $previous = array_last($this->children);

        if ($previous instanceof $node && $node instanceof $previous) {
            $new = $this->merge($node, $previous);

            if ($new !== $node) {
                array_pop($this->children);

                $node = $new;
            }
        }

        // Push
        $this->children[] = $node;

        // Return
        return true;
    }

    /**
     * @param TChild $node
     */
    protected function valid(object $node): bool {
        return true;
    }

    /**
     * @param TChild $node
     * @param TChild $previous
     *
     * @return new<TChild>
     */
    protected function merge(object $node, object $previous): object {
        $class = $node::class;

        return match (true) {
            $node instanceof NodeString && $previous instanceof NodeString
                => new $class($previous->string.$node->string),
            default
                => $node,
        };
    }
}
