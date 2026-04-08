<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Assertions\Application\ScheduleMatchers;

use Illuminate\Console\Application;
use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Contracts\Container\Container;
use LastDragon_ru\LaraASP\Testing\Assertions\Application\ScheduleMatcher;
use Override;
use ReflectionAttribute;
use ReflectionClass;
use Symfony\Component\Console\Attribute\AsCommand;

use function array_filter;
use function array_map;
use function array_unique;
use function in_array;
use function is_a;
use function is_string;

/**
 * @internal
 */
readonly class CommandMatcher implements ScheduleMatcher {
    public function __construct(
        protected Container $container,
    ) {
        // empty
    }

    #[Override]
    public function isMatch(Event $event, mixed $task): bool {
        // Command?
        if (!isset($event->command)) {
            return false;
        }

        // Check
        $variants = match (true) {
            is_string($task) && is_a($task, Command::class, true) => array_unique(
                array_filter(
                    [
                        Application::formatCommandString($this->container->make($task)->getName() ?? ''),
                        ...array_map(
                            static function (ReflectionAttribute $attribute): string {
                                return $attribute->newInstance()->name;
                            },
                            (new ReflectionClass($task))->getAttributes(
                                AsCommand::class,
                                ReflectionAttribute::IS_INSTANCEOF,
                            ),
                        ),
                    ],
                    static function (string $command): bool {
                        return $command !== '';
                    },
                ),
            ),
            is_string($task)                                      => [
                Application::formatCommandString($task),
                $task,
            ],
            default                                               => [
                // empty
            ],
        };

        return in_array($event->command, $variants, true);
    }
}
