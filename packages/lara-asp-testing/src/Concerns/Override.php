<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Concerns;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Testing\Concerns\InteractsWithContainer;
use LogicException;
use Mockery;
use Mockery\Exception\InvalidCountException;
use Mockery\MockInterface;
use OutOfBoundsException;
use Override as OverrideAttribute;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\TestCase;

use function assert;
use function is_callable;
use function is_string;
use function sprintf;

/**
 * Similar to {@see InteractsWithContainer} but will mark test as failed if
 * override was not used while test (that helps to find unused code).
 *
 * @see InteractsWithContainer
 *
 * @phpstan-require-extends TestCase
 */
trait Override {
    /**
     * @var array<class-string,MockInterface>
     */
    private array $overrides = [];

    abstract protected function app(): Application;

    /**
     * @internal
     */
    #[Before]
    protected function initOverride(): void {
        $this->overrides = [];
    }

    #[OverrideAttribute]
    protected function assertPostConditions(): void {
        foreach ($this->overrides as $class => $spy) {
            try {
                $spy->shouldHaveBeenCalled();
            } catch (InvalidCountException $exception) {
                throw new OutOfBoundsException(
                    sprintf(
                        'Override for `%s` should be used at least 1 times but used 0 times.',
                        $class,
                    ),
                    0,
                    $exception,
                );
            }
        }

        parent::assertPostConditions();
    }

    /**
     * @template T of object
     * @template R of T
     *
     * @param class-string<T> $class
     * @param (
     *      $factory is Closure(T&MockInterface):void|Closure():void
     *          ? Closure(T&MockInterface):void|Closure():void
     *          : Closure(T&MockInterface):R|Closure():R|R|class-string<R>
     *      )                 $factory
     *
     * @return ($factory is Closure(T&MockInterface):void|Closure():void ? T&MockInterface : R)
     */
    protected function override(string $class, object|string|null $factory = null): object {
        // Overridden?
        if (isset($this->overrides[$class])) {
            throw new LogicException(
                sprintf(
                    'Override for `%s` already defined.',
                    $class,
                ),
            );
        }

        // Mock
        $mock = match (true) {
            $factory instanceof Closure => (static fn ($mock) => $factory($mock) ?? $mock)(Mockery::mock($class)),
            is_string($factory)         => $this->app()->make($factory),
            default                     => $factory,
        };

        // Override
        $this->overrides[$class] = Mockery::spy(static function () use ($mock): mixed {
            return $mock;
        });

        assert(is_callable($this->overrides[$class]));

        $this->app()->bind(
            $class,
            ($this->overrides[$class])(...),
        );

        // Return
        return $mock; // @phpstan-ignore-line return.type (`ContainerExtension` is not so smart yet).
    }
}
