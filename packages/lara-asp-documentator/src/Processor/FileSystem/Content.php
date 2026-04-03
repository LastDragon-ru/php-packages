<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;

/**
 * @internal
 */
class Content {
    /**
     * @var array<non-empty-string, string>
     */
    private array $content = [];
    /**
     * @var array<non-empty-string, FilePath>
     */
    private array $paths = [];

    public function __construct() {
        // empty
    }

    /**
     * @var array<array-key, FilePath>
     */
    public array $changes {
        get => $this->paths;
    }

    public function cleanup(): void {
        $this->paths   = [];
        $this->content = [];
    }

    public function get(FilePath $path): ?string {
        return $this->content[$path->path] ?? null;
    }

    public function set(FilePath $path, string $content): void {
        $this->paths[$path->path]   = $path;
        $this->content[$path->path] = $content;
    }

    public function delete(DirectoryPath|FilePath $path): void {
        $delete = [];

        if ($path instanceof DirectoryPath) {
            foreach ($this->paths as $item) {
                if ($path->contains($item)) {
                    $delete[] = $item->path;
                }
            }
        } else {
            $delete[] = $path->path;
        }

        foreach ($delete as $item) {
            unset($this->content[$item]);
            unset($this->paths[$item]);
        }
    }
}
