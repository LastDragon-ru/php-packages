<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\OutputCleanup;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Package\WithProcessor;
use LastDragon_ru\PhpUnit\Utils\TempDirectory;
use LastDragon_ru\PhpUnit\Utils\TestData;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(OutputCleanup::class)]
final class OutputCleanupTest extends TestCase {
    use WithProcessor;

    public function testInvoke(): void {
        $source = TestData::get()->directory();
        $output = new TempDirectory($source);
        $input  = $source;
        $task   = new OutputCleanup();
        $fs     = $this->getFileSystem($input, $output->path);

        self::assertDirectoryEquals($source, $output->path);

        $this->runProcessorHookTask($task, $fs);

        self::assertDirectoryEmpty($output->path);
    }

    public function testInvokeInputEqualsToOutput(): void {
        $source = TestData::get()->directory();
        $output = new TempDirectory($source);
        $input  = $output->path;
        $task   = new OutputCleanup();
        $fs     = $this->getFileSystem($input, $output->path);

        self::assertDirectoryEquals($source, $output->path);

        $this->runProcessorHookTask($task, $fs);

        self::assertDirectoryEquals($source, $output->path);
    }
}
