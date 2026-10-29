<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Converters;

use Exception;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Node;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Nodes\BraceExpansionNode;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Nodes\CharacterSequenceNode;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Nodes\IntegerSequenceNode;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Nodes\SequenceNode;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Nodes\StringNode;

use function array_map;
use function implode;

class NodeToString {
    public function __construct() {
        // empty
    }

    public function __invoke(Node $node): string {
        return match (true) {
            $node instanceof BraceExpansionNode    => implode('', array_map($this(...), $node->children)),
            $node instanceof IntegerSequenceNode,
            $node instanceof CharacterSequenceNode => $node->increment !== null && $node->increment !== 1 && $node->increment !== 0
                ? "{{$node->start}..{$node->end}..{$node->increment}}"
                : "{{$node->start}..{$node->end}}",
            $node instanceof SequenceNode          => '{'.implode(',', array_map($this(...), $node->children)).'}',
            $node instanceof StringNode            => $node->string,
            default                                => throw new Exception('not implemented'),
        };
    }
}
