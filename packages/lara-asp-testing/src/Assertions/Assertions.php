<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Assertions;

use LastDragon_ru\LaraASP\Testing\Assertions\Application\ScheduleAssertions;

trait Assertions {
    use DatabaseAssertions;
    use JsonAssertions;
    use ResponseAssertions;
    use ScheduleAssertions;
    use ScoutAssertions;
}
