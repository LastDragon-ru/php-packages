<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Executor;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File as Contract;
use LastDragon_ru\Path\FilePath;
use Override;

/**
 * @implements Contract<string>
 *
 * @internal
 */
class File implements Contract {
    public function __construct(
        public readonly FilePath $path,
        private readonly Resolver $resolver,
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
    public protected(set) mixed $content {
        get => $this->content ?? $this->resolver->read($this->path);
    }

    #[Override]
    public function save(mixed $content): void {
        $this->resolver->save($this->path, $content);

        $this->content = $content;
    }
}
