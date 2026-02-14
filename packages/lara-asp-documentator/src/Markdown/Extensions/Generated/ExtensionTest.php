<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Generated;

use LastDragon_ru\LaraASP\Documentator\Markdown\Environment\Markdown;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Package\WithMarkdown;
use LastDragon_ru\PhpUnit\Utils\TestData;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Extension::class)]
#[CoversClass(ParserStart::class)]
#[CoversClass(ParserContinue::class)]
final class ExtensionTest extends TestCase {
    use WithMarkdown;

    public function testParse(): void {
        $markdown = new class() extends Markdown {
            #[Override]
            protected function extensions(): array {
                return [
                    new Extension(),
                ];
            }
        };

        $document = $markdown->parse(TestData::get()->content('Document.md'));

        $this->assertMarkdownDocumentEquals(
            TestData::get()->content('Document.xml'),
            $document,
        );
    }
}
