<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Executor;

use Closure;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File as Contract;
use LastDragon_ru\Path\FilePath;

/**
 * @template TContent
 *
 * @implements Contract<TContent>
 *
 * @internal
 */
class File implements Contract {
    public function __construct(
        public readonly FilePath $path,
        /**
         * @var Closure(): TContent
         */
        private readonly Closure $callback,
    ) {
        // empty
    }

    public string $name {
        get => $this->path->name;
    }

    public ?string $extension {
        get => $this->path->extension;
    }

    // @phpstan-ignore property.uninitialized (it is lazy, so all fine)
    public mixed $content {
        get => $this->content ?? ($this->callback)();
    }
}
