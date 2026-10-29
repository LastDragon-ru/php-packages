<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander;

use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\Nodes\BraceExpansionNode;
use LastDragon_ru\GlobMatcher\BraceExpander\Converters\NodeToCount;
use LastDragon_ru\GlobMatcher\BraceExpander\Converters\NodeToIterable;
use LastDragon_ru\GlobMatcher\BraceExpander\Converters\NodeToString;
use LastDragon_ru\GlobMatcher\BraceExpander\Parser\Parser;
use Override;
use Stringable;
use Traversable;

/**
 * Expands Bash Brace Expansion.
 *
 * @see https://www.gnu.org/software/bash/manual/html_node/Brace-Expansion.html
 *
 * @implements IteratorAggregate<int, string>
 */
readonly class BraceExpander implements Stringable, Countable, IteratorAggregate {
    public BraceExpansionNode $node;

    public function __construct(string $pattern) {
        $this->node = $this->parse($pattern);
    }

    private function parse(string $pattern): BraceExpansionNode {
        $node = (new Parser())->parse($pattern);

        if ($node === null) {
            throw new InvalidArgumentException('The `$pattern` is not a valid brace expansion string.');
        }

        return $node;
    }

    #[Override]
    public function count(): int {
        return (new NodeToCount())($this->node);
    }

    #[Override]
    public function getIterator(): Traversable {
        return (new NodeToIterable())($this->node);
    }

    #[Override]
    public function __toString(): string {
        return (new NodeToString())($this->node);
    }
}
