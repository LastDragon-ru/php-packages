<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Utils;

use Exception;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use LastDragon_ru\PhpUnit\Exceptions\TempFileFailed;

use function chmod;
use function fclose;
use function fopen;
use function fwrite;
use function is_dir;
use function is_file;
use function is_resource;
use function stream_copy_to_stream;
use function sys_get_temp_dir;
use function unlink;

/**
 * Creates a temporary file in the system temp directory. The file will be
 * removed after the instance removal.
 */
readonly class TempFile {
    public FilePath $path;

    public function __construct(FilePath|string|null $source = null) {
        $dir    = sys_get_temp_dir();
        $path   = null;
        $target = null;

        try {
            foreach (new TempName() as $name) {
                // Exists?
                $variant = "{$dir}/{$name}";

                if (is_file($variant) || is_dir($variant)) {
                    continue;
                }

                // Open
                $target = fopen($variant, 'w');

                if ($target === false) {
                    break;
                }

                // Permission
                if (!chmod($variant, 0600)) {
                    break;
                }

                // Copy
                if ($source instanceof FilePath) {
                    $source = fopen($source->path, 'r');

                    if ($source !== false && stream_copy_to_stream($source, $target) !== false) {
                        $path = $variant;
                    }
                } elseif (fwrite($target, (string) $source) !== false) {
                    $path = $variant;
                } else {
                    $path = null;
                }

                break;
            }
        } catch (Exception $exception) {
            throw new TempFileFailed(new DirectoryPath($dir), $exception);
        } finally {
            foreach ([$source, $target] as $stream) {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
        }

        if ($path === null) {
            throw new TempFileFailed(new DirectoryPath($dir));
        }

        $this->path = new FilePath($path);
    }

    public function __destruct() {
        unlink($this->path->path);
    }
}
