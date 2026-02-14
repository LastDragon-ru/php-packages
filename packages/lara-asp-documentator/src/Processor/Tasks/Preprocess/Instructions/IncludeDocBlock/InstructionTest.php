<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocBlock;

use Closure;
use Exception;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Package\WithPreprocess;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\PhpUnit\Utils\TestData;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function mb_trim;

/**
 * @internal
 */
#[CoversClass(Instruction::class)]
final class InstructionTest extends TestCase {
    use WithPreprocess;

    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param Closure(self, Context, Parameters): Exception|non-empty-string $expected
     * @param non-empty-string                                               $file
     */
    #[DataProvider('dataProviderProcess')]
    public function testInvoke(Closure|string $expected, string $file, Parameters $params): void {
        $path     = TestData::get()->file($file);
        $fs       = $this->getFileSystem($path->directory());
        $file     = $fs->get($path);
        $context  = $this->getPreprocessInstructionContext($fs, $file);
        $instance = $this->app()->make(Instruction::class);

        if ($expected instanceof Closure) {
            self::expectExceptionObject($expected($this, $context, $params));
        } else {
            $expected = mb_trim(TestData::get()->content($expected));
        }

        $actual = ($instance)($context, $params);

        self::assertInstanceOf(Document::class, $actual);
        self::assertSame($expected, mb_trim((string) $actual));
    }
    //</editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{Closure(self, Context, Parameters): Exception|non-empty-string, non-empty-string, Parameters}>
     */
    public static function dataProviderProcess(): array {
        return [
            'default'          => [
                'ValidExpected.md',
                'Valid.php',
                new Parameters('Valid.php'),
            ],
            'with summary'     => [
                'ValidWithSummaryExpected.md',
                'Valid.php',
                new Parameters('Valid.php', summary: true),
            ],
            'only summary'     => [
                'ValidOnlySummaryExpected.md',
                'Valid.php',
                new Parameters('Valid.php', summary: true, description: false),
            ],
            'only description' => [
                'ValidOnlyDescriptionExpected.md',
                'Valid.php',
                new Parameters('Valid.php', summary: false, description: true),
            ],
            'no docblock'      => [
                'NoDocBlockExpected.md',
                'NoDocBlock.php',
                new Parameters('NoDocBlock.php'),
            ],
        ];
    }
    // </editor-fold>
}
