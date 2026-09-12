<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob;

readonly class Options {
    public function __construct(
        /**
         * If set, the `**` will match all files and zero or more directories
         * and subdirectories.
         *
         * The same as `globstar`.
         */
        public bool $globstar = true,
        /**
         * Enables extended globbing (`?(pattern-list)`, etc).
         *
         * The same as `extglob`.
         */
        public bool $extended = true,
        /**
         * Filenames beginning with a dot are hidden and not matched by default
         * unless the glob begins with a dot or this option set to `true`.
         *
         * The same as `dotglob`.
         */
        public bool $matchHidden = false,
        /**
         * The same as `nocasematch`.
         */
        public bool $matchCase = true,
    ) {
        // empty
    }
}
