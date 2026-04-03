<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Executor\Files;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Format;
use LastDragon_ru\LaraASP\Documentator\Processor\Executor\Resolver;
use LastDragon_ru\Path\FilePath;
use Override;

/**
 * @internal
 *
 * @template TContent
 * @template TSource
 *
 * @extends Base<TContent>
 */
class FormattedFile extends Base {
    /**
     * @var TContent
     */
    private mixed $value = null;

    public function __construct(
        Resolver $resolver,
        /**
         * @var Format<TSource, TContent>
         */
        private readonly Format $format,
        /**
         * @var Base<TSource>
         */
        private readonly Base $parent,
    ) {
        parent::__construct($resolver);
    }

    public FilePath $path {
        get => $this->parent->path;
    }

    public mixed $content {
        get => $this->value ??= $this->format->read($this->resolver, $this->parent);
    }

    #[Override]
    public function save(mixed $content): void {
        // Changed?
        if ($this->value === $content) {
            return;
        }

        // Change
        $this->parent->save($this->format->write($this->resolver, $this, $content));
        $this->parent->reset($this->format);

        $this->value = $content;
    }

    #[Override]
    public function delete(): void {
        $this->parent->delete();
    }
}
