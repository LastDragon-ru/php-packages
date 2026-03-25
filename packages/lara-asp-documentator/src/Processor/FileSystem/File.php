<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use InvalidArgumentException;
use LastDragon_ru\Path\FilePath;

use function sprintf;

/**
 * @internal
 */
class File {
    public function __construct(
        private readonly FileSystem $fs,
        public readonly FilePath $path,
    ) {
        if (!$this->path->normalized) {
            throw new InvalidArgumentException(
                sprintf(
                    'Path must be normalized, `%s` given.',
                    $this->path,
                ),
            );
        }

        if ($this->path->relative) {
            throw new InvalidArgumentException(
                sprintf(
                    'Path must be absolute, `%s` given.',
                    $this->path,
                ),
            );
        }
    }

    public string $content {
        get => $this->fs->read($this);
    }
}
