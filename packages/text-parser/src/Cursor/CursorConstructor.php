<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Cursor;

/**
 * @template TNodeInterface of object
 * @template TNode of TNodeInterface
 */
interface CursorConstructor {
    /**
     * @param TNode                                             $node
     * @param ?Cursor<TNodeInterface, covariant TNodeInterface> $parent
     * @param ?int<0, max>                                      $offset
     */
    public function __construct(object $node, ?Cursor $parent = null, ?int $offset = null);
}
