<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Package;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document as DocumentImpl;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Reference\Node;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File as FileContract;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\Path\FilePath;
use Mockery;

/**
 * @phpstan-require-extends TestCase
 * @internal
 */
trait WithPreprocess {
    use WithProcessor;

    protected function getPreprocessInstructionContext(
        FileSystem $fs,
        File $file,
        ?Document $document = null,
        ?Node $node = null,
    ): Context {
        $file = new class($file) implements FileContract {
            public function __construct(
                public File $file,
            ) {
                // empty
            }

            public FilePath $path {
                get => $this->file->path;
            }
            public string   $name {
                get => $this->path->name;
            }
            public ?string  $extension {
                get => $this->path->extension;
            }
            public mixed    $content {
                get {
                    return $this->file->content;
                }
            }
        };

        return new Context(
            $this->getProcessorResolver($fs),
            $file,
            $document ?? Mockery::mock(DocumentImpl::class),
            $node ?? new Node(),
        );
    }
}
