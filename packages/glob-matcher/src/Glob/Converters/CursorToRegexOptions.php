<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Converters;

readonly class CursorToRegexOptions {
    public function __construct(
        /**
         * @var non-empty-string
         */
        public string $delimiter = '#',
        public bool $matchCase = true,
        public bool $matchHidden = false,
    ) {
        // empty
    }
}
