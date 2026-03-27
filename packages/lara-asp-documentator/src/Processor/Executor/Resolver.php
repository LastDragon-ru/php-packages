<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Executor;

use Exception;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Cast;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Container;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Resolver as Contract;
use LastDragon_ru\LaraASP\Documentator\Processor\Dispatcher;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\PathNotFound;
use LastDragon_ru\LaraASP\Documentator\Processor\Executor\File as FileImpl;
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
     * @var WeakMap<File<*>, array<class-string<Cast<mixed>>, mixed>>
     */
    private WeakMap $files;
    private Cache   $cache;

    public function __construct(
        private readonly Container $container,
        protected readonly Dispatcher $dispatcher,
        protected readonly FileSystem $fs,
        protected readonly Listener $on,
    ) {
        $this->casts = [];
        $this->files = new WeakMap();
        $this->cache = new Cache(50);
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
        // Cached?
        $path = $this->path($path);
        $file = $this->cache[$path];

        if ($file !== null) {
            return $file;
        }

        // Exists?
        if (!$this->fs->exists($path)) {
            throw new PathNotFound($path);
        }

        // Create
        $file               = new FileImpl($path, function () use ($path): string {
            return $this->fs->read($path);
        });
        $this->cache[$path] = $file;

        return $file;
    }

    #[Override]
    public function get(FilePath $path): File {
        try {
            $path = $this->path($path);
            $file = $this->file($path);

            ($this->dispatcher)(new Dependency($path, DependencyResult::Found));

            $this->on->run($path);
        } catch (Exception $exception) {
            ($this->dispatcher)(new Dependency($path, DependencyResult::NotFound));

            throw $exception;
        }

        return $file;
    }

    #[Override]
    public function find(FilePath $path): ?File {
        try {
            $path = $this->path($path);
            $file = $this->file($path);

            ($this->dispatcher)(new Dependency($path, DependencyResult::Found));

            $this->on->run($path);
        } catch (PathNotFound) {
            ($this->dispatcher)(new Dependency($path, DependencyResult::NotFound));

            $file = null;
        } catch (Exception $exception) {
            ($this->dispatcher)(new Dependency($path, DependencyResult::NotFound));

            throw $exception;
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
            $this->fs->write($path, $content);
            $this->on->save($path);
        } finally {
            if (isset($this->cache[$path])) {
                unset($this->files[$this->cache[$path]]);
            }

            if ($file !== null) {
                unset($this->files[$file]);
            }
        }
    }

    #[Override]
    public function queue(FilePath|iterable $path): void {
        $iterator = $path instanceof FilePath ? [$path] : $path;

        foreach ($iterator as $item) {
            $filepath = $this->path($item);

            ($this->dispatcher)(new Dependency($filepath, DependencyResult::Queued));

            $this->on->queue($filepath);
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

            $this->cache->delete($delete);
            $this->fs->delete($delete);
            $this->on->delete($delete);
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

    public function begin(FilePath $path): void {
        $this->fs->begin($path->directory());
    }

    public function commit(): void {
        $this->fs->commit();
        $this->cache->cleanup();
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
}
