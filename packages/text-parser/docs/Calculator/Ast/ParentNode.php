<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Docs\Calculator\Ast;

/**
 * @template TChild of Node
 */
interface ParentNode {
    /**
     * @var list<TChild>
     */
    public array $children {
        get;
    }
}
