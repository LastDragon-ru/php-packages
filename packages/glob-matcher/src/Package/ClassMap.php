<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Package;

use Composer\ClassMapGenerator\ClassMapGenerator;
use ReflectionClass;

use function dirname;
use function is_a;

/**
 * @internal
 */
class ClassMap {
    /**
     * @var array<class-string, list<class-string>>
     */
    private static $map = [];

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return list<class-string<T>>
     */
    public static function get(string $class): array {
        if (!isset(self::$map[$class])) {
            self::$map[$class] = [];
            $reflection        = new ReflectionClass($class);
            $path              = $reflection->getFileName();
            $map               = $path !== false
                ? ClassMapGenerator::createMap(dirname($path))
                : [];

            foreach ($map as $name => $path) {
                // Same?
                if ($name === $class) {
                    continue;
                }

                // Subclass?
                if (!is_a($name, $class, true)) {
                    continue;
                }

                // Instantiable?
                if (!(new ReflectionClass($name))->isInstantiable()) {
                    continue;
                }

                // Save
                self::$map[$class][] = $name;
            }
        }

        return self::$map[$class]; // @phpstan-ignore return.type (https://github.com/phpstan/phpstan/issues/9521)
    }
}
