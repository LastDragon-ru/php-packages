<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Executor;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File as Contract;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File as FileSystemFile;
use LastDragon_ru\Path\FilePath;

/**
 * @template TContent = string
 *
 * @implements Contract<TContent>
 *
 * @internal
 */
class File implements Contract {
    public function __construct(
        public readonly FileSystemFile $file,
    ) {
        // empty
    }

    public FilePath $path {
        get => $this->file->path;
    }

    public string $name {
        get => $this->path->name;
    }

    public ?string $extension {
        get => $this->path->extension;
    }

    public mixed $content {
        get => $this->file->content;
    }
}
