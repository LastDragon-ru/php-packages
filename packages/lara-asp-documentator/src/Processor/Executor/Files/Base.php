<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Executor\Files;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Format;
use LastDragon_ru\LaraASP\Documentator\Processor\Executor\Resolver;
use Override;
use WeakMap;

/**
 * @internal
 *
 * @template TContent
 *
 * @implements File<TContent>
 */
abstract class Base implements File {
    /**
     * @var WeakMap<Format<TContent, *>, File<*>>
     */
    private WeakMap $formats;

    protected function __construct(
        protected readonly Resolver $resolver,
    ) {
        $this->formats = new WeakMap();
    }

    public string $name {
        get => $this->path->name;
    }

    public ?string $extension {
        get => $this->path->extension;
    }

    #[Override]
    public function as(string $format): File {
        $instance                   = $this->resolver->format($format);
        $this->formats[$instance] ??= new FormattedFile($this->resolver, $instance, $this);

        return $this->formats[$instance]; // @phpstan-ignore return.type (https://github.com/phpstan/phpstan/issues/9521)
    }

    /**
     * @param Format<TContent, *> $except
     */
    protected function reset(Format $except): void {
        if (isset($this->formats[$except])) {
            $file                   = $this->formats[$except];
            $this->formats          = new WeakMap();
            $this->formats[$except] = $file;
        } else {
            $this->formats = new WeakMap();
        }
    }
}
