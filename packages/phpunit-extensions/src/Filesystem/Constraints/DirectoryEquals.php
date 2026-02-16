<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Filesystem\Constraints;

use FilesystemIterator;
use LastDragon_ru\Path\DirectoryPath;
use Override;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Util\Exporter;
use SebastianBergmann\Comparator\ComparisonFailure;
use SplDoublyLinkedList;
use SplFileInfo;
use SplQueue;

use function array_diff_uassoc;
use function array_keys;
use function array_merge;
use function array_unique;
use function array_values;
use function assert;
use function file_get_contents;
use function ksort;
use function max;
use function sort;

/**
 * Compares two directories. By default, directories are equal if the list of
 * their children is the same, and files have the same content. Permissions are
 * ignored. You can override {@see self::properties()} and {@see self::equal()}
 * to customize comparison logic.
 */
class DirectoryEquals extends Constraint {
    /**
     * @var ?array{
     *      array<string, list<?array<non-empty-string, scalar|null>>>,
     *      array<string, list<?array<non-empty-string, scalar|null>>>,
     *      }
     */
    private ?array $difference = null;

    public function __construct(
        protected readonly DirectoryPath $expected,
    ) {
        // empty
    }

    #[Override]
    public function toString(): string {
        return 'equals to directory '.Exporter::export($this->expected->path);
    }

    #[Override]
    protected function fail(mixed $other, string $description, ?ComparisonFailure $comparisonFailure = null): never {
        if ($this->difference !== null && $comparisonFailure === null) {
            $comparisonFailure = new ComparisonFailure(
                $this->difference[0],
                $this->difference[1],
                Exporter::export($this->difference[0]),
                Exporter::export($this->difference[1]),
            );
        }

        parent::fail($other, $description, $comparisonFailure);
    }

    #[Override]
    protected function failureDescription(mixed $other): string {
        return match (true) {
            $other instanceof DirectoryPath => 'directory '.Exporter::export($other->path).' '.$this->toString(),
            default                         => parent::failureDescription($other),
        };
    }

    #[Override]
    public function evaluate(mixed $other, string $description = '', bool $returnResult = false): ?bool {
        $this->difference = null;

        try {
            return parent::evaluate($other, $description, $returnResult);
        } finally {
            $this->difference = null;
        }
    }

    #[Override]
    protected function matches(mixed $other): bool {
        // Directory?
        if (!($other instanceof DirectoryPath)) {
            return false;
        }

        // Same?
        if ($this->expected->equals($other)) {
            return true;
        }

        // Compare
        /** @var SplQueue<DirectoryPath> $queue */
        $queue = new SplQueue();

        $queue->setIteratorMode(SplDoublyLinkedList::IT_MODE_DELETE);
        $queue->push(new DirectoryPath('./'));

        foreach ($queue as $path) {
            // First, we are comparing the lists of children (quick)
            [$otherObjects, $otherProperties]       = $this->listing($other->resolve($path));
            [$expectedObjects, $expectedProperties] = $this->listing($this->expected->resolve($path));

            if ($expectedProperties !== $otherProperties) {
                $this->difference = $this->difference($other, $path, $expectedProperties, $otherProperties);
                break;
            }

            // And content of expected/actual file next (full)
            foreach ($expectedObjects as $filename => $object) {
                // Directory?
                if ($object->isDir()) {
                    $queue[] = $path->directory($filename);
                    continue;
                }

                // Compare file
                if (!isset($otherObjects[$filename]) || !$this->equal($object, $otherObjects[$filename])) {
                    $this->difference = $this->difference(
                        $other,
                        $path,
                        [
                            $filename => ($expectedProperties[$filename] ?? []) + ['content' => 'not'],
                        ],
                        [
                            $filename => ($otherProperties[$filename] ?? []) + ['content' => 'equal'],
                        ],
                    );

                    break 2;
                }
            }
        }

        return $this->difference === null;
    }

