<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher;

use InvalidArgumentException;
use LastDragon_ru\GlobMatcher\BraceExpander\BraceExpander;
use LastDragon_ru\GlobMatcher\BraceExpander\Parser\Name as BraceExpanderName;
use LastDragon_ru\GlobMatcher\Contracts\Matcher;
use LastDragon_ru\GlobMatcher\Glob\Ast\Cursor;
use LastDragon_ru\GlobMatcher\Glob\Converters\CursorToRegex;
use LastDragon_ru\GlobMatcher\Glob\Converters\CursorToRegexOptions;
use LastDragon_ru\GlobMatcher\Glob\Glob;
use LastDragon_ru\GlobMatcher\Glob\Options as GlobOptions;
use LastDragon_ru\GlobMatcher\Glob\Parser\Name as GlobName;
use LastDragon_ru\TextParser\Iterables\StringifyIterable;
use LastDragon_ru\TextParser\Iterables\TokenEscapeIterable;
use LastDragon_ru\TextParser\Tokenizer\Token;
use LastDragon_ru\TextParser\Tokenizer\Tokenizer;
use LastDragon_ru\TextParser\Utils;
use Override;
use Stringable;

use function count;
use function implode;
use function mb_strrpos;
use function mb_substr;
use function preg_match;

readonly class GlobMatcher implements Matcher {
    private string $regex;
    public Options $options;

    public function __construct(
        public string $pattern,
        ?Options $options = null,
    ) {
        $this->options = self::options($options);
        $this->regex   = $this->regex();
    }

    #[Override]
    public function match(Stringable|string $string): bool {
        return (bool) preg_match($this->regex, (string) $string);
    }

    private function regex(): string {
        $regexes  = [];
        $options  = new GlobOptions(
            globstar   : $this->options->globstar,
            extended   : $this->options->extended,
            matchHidden: $this->options->matchHidden,
            matchCase  : $this->options->matchCase,
        );
        $patterns = $this->options->braces ? new BraceExpander($this->pattern) : [$this->pattern];

        if (count($patterns) > $this->options->bracesLimit) {
            throw new InvalidArgumentException('The `$pattern` generates too many variants.');
        }

        $settings  = new CursorToRegexOptions(
            matchCase  : $options->matchCase,
            matchHidden: $options->matchHidden,
        );
        $converter = new CursorToRegex($settings);

        foreach ($patterns as $pattern) {
            $node      = (new Glob($pattern, $options))->node;
            $regex     = $converter(new Cursor($node));
            $regexes[] = mb_substr(
                $regex,
                1,
                (int) mb_strrpos($regex, $settings->delimiter, 0, Package::Encoding) - 1,
                Package::Encoding,
            );
        }

        $delim = $settings->delimiter;
        $flags = 'us'.($this->options->matchCase ? '' : 'i');
        $regex = '(?:'.implode('|', $regexes).')';
        $regex = "{$delim}^{$regex}\${$delim}{$flags}";

        return $regex;
    }

    public static function escape(string $pattern, ?Options $options = null): string {
        $options  = self::options($options);
        $tokens   = $options->braces ? [BraceExpanderName::class, GlobName::class] : [GlobName::class];
        $iterable = (new Tokenizer($tokens))->tokenize([$pattern]);
        $iterable = new readonly class($iterable, GlobName::Backslash) extends TokenEscapeIterable {
            #[Override]
            protected function isEscapable(Token $token): bool {
                return parent::isEscapable($token)
                    && $token->name !== GlobName::Colon
                    && $token->name !== GlobName::Dot
                    && $token->name !== GlobName::Equal
                    && $token->name !== GlobName::Slash
                    && $token->name !== BraceExpanderName::Comma
                    && $token->name !== BraceExpanderName::DoubleDot;
            }
        };
        $iterable = new StringifyIterable($iterable);
        $escaped  = Utils::toString($iterable);

        return $escaped;
    }

    private static function options(?Options $options): Options {
        return $options ?? new Options();
    }
}
