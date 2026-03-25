<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use ArrayAccess;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use Override;

use function array_values;

/**
 * @internal
 * @implements ArrayAccess<FilePath, string>
 */
class Content implements ArrayAccess {
    /**
     * @var array<non-empty-string, string>
     */
    private array $files = [];
    /**
     * @var array<non-empty-string, FilePath>
     */
    private array $paths = [];

    public function __construct() {
        // empty
    }

    public function changed(FilePath $path): bool {
        return isset($this->files[$path->path]);
    }

    /**
     * @return list<FilePath>
     */
    public function changes(): array {
        return array_values($this->paths);
    }

    public function cleanup(): void {
        $this->files = [];
        $this->paths = [];
    }

    public function delete(DirectoryPath|FilePath $path): void {
        $delete = [];

        if ($path instanceof DirectoryPath) {
            foreach ($this->paths as $item) {
                if ($path->contains($item)) {
                    $delete[] = $item;
                }
            }
        } else {
            $delete[] = $path;
        }

        foreach ($delete as $item) {
            $this->reset($item);
        }
    }

    public function reset(FilePath $path): void {
        unset($this[$path]);
    }

    #[Override]
    public function offsetExists(mixed $offset): bool {
        return isset($this->files[$offset->path]);
    }

    #[Override]
    public function offsetGet(mixed $offset): mixed {
        return $this->files[$offset->path] ?? null;
    }

    #[Override]
    public function offsetSet(mixed $offset, mixed $value): void {
        // Null? ($content[] = '...')
        if ($offset === null) {
            return;
        }

        // Save
        $this->paths[$offset->path] = $offset;
        $this->files[$offset->path] = $value;
    }

    #[Override]
    public function offsetUnset(mixed $offset): void {
        unset($this->paths[$offset->path]);
        unset($this->files[$offset->path]);
    }
}
