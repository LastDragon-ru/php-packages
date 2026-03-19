<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Contracts;

use LastDragon_ru\Path\FilePath;
use League\CommonMark\Node\Block\Document as DocumentNode;
use League\CommonMark\Node\Node;
use Stringable;

interface Document extends Stringable {
    public DocumentNode $node {
        get;
    }
    public ?FilePath    $path {
        get;
        set;
    }

    /**
     * @param Mutation<covariant Node>|iterable<mixed, Mutation<covariant Node>> ...$mutations
     */
    public function mutate(Mutation|iterable ...$mutations): self;
}
