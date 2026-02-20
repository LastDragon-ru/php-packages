<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Link;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\Path\FilePath;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(UnlinkEmpty::class)]
#[CoversClass(Base::class)]
final class UnlinkEmptyTest extends TestCase {
    public function testInvoke(): void {
        $content = <<<'MARKDOWN'
            # Header

            Text text [link](https://example.com) text text [`link`][link] text
            text text [empty][empty-a] text text [empty](<>) text text text
            text text [empty][empty-b] text text [empty](#) text text text
            text text [empty][empty-c] text text [empty](file.md) text text text
            text text [empty][empty-d] text text [empty](./file.md#) text text text
            text text [fragment][fragment] text text [fragment](#fragment) text text text
            text text ![image][image] text text ![image](#fragment).

            [empty-a]: <>
            [empty-b]: #
            [empty-c]: file.md
            [empty-d]: ./file.md#
            [link]: ./path/to/file.md
            [image]: ./#fragment
            [fragment]: #fragment

            # Special

            ## Inside Quote

            > Text text [link](https://example.com) text text [`link`][link] text
            > text text [empty][empty-a] text text [empty](<>) text text text
            > text text [empty][empty-b] text text [empty](#) text text text
            > text text [empty][empty-c] text text [empty](file.md) text text text
            > text text [empty][empty-d] text text [empty](./file.md#) text text text
            > text text [fragment][fragment] text text [fragment](#fragment) text text text
            > text text ![image][image] text text ![image](#fragment).

            ## Inside Table

            | Header                     |  [Header][link]               |
            |----------------------------|-------------------------------|
            | Cell [link][empty-a] cell. | Cell `\|` \\| ![table][image] |
            | Cell                       | Cell cell [table][empty-a].   |
            MARKDOWN;

        $markdown = $this->app()->make(Markdown::class);
        $document = $markdown->parse($content, new FilePath('path/to/file.md'));
        $actual   = (string) $document->mutate(new UnlinkEmpty());

        self::assertSame(
            <<<'MARKDOWN'
            # Header

            Text text [link](https://example.com) text text [`link`][link] text
            text text empty text text empty text text text
            text text empty text text empty text text text
            text text empty text text empty text text text
            text text empty text text empty text text text
            text text [fragment][fragment] text text [fragment](#fragment) text text text
            text text ![image][image] text text ![image](#fragment).

            [empty-a]: <>
            [empty-b]: #
            [empty-c]: file.md
            [empty-d]: ./file.md#
            [link]: ./path/to/file.md
            [image]: ./#fragment
            [fragment]: #fragment

            # Special

            ## Inside Quote

            > Text text [link](https://example.com) text text [`link`][link] text
            > text text empty text text empty text text text
            > text text empty text text empty text text text
            > text text empty text text empty text text text
            > text text empty text text empty text text text
            > text text [fragment][fragment] text text [fragment](#fragment) text text text
            > text text ![image][image] text text ![image](#fragment).

            ## Inside Table

            | Header                     |  [Header][link]               |
            |----------------------------|-------------------------------|
            | Cell link cell. | Cell `\|` \\| ![table][image] |
            | Cell                       | Cell cell table.   |

            MARKDOWN,
            $actual,
        );
    }
}
