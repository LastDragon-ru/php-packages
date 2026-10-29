<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Converters;

use Exception;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Node;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Nodes\BraceExpansionNode;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Nodes\CharacterSequenceNode;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Nodes\IntegerSequenceNode;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Nodes\SequenceNode;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Nodes\StringNode;

use function is_int;

use const PHP_INT_MAX;

class NodeToCount {
    use Sequence;

    public function __construct() {
        // empty
    }

    /**
     * @return int<0, max>
     */
    public function __invoke(Node $node): int {
        $count = 0;

        switch (true) {
            case $node instanceof BraceExpansionNode:
                $count = 1;

                foreach ($node->children as $child) {
                    $value = $this($child);
                    $count = ($value > 0 ? $value : 1) * $count;

                    // @phpstan-ignore function.alreadyNarrowedType (if overflow it will be float)
                    if (!is_int($count)) {
                        break;
                    }
                }
                break;
            case $node instanceof IntegerSequenceNode:
                $count = $this->sequence(
                    $this->stringToInt($node->start),
                    $this->stringToInt($node->end),
                    $node->increment,
                )[3];
                break;
            case $node instanceof CharacterSequenceNode:
                $count = $this->sequence(
                    $this->charToInt($node->start),
                    $this->charToInt($node->end),
                    $node->increment,
                )[3];
                break;
            case $node instanceof SequenceNode:
                foreach ($node->children as $child) {
                    $count += $this($child);

                    // @phpstan-ignore function.alreadyNarrowedType (if overflow it will be float)
                    if (!is_int($count)) {
                        break;
                    }
                }
                break;
            case $node instanceof StringNode:
                $count = 1;
                break;
            default:
                throw new Exception('not implemented');
        }

        // @phpstan-ignore function.alreadyNarrowedType (if overflow it will be float)
        if (!is_int($count)) {
            $count = PHP_INT_MAX;
        }

        return $count;
    }
}
