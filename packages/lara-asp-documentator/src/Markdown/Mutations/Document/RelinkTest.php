<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document;

use Closure;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\Path\FilePath;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(Relink::class)]
final class RelinkTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param ?non-empty-string       $path
     * @param Closure(string): string $callback
     */
    #[DataProvider('dataProviderInvoke')]
    public function testInvoke(string $expected, ?string $path, string $content, Closure $callback): void {
        $path     = $path !== null ? new FilePath($path) : null;
        $markdown = $this->app()->make(Markdown::class);
        $document = $markdown->parse($content, $path);
        $actual   = (string) $document->mutate(new Relink($callback));

        self::assertSame($expected, $actual);
    }
    //</editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{string, ?non-empty-string, string, Closure(string): string}>
     */
    public static function dataProviderInvoke(): array {
        $callback = static function (string $url): string {
            return "relinked: {$url}";
        };
        $markdown = <<<'MARKDOWN'
            # General

            Text text [link](path/to/file.txt) text [link][relative-path] text
            text text [link](/path/to/file.txt) text [link][absolute-path] text
            text text [link](https://example.com/) text [link][url] text
            text text ![image](path/to/file.txt) text ![image][relative-path] text
            text text ![image](/path/to/file.txt) text ![image][absolute-path] text
            text text ![image](https://example.com/) text ![image][url] text
            text text.

            # Special

            ## Target escaping

            ![image](./%3Cfile%3E/%20/a)
            ![image](/%3Cfile%3E/%20/a)

            ## Title escaping

            Text ![title]( path/to/file.txt "title with ( ) and with ' '" ) text
            text ![title]( /path/to/file.txt (title with \( \) and with ' ')) text
            text ![title](https://example.com/ "title with ( ) and with ' ' and with \" \"").

            ## Inside Quote

            > Text text [link](path/to/file.txt) text [link][relative-path] text
            > text text [link](/path/to/file.txt) text [link][absolute-path] text
            > text text [link](https://example.com/) text [link][url] text
            > text text ![image](path/to/file.txt) text ![image][relative-path] text
            > text text ![image](/path/to/file.txt) text ![image][absolute-path] text
            > text text ![image](https://example.com/) text ![image][url] text
            > text text.

            ## Inside Table

            | Header                             |  Value                               |
            |------------------------------------|--------------------------------------|
            | Cell [link](path/to/file.txt).     | Cell `\|` \\| [link][relative-path]. |
            | Cell [link](/path/to/file.txt).    | Cell `\|` \\| [link][absolute-path]. |
            | Cell [link](https://example.com/). | Cell `\|` \\| [link][url].           |

            [relative-path]: path/to/file.txt "title"
            [absolute-path]: /path/to/file.txt
            [url]: https://example.com/
            MARKDOWN;

        return [
            'Without path' => [
                <<<'MARKDOWN'
                # General

                Text text [link](<relinked: path/to/file.txt>) text [link][relative-path] text
                text text [link](<relinked: /path/to/file.txt>) text [link][absolute-path] text
                text text [link](<relinked: https://example.com/>) text [link][url] text
                text text ![image](<relinked: path/to/file.txt>) text ![image][relative-path] text
                text text ![image](<relinked: /path/to/file.txt>) text ![image][absolute-path] text
                text text ![image](<relinked: https://example.com/>) text ![image][url] text
                text text.

                # Special

                ## Target escaping

                ![image](<relinked: ./\<file\>/ /a>)
                ![image](<relinked: /\<file\>/ /a>)

                ## Title escaping

                Text ![title](<relinked: path/to/file.txt> "title with ( ) and with ' '" ) text
                text ![title](<relinked: /path/to/file.txt> (title with \( \) and with ' ')) text
                text ![title](<relinked: https://example.com/> "title with ( ) and with ' ' and with \" \"").

                ## Inside Quote

                > Text text [link](<relinked: path/to/file.txt>) text [link][relative-path] text
                > text text [link](<relinked: /path/to/file.txt>) text [link][absolute-path] text
                > text text [link](<relinked: https://example.com/>) text [link][url] text
                > text text ![image](<relinked: path/to/file.txt>) text ![image][relative-path] text
                > text text ![image](<relinked: /path/to/file.txt>) text ![image][absolute-path] text
                > text text ![image](<relinked: https://example.com/>) text ![image][url] text
                > text text.

                ## Inside Table

                | Header                             |  Value                               |
                |------------------------------------|--------------------------------------|
                | Cell [link](<relinked: path/to/file.txt>).     | Cell `\|` \\| [link][relative-path]. |
                | Cell [link](<relinked: /path/to/file.txt>).    | Cell `\|` \\| [link][absolute-path]. |
                | Cell [link](<relinked: https://example.com/>). | Cell `\|` \\| [link][url].           |

                [relative-path]: <relinked: path/to/file.txt> "title"
                [absolute-path]: <relinked: /path/to/file.txt>
                [url]: <relinked: https://example.com/>

                MARKDOWN,
                null,
                $markdown,
                $callback,
            ],
            'With path'    => [
                <<<'MARKDOWN'
                # General

                Text text [link](<relinked: /path/to/file.txt>) text [link][relative-path] text
                text text [link](<relinked: /path/to/file.txt>) text [link][absolute-path] text
                text text [link](<relinked: https://example.com/>) text [link][url] text
                text text ![image](<relinked: /path/to/file.txt>) text ![image][relative-path] text
                text text ![image](<relinked: /path/to/file.txt>) text ![image][absolute-path] text
                text text ![image](<relinked: https://example.com/>) text ![image][url] text
                text text.

                # Special

                ## Target escaping

                ![image](<relinked: /\<file\>/ /a>)
                ![image](<relinked: /\<file\>/ /a>)

                ## Title escaping

                Text ![title](<relinked: /path/to/file.txt> "title with ( ) and with ' '" ) text
                text ![title](<relinked: /path/to/file.txt> (title with \( \) and with ' ')) text
                text ![title](<relinked: https://example.com/> "title with ( ) and with ' ' and with \" \"").

                ## Inside Quote

                > Text text [link](<relinked: /path/to/file.txt>) text [link][relative-path] text
                > text text [link](<relinked: /path/to/file.txt>) text [link][absolute-path] text
                > text text [link](<relinked: https://example.com/>) text [link][url] text
                > text text ![image](<relinked: /path/to/file.txt>) text ![image][relative-path] text
                > text text ![image](<relinked: /path/to/file.txt>) text ![image][absolute-path] text
                > text text ![image](<relinked: https://example.com/>) text ![image][url] text
                > text text.

                ## Inside Table

                | Header                             |  Value                               |
                |------------------------------------|--------------------------------------|
                | Cell [link](<relinked: /path/to/file.txt>).     | Cell `\|` \\| [link][relative-path]. |
                | Cell [link](<relinked: /path/to/file.txt>).    | Cell `\|` \\| [link][absolute-path]. |
                | Cell [link](<relinked: https://example.com/>). | Cell `\|` \\| [link][url].           |

                [relative-path]: <relinked: /path/to/file.txt> "title"
                [absolute-path]: <relinked: /path/to/file.txt>
                [url]: <relinked: https://example.com/>

                MARKDOWN,
                '/document.md',
                $markdown,
                $callback,
            ],
        ];
    }
    // </editor-fold>
}
