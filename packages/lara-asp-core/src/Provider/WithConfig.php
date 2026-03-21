<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Provider;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\CachesConfiguration;
use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Core\Application\Configuration\Configuration;
use LastDragon_ru\LaraASP\Core\Application\Configuration\ConfigurationResolver;

/**
 * @see Configuration
 *
 * @phpstan-require-extends ServiceProvider
 */
trait WithConfig {
    use Helper;

    /**
     * @template C of Configuration
     * @template T of ConfigurationResolver<C>
     *
     * @param class-string<T> $resolver
     */
    protected function registerConfig(string $resolver): void {
        $package = $this->getName();

        $this->app->singletonIf($resolver);
        $this->loadPackageConfig($resolver);
        $this->publishes([
            $this->getPath('../defaults/config.php') => $this->app->configPath("{$package}.php"),
        ], 'config');
    }

    /**
     * @param class-string<ConfigurationResolver<covariant Configuration>> $resolver
     */
    private function loadPackageConfig(string $resolver): void {
        if (!($this->app instanceof CachesConfiguration && $this->app->configurationIsCached())) {
            $repository = $this->app->make(Repository::class);
            $package    = $this->getName();
            $current    = $repository->get($package, null);

            if ($current === null) {
                $repository->set([
                    $package => $resolver::getDefaultConfig(),
                ]);
            }
        }
    }
}
