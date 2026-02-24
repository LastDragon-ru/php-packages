<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Reference;

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
            Text text text text [link][link] text text text ![image][link]
            text text [empty][empty-a] text text text ![image][empty-a].

            [link]: https://example.com
            [empty-a]: # (Empty destination)
            [empty-a]: <>
            MARKDOWN;

        $markdown = $this->app()->make(Markdown::class);
        $document = $markdown->parse($content, new FilePath('path/to/file.md'));
        $actual   = (string) $document->mutate(new RemoveEmpty());

        self::assertSame(
            <<<'MARKDOWN'
            Text text text text [link][link] text text text ![image][link]
            text text empty text text text .

            [link]: https://example.com

            MARKDOWN,
            $actual,
        );
    }
}
