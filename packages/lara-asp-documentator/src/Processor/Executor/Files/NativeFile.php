<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Executor\Files;

use LastDragon_ru\LaraASP\Documentator\Processor\Executor\Resolver;
use LastDragon_ru\Path\FilePath;
use Override;

/**
 * @internal
 *
 * @extends  Base<string>
 */
class NativeFile extends Base {
    public function __construct(
        Resolver $resolver,
        public readonly FilePath $path,
    ) {
        parent::__construct($resolver);
    }

    // @phpstan-ignore property.uninitialized (it is lazy, so all fine)
    public protected(set) mixed $content {
        get => $this->content ??= $this->resolver->read($this->path);
    }

    #[Override]
    public function save(mixed $content): void {
        $this->resolver->save($this->path, $content);

        $this->content = $content;
    }

    #[Override]
    public function delete(): void {
        $this->resolver->delete($this->path);
    }
}
