<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Image;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\Path\FilePath;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(RemoveEmpty::class)]
final class RemoveEmptyTest extends TestCase {
    public function testInvoke(): void {
        $content = <<<'MARKDOWN'
            # Header

            Text text ![image](https://example.com) text text ![`image`][image] text
            text text ![empty][empty-a] text text ![empty](<>) text text text
            text text ![empty][empty-b] text text ![empty](#) text text text
            text text ![empty][empty-c] text text ![empty](file.md) text text text
            text text ![empty][empty-d] text text ![empty](./file.md#) text text text
            text text ![fragment][fragment] text text ![fragment](#fragment) text text text
            text text [link][link] text text [link](#fragment).

            [empty-a]: <>
            [empty-b]: # (Empty link)
            [empty-c]: file.md
            [empty-d]: ./file.md#
            [link]: ./path/to/file.md
            [image]: ./#fragment
            [fragment]: #fragment

            # Special

            ## Inside Quote

            > Text text ![image](https://example.com) text text ![`image`][image] text
            > text text ![empty][empty-a] text text ![empty](<>) text text text
            > text text ![empty][empty-b] text text ![empty](#) text text text
            > text text ![empty][empty-c] text text ![empty](file.md) text text text
            > text text ![empty][empty-d] text text ![empty](./file.md#) text text text
            > text text ![fragment][fragment] text text ![fragment](#fragment) text text text
            > text text [link][link] text text [link](#fragment).

            ## Inside Table

            | Header                       |  ![Header][image]             |
            |------------------------------|-------------------------------|
            | Cell ![image][empty-a] cell. | Cell `\|` \\| ![table][image] |
            | Cell                         | Cell cell ![table][empty-a].  |
            MARKDOWN;

        $markdown = $this->app()->make(Markdown::class);
        $document = $markdown->parse($content, new FilePath('path/to/file.md'));
        $actual   = (string) $document->mutate(new RemoveEmpty());

        self::assertSame(
            <<<'MARKDOWN'
            # Header

            Text text ![image](https://example.com) text text ![`image`][image] text
            text text  text text  text text text
            text text  text text  text text text
            text text  text text  text text text
            text text  text text  text text text
            text text  text text  text text text
            text text [link][link] text text [link](#fragment).

            [empty-a]: <>
            [empty-b]: # (Empty link)
            [empty-c]: file.md
            [empty-d]: ./file.md#
            [link]: ./path/to/file.md
            [image]: ./#fragment
            [fragment]: #fragment

            # Special

            ## Inside Quote

            > Text text ![image](https://example.com) text text ![`image`][image] text
            > text text  text text  text text text
            > text text  text text  text text text
            > text text  text text  text text text
            > text text  text text  text text text
            > text text  text text  text text text
            > text text [link][link] text text [link](#fragment).

            ## Inside Table

            | Header                       |  ![Header][image]             |
            |------------------------------|-------------------------------|
            | Cell  cell. | Cell `\|` \\| ![table][image] |
            | Cell                         | Cell cell .  |

            MARKDOWN,
            $actual,
        );
    }
}
