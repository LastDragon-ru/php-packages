<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Executor;

use Closure;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Cast;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Container;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Resolver as Contract;
use LastDragon_ru\LaraASP\Documentator\Processor\Dispatcher;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Executor\File as FileImpl;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File as FileSystemFile;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use Override;
use WeakMap;

/**
 * @internal
 */
class Resolver implements Contract {
    /**
     * @var array<class-string<Cast<mixed>>, Cast<mixed>>
     */
    private array $casts;
    /**
     * @var WeakMap<File<string>, array<class-string<Cast<mixed>>, mixed>>
     */
    private WeakMap $files;
    /**
     * @var WeakMap<FileSystemFile, File<string>>
     */
    private WeakMap $cache;

    public function __construct(
        private readonly Container $container,
        protected readonly Dispatcher $dispatcher,
        protected readonly FileSystem $fs,
        /**
         * @var Closure(FilePath): void
         */
        protected readonly Closure $run,
        /**
         * @var Closure(FilePath): void
         */
        protected readonly Closure $save,
        /**
         * @var Closure(FilePath): void
         */
        protected readonly Closure $queue,
        /**
         * @var Closure(DirectoryPath|FilePath): void
         */
        protected readonly Closure $delete,
    ) {
        $this->casts = [];
        $this->files = new WeakMap();
        $this->cache = new WeakMap();
    }

    public DirectoryPath $input {
        get => $this->fs->input;
    }

    public DirectoryPath $output {
        get => $this->fs->output;
    }

    public DirectoryPath $directory {
        get => $this->fs->directory;
    }

    /**
     * @return File<string>
     */
    public function file(FilePath $path): File {
        return $this->wrap($this->fs->get($this->path($path)));
    }

    #[Override]
    public function get(FilePath $path): File {
        $path = $this->path($path);

        ($this->dispatcher)(new Dependency($path, DependencyResult::Found));

        $file = $this->fs->get($path);

        ($this->run)($file->path);

        return $this->wrap($file);
    }

    #[Override]
    public function find(FilePath $path): ?File {
        $file   = null;
        $path   = $this->path($path);
        $exists = $this->fs->exists($path);

        if ($exists) {
            ($this->dispatcher)(new Dependency($path, DependencyResult::Found));

            $file = $this->fs->get($path);
            $file = $this->wrap($file);

            ($this->run)($file->path);
        } else {
            ($this->dispatcher)(new Dependency($path, DependencyResult::NotFound));
        }

        return $file;
    }

    #[Override]
    public function cast(File|FilePath $path, string $cast): mixed {
        $file = $path instanceof File ? $path : $this->get($path);

        if (!isset($this->files[$file][$cast])) {
            $this->casts[$cast]      ??= $this->container->make($cast);
            $this->files[$file]      ??= [];
            $this->files[$file][$cast] = ($this->casts[$cast])($this, $file);
        }

        return $this->files[$file][$cast];
    }

    #[Override]
    public function save(File|FilePath $path, string $content): void {
        $file = $path instanceof File ? $path : null;
        $path = $this->path($path instanceof File ? $path->path : $path);

        ($this->dispatcher)(new Dependency($path, DependencyResult::Saved));

        try {
            $saved = $this->fs->write($path, $content);
            $saved = $this->wrap($saved);

            ($this->save)($path);
        } finally {
            $this->files = new WeakMap(); // fixme!: Temp

//            if (($saved ?? $file) !== null) {
//                unset($this->files[$saved ?? $file]);
//            }
        }
    }

    #[Override]
    public function queue(FilePath|iterable $path): void {
        $iterator = $path instanceof FilePath ? [$path] : $path;

        foreach ($iterator as $file) {
            $filepath = $this->path($file);

            ($this->dispatcher)(new Dependency($filepath, DependencyResult::Queued));
            ($this->queue)($filepath);
        }
    }

    #[Override]
    public function delete(DirectoryPath|FilePath|File|iterable $path): void {
        $iterator = match (true) {
            $path instanceof FilePath || $path instanceof DirectoryPath => [$path],
            $path instanceof File                                       => [$path->path],
            default                                                     => $path,
        };

        foreach ($iterator as $delete) {
            $delete = $this->path($delete);

            ($this->dispatcher)(new Dependency($delete, DependencyResult::Deleted));

            $this->fs->delete($delete);

            ($this->delete)($delete);
        }
    }

    #[Override]
    public function search(
        ?DirectoryPath $directory = null,
        array|string $include = [],
        array|string $exclude = [],
        bool $hidden = false,
    ): iterable {
        $path  = $this->path($directory ?? new DirectoryPath('.'));
        $found = $this->fs->search($path, (array) $include, (array) $exclude, $hidden);

        return $found;
    }

    /**
     * @template T of DirectoryPath|FilePath
     *
     * @param T $path
     *
     * @return new<T>
     */
    protected function path(DirectoryPath|FilePath $path): DirectoryPath|FilePath {
        $path = match (true) {
            $path->relative => $this->directory->resolve($path),
            default         => $path->normalized(),
        };

        return $path;
    }

    /**
     * @return File<string>
     */
    protected function wrap(FileSystemFile $file): File {
        return $this->cache[$file] ??= new FileImpl($file);
    }
}
