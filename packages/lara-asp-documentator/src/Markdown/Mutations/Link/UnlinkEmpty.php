<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Link;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Utils;
use League\CommonMark\Node\Node;
use Override;

use function rawurldecode;

/**
 * Removes links with empty destination.
 */
readonly class UnlinkEmpty extends Unlink {
    #[Override]
    public function mutagens(Document $document, Node $node): array {
        return Utils::isPathEmpty($document->path, rawurldecode($node->getUrl()))
            ? parent::mutagens($document, $node)
            : [];
    }
}
