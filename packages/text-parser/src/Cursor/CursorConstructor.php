<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Cursor;

/**
 * @template TNode of object
 * @template TNodeInterface of object
 */
interface CursorConstructor {
    /**
     * @param TNode&TNodeInterface                              $node
     * @param ?Cursor<covariant TNodeInterface, TNodeInterface> $parent
     * @param ?int<0, max>                                      $offset
     */
    public function __construct(object $node, ?Cursor $parent = null, ?int $offset = null);
}
