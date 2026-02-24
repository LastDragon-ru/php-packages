<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Reference;

use LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location as LocationData;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\Path\FilePath;
use League\CommonMark\Node\Node;
use PHPUnit\Framework\Attributes\CoversClass;

use function array_map;

/**
 * @internal
 */
#[CoversClass(RemoveUnused::class)]
final class UsagesTest extends TestCase {
    public function testInvoke(): void {
        $content = <<<'MARKDOWN'
            # Header

            Text text [link](https://example.com) text text [`link`][link] text
            text text ![image][image] text text.

            ![image][image]

            [link]: https://example.com
            [image]: https://example.com
            [unused]: https://example.com

            # Special

            ## Inside Quote

            > ![image][link]

            ## Inside Table

            | Header                  |  [Header][link]               |
            |-------------------------|-------------------------------|
            | Cell [link][link] cell. | Cell `\|` \\| ![table][image] |
            | Cell                    | Cell cell [table][link].      |
            MARKDOWN;

        $markdown = $this->app()->make(Markdown::class);
        $document = $markdown->parse($content, new FilePath('path/to/file.md'));
        $usages   = Usages::get($document->node);
        $refs     = $document->node->getReferenceMap();
        $map      = static fn (Node $node) => LocationData::get($node);

        // Unused
        $unused = $refs->get('unused');

        self::assertNotNull($unused);
        self::assertFalse(isset($usages[$unused]));

        // Link
        $link = $refs->get('link');

        self::assertNotNull($link);
        self::assertTrue(isset($usages[$link]));
        self::assertEquals(
            [
                new Location(3, 3, 48, 14),
                new Location(16, 16, 2, 14),
                new Location(20, 20, 29, 14),
                new Location(22, 22, 7, 12),
                new Location(23, 23, 38, 13),
            ],
            array_map($map, $usages[$link]),
        );

        // Image
        $image = $refs->get('image');

        self::assertNotNull($image);
        self::assertTrue(isset($usages[$image]));
        self::assertEquals(
            [
                new Location(4, 4, 10, 15),
                new Location(6, 6, 0, 15),
                new Location(22, 22, 42, 15),
            ],
            array_map($map, $usages[$image]),
        );
    }
}
