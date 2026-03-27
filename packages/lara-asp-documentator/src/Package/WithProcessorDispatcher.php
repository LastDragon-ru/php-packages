<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Package;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Event;
use LastDragon_ru\LaraASP\Documentator\Processor\Dispatcher;

/**
 * @internal
 */
class WithProcessorDispatcher extends Dispatcher {
    /**
     * @var list<Event>
     */
    public array $events = [];

    public function __construct() {
        parent::__construct(function (Event $event): void {
            $this->events[] = $event;
        });
    }
}
