<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast;

use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\AsteriskNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\GlobstarNode;
use LastDragon_ru\TextParser\Ast\NodeFactory;
use Override;

/**
 * @template TParent of NodeParent
 * @template TChild of Node
 *
 * @extends NodeFactory<TParent, TChild>
 */
abstract class Factory extends NodeFactory {
    #[Override]
    protected function merge(object $node, object $previous): object {
        $class = $node::class;

        return match (true) {
            $node instanceof AsteriskNode && $previous instanceof AsteriskNode,
            $node instanceof GlobstarNode && $previous instanceof GlobstarNode,
                => new $class($previous->count + $node->count),
            default
                => parent::merge($node, $previous),
        };
    }
}
