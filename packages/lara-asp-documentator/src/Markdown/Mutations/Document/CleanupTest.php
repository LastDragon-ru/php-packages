<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\Path\FilePath;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Cleanup::class)]
final class CleanupTest extends TestCase {
    public function testInvoke(): void {
        $content = <<<'MARKDOWN'
            Text text text[^1] text text text text [`link`][link] text
            text text ![image][image] text text [empty](<>) [link][empty]
            text text ![image][empty] text text ![image](<>).

            [^1]: footnote 1
            [^2]: Unused footnote
            [link]: https://example.com
            [image]: https://example.com
            [unused]: https://example.com (Unused reference)
            [empty]: # (Empty link)

            [reference]: https://example.com (Reference is unused, because `^footnote` is not used)
            [^footnote]: [footnote][reference]

            <!-- comment -->
            [//]: # (comment)
            MARKDOWN;

        $markdown = $this->app()->make(Markdown::class);
        $document = $markdown->parse($content, new FilePath(__FILE__));
        $actual   = $document->mutate(new Cleanup());

        self::assertSame(
            <<<'MARKDOWN'
            Text text text[^1] text text text text [`link`][link] text
            text text ![image][image] text text empty link
            text text  text text .

            [^1]: footnote 1
            [link]: https://example.com
            [image]: https://example.com
            [empty]: # (Empty link)

            [reference]: https://example.com (Reference is unused, because `^footnote` is not used)

            MARKDOWN,
            (string) $actual,
        );

        self::assertSame(
            <<<'MARKDOWN'
            Text text text[^1] text text text text [`link`][link] text
            text text ![image][image] text text empty link
            text text  text text .

            [^1]: footnote 1
            [link]: https://example.com
            [image]: https://example.com

            MARKDOWN,
            (string) $actual->mutate(new Cleanup()),
        );
    }
}
