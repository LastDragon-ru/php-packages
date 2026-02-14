<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeTemplate;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Package\WithPreprocess;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeTemplate\Exceptions\TemplateDataMissed;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeTemplate\Exceptions\TemplateVariablesMissed;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeTemplate\Exceptions\TemplateVariablesUnused;
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
     * @param non-empty-string           $expected
     * @param non-empty-string           $source
     * @param array<string, scalar|null> $data
     */
    #[DataProvider('dataProviderInvoke')]
    public function testInvoke(string $expected, string $source, array $data): void {
        $fs       = $this->getFileSystem(__DIR__);
        $file     = $fs->get($fs->input->file(__FILE__));
        $params   = new Parameters(TestData::get()->file($source)->path, $data);
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

    public function testInvokeNoData(): void {
        $fs       = $this->getFileSystem(__DIR__);
        $file     = $fs->get($fs->input->file(__FILE__));
        $params   = new Parameters((string) $file->path, []);
        $context  = $this->getPreprocessInstructionContext($fs, $file);
        $instance = $this->app()->make(Instruction::class);

        self::expectExceptionObject(
            new TemplateDataMissed($context, $params),
        );

        ($instance)($context, $params);
    }

    public function testInvokeVariablesUnused(): void {
        $path     = TestData::get()->file('Document.md');
        $fs       = $this->getFileSystem($path->directory());
        $file     = $fs->get($path);
        $params   = new Parameters((string) $file->path, [
            'a' => 'A',
            'b' => 'B',
            'c' => 'C',
            'd' => 'D',
        ]);
        $context  = $this->getPreprocessInstructionContext($fs, $file);
        $instance = $this->app()->make(Instruction::class);

        self::expectExceptionObject(
            new TemplateVariablesUnused($context, $params, ['c', 'd']),
        );

        ($instance)($context, $params);
    }

    public function testInvokeVariablesMissed(): void {
        $path     = TestData::get()->file('Document.md');
        $fs       = $this->getFileSystem($path->directory());
        $file     = $fs->get($path);
        $params   = new Parameters((string) $file->path, [
            'a' => 'A',
        ]);
        $context  = $this->getPreprocessInstructionContext($fs, $file);
        $instance = $this->app()->make(Instruction::class);

        self::expectExceptionObject(
            new TemplateVariablesMissed($context, $params, ['b']),
        );

        ($instance)($context, $params);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{non-empty-string, non-empty-string, array<string, scalar|null>}>
     */
    public static function dataProviderInvoke(): array {
        return [
            'txt' => ['Text~expected.txt', 'Text.txt', ['a' => 'File', 'b' => 'Variable']],
            'md'  => ['Markdown~expected.md', 'Markdown.md', ['a' => 'File', 'b' => 'Variable']],
        ];
    }
    // </editor-fold>
}
