<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast;

/**
 * @template TChild of Node
 */
interface NodeParent {
    /**
     * @var list<TChild>
     */
    public array $children {
        get;
    }
}
