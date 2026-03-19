<?php declare(strict_types = 1);

namespace LastDragon_ru\Path;

use InvalidArgumentException;

use function pathinfo;
use function str_ends_with;

use const PATHINFO_EXTENSION;

/**
 * @extends Path<non-empty-string>
 */
final class FilePath extends Path {
    /**
     * @param non-empty-string $path
     */
    public function __construct(string $path) {
        parent::__construct($path);

        if ($this->name === '.' || $this->name === '..') {
            throw new InvalidArgumentException('Filename cannot be `.` or `..`.');
        } elseif ($this->name === '' || !str_ends_with($path, $this->name)) { // @phpstan-ignore identical.alwaysFalse
            throw new InvalidArgumentException('Filename cannot be empty.');
        } else {
            // empty
        }
    }

    /**
     * @var ?non-empty-string
     */
    public ?string $extension {
        get {
            $extension = pathinfo($this->name, PATHINFO_EXTENSION);
            $extension = $extension !== '' ? $extension : null;

            return $extension;
        }
    }
}
