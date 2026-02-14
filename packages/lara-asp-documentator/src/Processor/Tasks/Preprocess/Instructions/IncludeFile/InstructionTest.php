<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeFile;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Package\WithPreprocess;
use LastDragon_ru\PhpUnit\Utils\TestData;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function pathinfo;

use const PATHINFO_EXTENSION;

/**
 * @internal
 */
#[CoversClass(Instruction::class)]
final class InstructionTest extends TestCase {
    use WithPreprocess;

    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param non-empty-string $expected
     * @param non-empty-string $source
     */
    #[DataProvider('dataProviderInvoke')]
    public function testInvoke(string $expected, string $source): void {
        $fs       = $this->getFileSystem(__DIR__);
        $file     = $fs->get($fs->input->file(__FILE__));
        $params   = new Parameters(TestData::get()->file($source)->path);
        $context  = $this->getPreprocessInstructionContext($fs, $file);
        $instance = $this->app()->make(Instruction::class);
        $expected = TestData::get()->content($expected);
        $actual   = ($instance)($context, $params);

        if (pathinfo($source, PATHINFO_EXTENSION) === 'md') {
            self::assertInstanceOf(Document::class, $actual);
        } else {
            self::assertIsString($actual);
        }

        self::assertSame($expected, (string) $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{non-empty-string, non-empty-string}>
     */
    public static function dataProviderInvoke(): array {
        return [
            'txt' => ['Text~expected.txt', 'Text.txt'],
            'md'  => ['Markdown~expected.md', 'Markdown.md'],
        ];
    }
    // </editor-fold>
}
