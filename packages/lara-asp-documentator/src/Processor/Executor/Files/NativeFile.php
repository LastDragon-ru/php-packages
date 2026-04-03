<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Executor\Files;

use LastDragon_ru\LaraASP\Documentator\Processor\Executor\Resolver;
use LastDragon_ru\Path\FilePath;
use Override;

/**
 * @internal
 *
 * @extends Base<string>
 */
class NativeFile extends Base {
    private ?string $value = null;

    public function __construct(
        Resolver $resolver,
        public readonly FilePath $path,
    ) {
        parent::__construct($resolver);
    }

    public mixed $content {
        get => $this->value ??= $this->resolver->read($this->path);
    }

    #[Override]
    public function save(mixed $content): void {
        // Changed?
        if ($this->value === $content) {
            return;
        }

        // Change
        $this->resolver->save($this->path, $content);

        $this->value = $content;
    }

    #[Override]
    public function delete(): void {
        $this->resolver->delete($this->path);
    }
}
