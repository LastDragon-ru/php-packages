<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Image;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Delete;
use LastDragon_ru\LaraASP\Documentator\Markdown\Utils;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Node\Node;
use Override;

use function rawurldecode;

/**
 * Removes images with empty destination.
 *
 * @implements Mutation<Image>
 */
readonly class RemoveEmpty implements Mutation {
    public function __construct() {
        // empty
    }

    #[Override]
    public static function nodes(): array {
        return [
            Image::class,
        ];
    }

    #[Override]
    public function mutagens(Document $document, Node $node): array {
        $url = rawurldecode($node->getUrl());

        return Utils::isPathToSelf($document->path, $url) || Utils::isPathEmpty($document->path, $url)
            ? [new Delete(Location::get($node))]
            : [];
    }
}
