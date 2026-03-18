<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Concerns;

trait Concerns {
    use DatabaseQueryComparator;
    use ModelComparator;
    use Override;
    use StrictAssertEquals;
}
