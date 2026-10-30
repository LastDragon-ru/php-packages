<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob;

use InvalidArgumentException;
use LastDragon_ru\GlobMatcher\Contracts\Matcher;
use LastDragon_ru\GlobMatcher\Glob\Ast\Cursor;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\GlobNode;
use LastDragon_ru\GlobMatcher\Glob\Converters\CursorToRegex;
use LastDragon_ru\GlobMatcher\Glob\Converters\CursorToRegexOptions;
use LastDragon_ru\GlobMatcher\Glob\Converters\CursorToString;
use LastDragon_ru\GlobMatcher\Glob\Parser\Parser;
use Override;
use Stringable;

use function preg_match;

/**
 * Parse/Match glob pattern.
 *
 * @see https://en.wikipedia.org/wiki/Glob_(programming)
 */
readonly class Glob implements Stringable, Matcher {
    public GlobNode $node;
    public Options  $options;
    private string  $regex;

    public function __construct(string $pattern, ?Options $options = null) {
        $this->options = $options ?? new Options();
        $this->node    = $this->parse($this->options, $pattern);
        $this->regex   = $this->regex($this->options, $this->node);
    }

    #[Override]
    public function match(Stringable|string $string): bool {
        return (bool) preg_match($this->regex, (string) $string);
    }

    #[Override]
    public function __toString(): string {
        return (new CursorToString())(new Cursor($this->node));
    }

    private function parse(Options $options, string $pattern): GlobNode {
        $node = (new Parser($options))->parse($pattern);

        if ($node === null) {
            throw new InvalidArgumentException('The `$pattern` is not a valid glob.');
        }

        return $node;
    }

    private function regex(Options $options, GlobNode $node): string {
        $converter = new CursorToRegex(
            new CursorToRegexOptions(
                matchCase  : $options->matchCase,
                matchHidden: $options->matchHidden,
            ),
        );
        $regex     = $converter(new Cursor($node));

        return $regex;
    }
}
