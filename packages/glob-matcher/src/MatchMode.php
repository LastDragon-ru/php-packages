<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher;

/**
 * @deprecated %{VERSION} Will be removed in the future.
 */
enum MatchMode {
    case Match;
    case Starts;
    case Ends;
    case Contains;
}
