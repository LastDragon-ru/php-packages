<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Docs\Calculator\Ast;

use LastDragon_ru\TextParser\Cursor\Axes\ArrayAxis;
use LastDragon_ru\TextParser\Cursor\Axis;
use LastDragon_ru\TextParser\Cursor\Cursor as NodeCursor;
use LastDragon_ru\TextParser\Cursor\Offsettable;

/**
 * @template TNode of Node
 *
 * @extends NodeCursor<TNode, Node>
 */
class Cursor extends NodeCursor {
    /**
     * @var Axis<covariant self<covariant TNode>>&Offsettable<covariant self<covariant TNode>>
     */
    public Axis&Offsettable $children {
        get => new ArrayAxis(
            fn ($node, $offset) => new static($node, $this, $offset),
            $this->node instanceof ParentNode ? $this->node->children : [],
        );
    }
}
