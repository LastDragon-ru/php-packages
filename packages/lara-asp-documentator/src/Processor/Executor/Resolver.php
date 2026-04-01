<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Executor;

use Exception;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Cast;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Container;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Format;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Resolver as Contract;
use LastDragon_ru\LaraASP\Documentator\Processor\Dispatcher;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\PathNotFound;
use LastDragon_ru\LaraASP\Documentator\Processor\Executor\Files\NativeFile;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use Override;
use WeakMap;

use function array_last;
use function array_pop;

/**
 * @internal
 */
class Resolver implements Contract {
    /**
     * @var list<FilePath>
     */
    private array $level = [];
    /**
     * @var array<class-string<Cast<mixed>>, Cast<mixed>>
     */
    private array $casts;
    /**
     * @var array<class-string<Format<*, *>>, Format<*, *>>
     */
    private array $formats;
    /**
     * @var WeakMap<File<*>, array<class-string<Cast<mixed>>, mixed>>
     */
    private WeakMap $files;
    private Cache   $cache;

    public function __construct(
        private readonly Container $container,
        private readonly Dispatcher $dispatcher,
        private readonly FileSystem $fs,
        private readonly Listener $on,
    ) {
        $this->casts     = [];
        $this->files     = new WeakMap();
        $this->cache     = new Cache(50);
        $this->formats   = [];
        $this->directory = $this->input;
    }

    public DirectoryPath $input {
        get => $this->fs->input;
    }

    public DirectoryPath $output {
        get => $this->fs->output;
    }

    public protected(set) DirectoryPath $directory {
        get => $this->directory;
    }

    /**
     * @return File<string>
     */
    public function file(FilePath $path): File {
        // Cached?
        $path = $this->input($path);
        $file = $this->cache[$path];

        if ($file !== null) {
            return $file;
        }

        // Exists?
        if (!$this->fs->exists($path)) {
            throw new PathNotFound($path);
        }

        // Create
        return $this->make($path);
    }

    #[Override]
    public function get(FilePath $path): File {
        try {
            $path = $this->input($path);
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
            $path = $this->input($path);
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

    public function read(FilePath $path): string {
        return $this->fs->read($this->input($path));
    }

    public function save(FilePath $path, string $content): void {
        $path = $this->input($path);

        ($this->dispatcher)(new Dependency($path, DependencyResult::Saved));

        try {
            $this->fs->write($path, $content);
            $this->on->save($path);
        } finally {
            if (isset($this->cache[$path])) {
                unset($this->files[$this->cache[$path]]);
            }
        }
    }

    #[Override]
    public function queue(FilePath|iterable $path): void {
        $iterator = $path instanceof FilePath ? [$path] : $path;

        foreach ($iterator as $item) {
            $filepath = $this->input($item);

            ($this->dispatcher)(new Dependency($filepath, DependencyResult::Queued));

            $this->on->queue($filepath);
        }
    }

    #[Override]
    public function create(FilePath $path): File {
        return $this->make($this->output($path));
    }

    #[Override]
    public function delete(DirectoryPath|FilePath|File|iterable $path): void {
        $iterator = match (true) {
            $path instanceof FilePath || $path instanceof DirectoryPath => [$path],
            $path instanceof File                                       => [$path->path],
            default                                                     => $path,
        };

        foreach ($iterator as $delete) {
            $delete = $this->input($delete);

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
        $path  = $this->input($directory ?? new DirectoryPath('.'));
        $found = $this->fs->search($path, (array) $include, (array) $exclude, $hidden);

        return $found;
    }

    public function begin(FilePath $path): void {
        $path            = $this->input($path);
        $this->level[]   = $path;
        $this->directory = $path->directory();
    }

    public function commit(): void {
        array_pop($this->level);

        $this->directory = array_last($this->level)?->directory() ?? $this->input;

        $this->cache->cleanup();
    }

    /**
     * @template T of Format
     *
     * @param class-string<T> $format
     *
     * @return T
     */
    public function format(string $format): Format {
        // @phpstan-ignore return.type (https://github.com/phpstan/phpstan/issues/9521)
        return $this->formats[$format] ??= $this->container->make($format);
    }

    /**
     * @template T of DirectoryPath|FilePath
     *
     * @param T $path
     *
     * @return new<T>
     */
    protected function input(DirectoryPath|FilePath $path): DirectoryPath|FilePath {
        return $this->directory->resolve($path);
    }

    /**
     * @template T of DirectoryPath|FilePath
     *
     * @param T $path
     *
     * @return new<T>
     */
    protected function output(DirectoryPath|FilePath $path): DirectoryPath|FilePath {
        return $this->output->resolve($path);
    }

    /**
     * @return File<string>
     */
    private function make(FilePath $path): File {
        return $this->cache[$path] ??= new NativeFile($this, $path);
    }
}
