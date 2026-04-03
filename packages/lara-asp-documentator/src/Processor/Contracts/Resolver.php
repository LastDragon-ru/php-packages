<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Contracts;

use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;

/**
 * Resolves task dependencies. The dependency will be processed before returning.
 *
 * Relative paths will be resolved based on {@see self::$directory}, except
 * {@see self::create()} that resolves paths based on {@see self::$output}
 */
interface Resolver {
    public DirectoryPath $input {
        get;
    }

    public DirectoryPath $output {
        get;
    }

    public DirectoryPath $directory {
        get;
    }

    /**
     * @return File<string>
     */
    public function get(FilePath $path): File;

    /**
     * @return ?File<string>
     */
    public function find(FilePath $path): ?File;

    /**
     * The file(s) will be processed after the current file (in undefined order).
     *
     * @param FilePath|iterable<mixed, FilePath> $path
     */
    public function queue(FilePath|iterable $path): void;

    /**
     * If the file exists, it will be returned.
     *
     * @return  File<string>
     */
    public function create(FilePath $path): File;

    /**
     * @param DirectoryPath|FilePath|File<*>|iterable<mixed, DirectoryPath|FilePath> $path
     */
    public function delete(DirectoryPath|FilePath|File|iterable $path): void;

    /**
     * @param list<non-empty-string>|non-empty-string $include Glob(s) to include.
     * @param list<non-empty-string>|non-empty-string $exclude Glob(s) to exclude.
     *
     * @return iterable<mixed, DirectoryPath|FilePath>
     */
    public function search(
        ?DirectoryPath $directory = null,
        array|string $include = [],
        array|string $exclude = [],
        bool $hidden = false,
    ): iterable;
}
