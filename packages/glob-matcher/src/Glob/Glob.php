<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob;

use InvalidArgumentException;
use LastDragon_ru\GlobMatcher\Contracts\Matcher;
use LastDragon_ru\GlobMatcher\Glob\Ast\Cursor;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\GlobNode;
use LastDragon_ru\GlobMatcher\Glob\Converters\CursorToRegex;
use LastDragon_ru\GlobMatcher\Glob\Converters\CursorToRegexOptions;
use LastDragon_ru\GlobMatcher\Glob\Parser\Parser;
use LastDragon_ru\GlobMatcher\Package;
use LastDragon_ru\GlobMatcher\Regex;
use Override;
use Stringable;

use function mb_strrpos;
use function mb_substr;

/**
 * Parse/Match glob pattern.
 *
 * @see https://en.wikipedia.org/wiki/Glob_(programming)
 */
readonly class Glob implements Matcher {
    /**
     * @deprecated 11.2.0 Will be removed in the future.
     */
    public GlobNode $node;
    /**
     * @deprecated 11.2.0 Will be removed in the future.
     */
    public Regex   $regex;
    public Options $options;

    public function __construct(string $pattern, ?Options $options = null) {
        $this->options = $options ?? new Options();
        $this->node    = $this->parse($this->options, $pattern);
        $this->regex   = $this->regex($this->options, $this->node);
    }

    #[Override]
    public function match(Stringable|string $string): bool {
        return $this->regex->match($string);
    }

    private function parse(Options $options, string $pattern): GlobNode {
        $node = (new Parser($options))->parse($pattern);

        if ($node === null) {
            throw new InvalidArgumentException('The `$pattern` is not a valid glob.');
        }

        return $node;
    }

    private function regex(Options $options, GlobNode $node): Regex {
        $converter = new CursorToRegex(
            new CursorToRegexOptions(
                matchCase  : $options->matchCase,
                matchHidden: $options->matchHidden || $options->hidden,
            ),
        );
        $regex     = $converter(new Cursor($node));
        $regex     = mb_substr($regex, 1, (int) mb_strrpos($regex, '#', 0, Package::Encoding) - 1, Package::Encoding);
        $regex     = new Regex($regex, $options->matchMode, $options->matchCase);

        return $regex;
    }
}
