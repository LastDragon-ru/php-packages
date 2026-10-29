<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Converters;

use Exception;
use LastDragon_ru\GlobMatcher\Glob\Ast\Cursor;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\AsteriskNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\CharacterClassNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\CharacterCollatingSymbolNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\CharacterEquivalenceClassNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\CharacterNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\GlobNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\GlobstarNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\NameNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\PatternListNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\PatternListQuantifier;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\PatternNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\QuestionNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\SegmentNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\StringNode;
use LastDragon_ru\GlobMatcher\Package;

use function array_filter;
use function array_first;
use function count;
use function implode;
use function mb_strpos;
use function preg_quote;
use function preg_replace;
use function str_replace;
use function str_starts_with;

class CursorToRegex {
    protected readonly CursorToRegexOptions $options;

    public function __construct(
        ?CursorToRegexOptions $options = null,
    ) {
        $this->options = $options ?? new CursorToRegexOptions();
    }

    /**
     * @param Cursor<*> $cursor
     */
    public function __invoke(Cursor $cursor): string {
        $regex = $this->regex($cursor);
        $delim = $this->options->delimiter;
        $flags = 'us'.($this->options->matchCase ? '' : 'i');
        $regex = "{$delim}^{$regex}\${$delim}{$flags}";

        return $regex;
    }

    /**
     * @param Cursor<*> $cursor
     */
    protected function regex(Cursor $cursor): string {
        $regex = '';

        switch (true) {
            case $cursor->node instanceof AsteriskNode:
                $regex = '[^/]*?'.($this->isLast($cursor) ? '/?' : '');
                break;
            case $cursor->node instanceof CharacterClassNode:
                $regex = "[:{$cursor->node->class->value}:]";
                break;
            case $cursor->node instanceof CharacterCollatingSymbolNode:
                $regex = "[.{$cursor->node->symbol}.]";
                break;
            case $cursor->node instanceof CharacterEquivalenceClassNode:
                $regex = "[={$cursor->node->class}=]";
                break;
            case $cursor->node instanceof CharacterNode:
                foreach ($cursor->children as $child) {
                    $regex .= match (true) {
                        $child->node instanceof StringNode => preg_replace(
                            '/([:\\\\^\\[\\]])/u',
                            '\\\\$1',
                            $child->node->string,
                        ),
                        default                            => $this->regex($child),
                    };
                }

                if ($cursor->node->negated) {
                    $regex = "^{$regex}";

                    if (mb_strpos($regex, '/', encoding: Package::Encoding) === false) {
                        $regex .= '/';
                    }
                }

                $regex = "[{$regex}]";
                break;
            case $cursor->node instanceof GlobNode:
                $regex = $this->children($cursor);
                break;
            case $cursor->node instanceof GlobstarNode:
                $mark  = 'globstar';
                $name  = $this->regex(new Cursor(new NameNode([new StringNode($mark)])));
                $name  = str_replace(["(?:{$mark})", $mark], '', $name);
                $regex = "(?:(?<=^|/)(?:{$name}[^/]*?)(?:(?:/|$)|(?=/|$)))*?";
                break;
            case $cursor->node instanceof NameNode:
                // `.` and `..` must always be matched explicitly
                $firstChild = $cursor->children->get(0);

                if (count($cursor->children) === 1 && $firstChild !== null && $this->isDot($firstChild)) {
                    $regex = $this->regex($firstChild);
                } else {
                    // Regex
                    $regex = '(?=.)'.$this->children($cursor);

                    // By default, the `.` at the start of a path or immediately
                    // following a slash must be matched explicitly.
                    if ($this->options->matchHidden || ($firstChild !== null && $this->isExplicitDot($firstChild))) {
                        $regex = "(?!\\.{1,2}(?:/|$))(?:{$regex})";
                    } else {
                        $regex = "(?!\\.)(?:{$regex})";
                    }
                }
                break;
            case $cursor->node instanceof PatternListNode:
                if (count($cursor->children) > 0) {
                    // Not not?
                    // - Optimize

                    // Build
                    $regex = $this->children($cursor, '|');
                    $regex = match ($cursor->node->quantifier) {
                        PatternListQuantifier::ZeroOrOne  => "(?:{$regex})?",
                        PatternListQuantifier::ZeroOrMore => "(?:{$regex})*",
                        PatternListQuantifier::OneOrMore  => "(?:{$regex})+",
                        PatternListQuantifier::OneOf      => "(?:{$regex})",
                        PatternListQuantifier::Not        => $this->not($cursor, $regex),
                    };
                }
                break;
            case $cursor->node instanceof PatternNode:
                $regex = $this->children($cursor);
                break;
            case $cursor->node instanceof QuestionNode:
                $regex = '[^/]';
                break;
            case $cursor->node instanceof SegmentNode:
                $regex = preg_quote('/', $this->options->delimiter);
                break;
            case $cursor->node instanceof StringNode:
                $regex = preg_quote($cursor->node->string, $this->options->delimiter);
                break;
            default:
                throw new Exception('not implemented');
        }

        return $regex;
    }

    /**
     * @param Cursor<*> $cursor
     */
    private function children(Cursor $cursor, string $separator = ''): string {
        $regex = [];

        foreach ($cursor->children as $child) {
            $regex[] = $this->regex($child);
        }

        $regex = array_filter($regex, static fn ($s) => $s !== '');
        $regex = count($regex) > 1
            ? '(?:'.implode("){$separator}(?:", $regex).')'
            : (string) array_first($regex);

        return $regex;
    }

    /**
     * @param Cursor<*> $cursor
     */
    private function isLast(Cursor $cursor): bool {
        $last   = true;
        $parent = $cursor;

        while ($parent !== null) {
            if ($parent->next !== null) {
                $last = false;
                break;
            }

            $parent = $parent->parent;
        }

        return $last;
    }

    /**
     * @param Cursor<*> $cursor
     */
    private function isDot(Cursor $cursor): bool {
        return $cursor->node instanceof StringNode
            && ($cursor->node->string === '.' || $cursor->node->string === '..');
    }

    /**
     * @param Cursor<*> $cursor
     */
    private function isExplicitDot(Cursor $cursor): bool {
        return $cursor->node instanceof StringNode
            && str_starts_with($cursor->node->string, '.');
    }

    /**
     * @param Cursor<*> $cursor
     */
    private function not(Cursor $cursor, string $regex): string {
        // Regex negative lookahead differs from glob `!(...)`, for this reason
        // we should include the following (till the `/` or end of pattern)
        // nodes to the not regex.
        $following = '';
        $parent    = $cursor;
        $last      = true;

        while ($parent !== null) {
            if (!($parent->node instanceof PatternNode)) {
                $next = $parent->next;

                while ($next !== null) {
                    if ($next->node instanceof SegmentNode) {
                        break 2;
                    }

                    $following .= '(?:'.$this->regex($next).')';
                    $next       = $next->next;
                    $last       = false;
                }
            }

            $parent = $parent->parent;
        }

        // Build
        $following = $following !== '' ? "(?:{$following})" : '';
        $anything  = '[^/]*?';
        $regex     = $last
            ? "(?!(?:{$regex}){$following}(?:$|\/)){$anything}"
            : "(?!(?:{$regex}){$following}){$anything}";

        return "(?:{$regex})";
    }
}
