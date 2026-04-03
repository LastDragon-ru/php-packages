<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocBlock;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document\Body;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document\Summary;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Formats\Php\Content;
use LastDragon_ru\LaraASP\Documentator\Processor\Formats\Php\PhpFile;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Instruction as InstructionContract;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Parameters as InstructionParameters;
use LastDragon_ru\LaraASP\Documentator\Utils\PhpDoc;
use LastDragon_ru\LaraASP\Documentator\Utils\PhpDocumentFactory;
use LastDragon_ru\Path\FilePath;
use Override;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeFinder;

/**
 * Includes the docblock of the first PHP class/interface/trait/enum/etc
 * from `<target>` file. Inline tags include as is except `@see`/`@link`
 * which will be replaced to FQCN (if possible). Other tags are ignored.
 *
 * @implements InstructionContract<Parameters>
 */
class Instruction implements InstructionContract {
    public function __construct(
        protected readonly PhpDocumentFactory $factory,
    ) {
        // empty
    }

    #[Override]
    public static function getName(): string {
        return 'include:docblock';
    }

    #[Override]
    public static function getPriority(): ?int {
        return null;
    }

    #[Override]
    public static function getParameters(): string {
        return Parameters::class;
    }

    #[Override]
    public function __invoke(Context $context, InstructionParameters $parameters): Document|string {
        $target   = $context->resolver->get(new FilePath($parameters->target));
        $document = $this->markdown($target->as(PhpFile::class));
        $result   = match (true) {
            $parameters->summary && $parameters->description => $document,
            $parameters->summary                             => $document?->mutate(new Summary()),
            $parameters->description                         => $document?->mutate(new Body()),
            default                                          => null,
        };

        return $result ?? '';
    }

    /**
     * @param File<Content> $file
     */
    private function markdown(File $file): ?Document {
        // Class?
        $class = (new NodeFinder())->findFirstInstanceOf($file->content->stmts, ClassLike::class);

        if ($class === null) {
            return null;
        }

        // Extract
        $comment  = new PhpDoc($class->getDocComment()?->getText());
        $markdown = ($this->factory)($comment, $file->path, $file->content->context);

        return $markdown;
    }
}
