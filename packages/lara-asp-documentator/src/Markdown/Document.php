<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document as DocumentContract;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Lines;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutator;
use LastDragon_ru\Path\FilePath;
use League\CommonMark\Node\Block\Document as DocumentNode;
use League\CommonMark\Parser\MarkdownParserInterface;
use Override;

// todo(lara-asp-documentator): There is no way to convert AST back to Markdown yet
//      https://github.com/thephpleague/commonmark/issues/419

/**
 * @internal
 */
class Document implements DocumentContract {
    public function __construct(
        protected readonly Markdown $markdown,
        protected readonly MarkdownParserInterface $parser,
        protected readonly string $content,
        public ?FilePath $path = null,
    ) {
        // empty
    }

    // @phpstan-ignore property.uninitialized (it is lazy, so all fine)
    public private(set) DocumentNode $node {
        get => $this->node ??= $this->parser->parse($this->content);
    }

    #[Override]
    public function mutate(Mutation|iterable ...$mutations): DocumentContract {
        $mutator  = new Mutator($mutations);
        $document = $mutator->mutate($this->markdown, $this, $this->getLines());

        return $document;
    }

    /**
     * @return array<int, string>
     */
    protected function getLines(): array {
        return Lines::get($this->node);
    }

    #[Override]
    public function __toString(): string {
        return $this->content;
    }
}
