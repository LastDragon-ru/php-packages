<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Converters;

use Exception;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Node;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Nodes\BraceExpansionNode;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Nodes\CharacterSequenceNode;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Nodes\IntegerSequenceNode;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Nodes\SequenceNode;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Nodes\StringNode;
use LastDragon_ru\GlobMatcher\Package;
use Traversable;

use function abs;
use function max;
use function mb_chr;
use function mb_str_pad;
use function mb_strlen;

use const STR_PAD_LEFT;

class NodeToIterable {
    use Sequence;

    public function __construct() {
        // empty
    }

    /**
     * @return Traversable<int, string>
     */
    public function __invoke(Node $node): Traversable {
        switch (true) {
            case $node instanceof BraceExpansionNode:
                foreach ($this->iterate($node, 0, '') as $value) {
                    yield $value;
                }
                break;
            case $node instanceof IntegerSequenceNode:
                [$start, $end, $increment, $steps] = $this->sequence(
                    $this->stringToInt($node->start),
                    $this->stringToInt($node->end),
                    $node->increment,
                );

                $length = $node->start !== (string) $start || $node->end !== (string) $end
                    ? max(
                        mb_strlen($node->start, Package::Encoding),
                        mb_strlen($node->end, Package::Encoding),
                    )
                    : 0;

                for ($value = $start; $steps > 0; $steps--, $value += $increment) {
                    yield $value < 0
                        ? '-'.mb_str_pad((string) abs($value), $length - 1, '0', STR_PAD_LEFT, Package::Encoding)
                        : mb_str_pad((string) $value, $length, '0', STR_PAD_LEFT, Package::Encoding);
                }
                break;
            case $node instanceof CharacterSequenceNode:
                [$start, , $increment, $steps] = $this->sequence(
                    $this->charToInt($node->start),
                    $this->charToInt($node->end),
                    $node->increment,
                );

                for ($value = $start; $steps > 0; $steps--, $value += $increment) {
                    yield mb_chr($value, Package::Encoding);
                }
                break;
            case $node instanceof SequenceNode:
                foreach ($node->children as $child) {
                    foreach ($this($child) as $value) {
                        yield $value;
                    }
                }
                break;
            case $node instanceof StringNode:
                yield $node->string;
                break;
            default:
                throw new Exception('not implemented');
        }
    }

    /**
     * @param int<0, max> $offset
     *
     * @return iterable<mixed, string>
     */
    private function iterate(BraceExpansionNode $node, int $offset, string $prefix): iterable {
        $child = $node->children[$offset] ?? null;

        if ($child !== null) {
            foreach ($this($child) as $string) {
                yield from $this->iterate($node, $offset + 1, $prefix.$string);
            }
        } else {
            yield $prefix;
        }
    }
}
