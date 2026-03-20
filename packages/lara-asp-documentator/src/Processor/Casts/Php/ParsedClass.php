<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Casts\Php;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Utils\PhpDoc;
use LastDragon_ru\LaraASP\Documentator\Utils\PhpDocumentFactory;
use PhpParser\Node\Stmt\ClassLike;

class ParsedClass {
    public function __construct(
        protected readonly PhpDocumentFactory $factory,
        public readonly ParsedFile $file,
        public readonly ClassLike $node,
    ) {
        // empty
    }

    // @phpstan-ignore property.uninitialized (it is lazy, so all fine)
    public private(set) PhpDoc $comment {
        get => $this->comment ??= new PhpDoc($this->node->getDocComment()?->getText());
    }

    // @phpstan-ignore property.uninitialized (it is lazy, so all fine)
    public private(set) Document $markdown {
        get => $this->markdown ??= ($this->factory)($this->comment, $this->file->path, $this->file->context);
    }
}
