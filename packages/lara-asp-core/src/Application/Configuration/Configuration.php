<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Application\Configuration;

abstract class Configuration {
    protected function __construct() {
        // empty
    }

    /**
     * @param array<string, mixed> $array
     */
    public static function __set_state(array $array): static {
        // @phpstan-ignore new.static, new.staticInAbstractClassStaticMethod (this is developer responsibility)
        return new static(...$array);
    }
}