    /**
     * Returns properties (name, size, etc) for quick comparison.
     *
     * @return array{name: string}&array<non-empty-string, scalar|null>
     */
    protected function properties(SplFileInfo $info): array {
        return [
            'name' => $info->getFilename().($info->isDir() ? '/' : ''),
            'size' => $info->isFile() ? (int) $info->getSize() : null,
        ];
    }

    /**
     * Compares content of the files. Called only if the quick comparison
     * doesn't see the difference.
     */
    protected function equal(SplFileInfo $a, SplFileInfo $b): bool {
        $equal  = true;
        $aFile  = $a->openFile();
        $bFile  = $b->openFile();
        $buffer = 8192;

        while (!$aFile->eof() && !$bFile->eof()) {
            $aData = $aFile->fread($buffer);
            $bData = $bFile->fread($buffer);

            if ($aData === false || $bData === false || $aData !== $bData) {
                $equal = false;
                break;
            }
        }

        return $equal;
    }

    /**
     * @return array{array<string, SplFileInfo>, array<string, array<non-empty-string, scalar|null>>}
     */
    private function listing(DirectoryPath $directory): array {
        $list     = [0 => [], 1 => []];
        $iterator = new FilesystemIterator($directory->path);

        foreach ($iterator as $info) {
            assert($info instanceof SplFileInfo, 'https://github.com/phpstan/phpstan/issues/8093');

            $properties                   = $this->properties($info);
            $list[0][$properties['name']] = $info;
            $list[1][$properties['name']] = $properties;
        }

        ksort($list[1]);

        return $list;
    }

    /**
     * @param array<string, array<non-empty-string, scalar|null>> $expected
     * @param array<string, array<non-empty-string, scalar|null>> $actual
     *
     * @return array{
     *     array<string, list<?array<non-empty-string, scalar|null>>>,
     *     array<string, list<?array<non-empty-string, scalar|null>>>,
     *     }
     */
    private function difference(DirectoryPath $other, DirectoryPath $directory, array $expected, array $actual): array {
        // Only different directories/files are interested
        $expectedPresent = [];
        $actualPresent   = [];
        $keys            = array_unique(array_merge(array_keys($expected), array_keys($actual)));
        $cmp             = static fn ($a, $b) => $a === $b ? 0 : 1;

        sort($keys);

        foreach ($keys as $key) {
            if (isset($expected[$key]) && isset($actual[$key])) {
                if ($expected[$key] !== $actual[$key]) {
                    // Save
                    $expectedPresent[$key] = $expected[$key];
                    $actualPresent[$key]   = $actual[$key];

                    // Content helps a lot to find what does not match.
                    $expectedSize = $expectedPresent[$key]['size'] ?? -1;
                    $actualSize   = $actualPresent[$key]['size'] ?? -1;
                    $diff         = array_keys(array_diff_uassoc($expectedPresent[$key], $actualPresent[$key], $cmp));
                    $size         = $diff === ['content'] || $diff === ['size']
                        ? max($expectedSize, $actualSize)
                        : -1;

                    if ($size > 0 && $size < 256 * 1024 && $key !== '') {
                        $expectedPresent[$key]['content'] = file_get_contents(
                            $this->expected->resolve($directory)->file($key)->path,
                        );
                        $actualPresent[$key]['content']   = file_get_contents(
                            $other->resolve($directory)->file($key)->path,
                        );
                    }
                } else {
                    continue;
                }
            } elseif (isset($expected[$key])) {
                $expectedPresent[$key] = $expected[$key];
                $actualPresent[$key]   = null;
            } elseif (isset($actual[$key])) {
                $expectedPresent[$key] = null;
                $actualPresent[$key]   = $actual[$key];
            } else {
                // empty
            }
        }

        $path = $directory->normalized()->path;

        return [
            [$path => array_values($expectedPresent)],
            [$path => array_values($actualPresent)],
        ];
    }
}
