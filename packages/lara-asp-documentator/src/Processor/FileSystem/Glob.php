<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use LastDragon_ru\GlobMatcher\Contracts\Matcher;
use LastDragon_ru\GlobMatcher\GlobMatcher;
use LastDragon_ru\GlobMatcher\Options;
use Override;
use Stringable;

use function array_find;
use function array_values;

/**
 * @internal
 */
readonly class Glob implements Matcher {
    /**
     * @var list<Matcher>
     */
    private array $matchers;

    /**
     * @param list<non-empty-string> $patterns
     */
    public function __construct(array $patterns, bool $hidden) {
        $this->matchers = $this->matchers($patterns, $hidden);
    }

    #[Override]
    public function match(Stringable|string $string): bool {
        return array_find($this->matchers, static fn ($matcher) => $matcher->match($string)) !== null;
    }

    public function mismatch(Stringable|string $string): bool {
        return !$this->match($string);
    }

    /**
     * @param list<string> $patterns
     *
     * @return list<Matcher>
     */
    protected function matchers(array $patterns, bool $hidden): array {
        $matchers = [];
        $options  = new Options(
            matchHidden: $hidden,
            matchCase  : true,
        );

        foreach ($patterns as $pattern) {
            if (isset($matchers[$pattern]) || $pattern === '') {
                continue;
            }

            $matchers[$pattern] = new GlobMatcher($pattern, $options);
        }

        return array_values($matchers);
    }
}
