<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Tokenizer;

use Generator;
use LastDragon_ru\TextParser\Package;

use function array_last;
use function array_pop;
use function array_reverse;
use function implode;
use function max;
use function mb_strlen;
use function mb_strtolower;
use function preg_quote;
use function preg_split;

use const PREG_SPLIT_DELIM_CAPTURE;
use const PREG_SPLIT_NO_EMPTY;
use const PREG_SPLIT_OFFSET_CAPTURE;

/**
 * Splits iterable of strings by the separator(s) where the key is an offset of
 * the string/separator.
 */
readonly class Splitter {
    protected string $regexp;
    protected int    $longest;

    public function __construct(
        /**
         * @var list<non-empty-string|int>
         */
        protected array $separators,
        protected bool $caseSensitive = true,
        /**
         * Internal buffer size in characters (not bytes!).
         *
         * @var positive-int|null
         */
        protected ?int $buffer = null,
    ) {
        [$this->regexp, $this->longest] = $this->prepare();
    }

    /**
     * @param iterable<mixed, string> $iterable
     *
     * @return iterable<int, non-empty-string>
     */
    public function split(iterable $iterable, int $offset = 0): iterable {
        $buffer = '';
        $limit  = max($this->buffer ?? Package::BufferCharacters, 2 * $this->longest);

        foreach ($iterable as $string) {
            $buffer .= $string;

            if (mb_strlen($buffer, Package::Encoding) >= $limit) {
                $items   = $this->explode($buffer);
                $length  = 0;
                $skipped = [];

                while ($items !== [] && $length < $this->longest) {
                    $last      = array_pop($items)[0];
                    $length    = mb_strlen($last, Package::Encoding);
                    $skipped[] = $last;
                }

                yield from $this->iterator($offset, $items);

                $last   = $items !== [] ? array_last($items) : ['', 0];
                $offset = $offset + mb_strlen($last[0], Package::Encoding) + $last[1];
                $buffer = implode('', array_reverse($skipped));
            }
        }

        yield from $this->iterator($offset, $this->explode($buffer));
    }

    /**
     * @return array{non-empty-string, int}
     */
    private function prepare(): array {
        // Collect separators
        $separators = [];
        $longest    = 0;

        foreach ($this->separators as $separator) {
            $separator = (string) $separator;

            if (!$this->caseSensitive) {
                $separator = mb_strtolower($separator, Package::Encoding);
            }

            if (isset($separators[$separator])) {
                continue;
            }

            $separators[$separator] = preg_quote($separator);
            $longest                = max($longest, mb_strlen($separator, Package::Encoding));
        }

        // Regexp
        $regexp = '#('.implode('|', $separators).')#u'.($this->caseSensitive ? '' : 'i');

        // Return
        return [$regexp, $longest];
    }

    /**
     * @return list<array{non-empty-string, int<0, max>}>
     */
    private function explode(string $string): array {
        $flags = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE;
        $split = preg_split($this->regexp, $string, -1, $flags);
        $split = $split !== false ? $split : [];

        return $split;
    }

    /**
     * @param list<array{non-empty-string, int}> $items
     *
     * @return Generator<int, non-empty-string>
     */
    private function iterator(int $offset, array $items): Generator {
        foreach ($items as $item) {
            yield $offset + $item[1] => $item[0];
        }
    }
}
