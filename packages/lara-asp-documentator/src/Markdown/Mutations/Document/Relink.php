<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document;

use Closure;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Reference as ReferenceData;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Reference\Node as ReferenceNode;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Text;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Replace;
use LastDragon_ru\LaraASP\Documentator\Markdown\Utils;
use LastDragon_ru\Path\Path;
use League\CommonMark\Extension\CommonMark\Node\Inline\AbstractWebResource;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image as ImageNode;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link as LinkNode;
use League\CommonMark\Node\Node;
use Override;

use function mb_ltrim;
use function mb_substr;
use function rawurldecode;

/**
 * Relink all paths in the document.
 *
 * Please note that links may/will be reformatted (because there is no
 * information about their original form)
 *
 * @implements Mutation<LinkNode|ImageNode|ReferenceNode>
 */
readonly class Relink implements Mutation {
    public function __construct(
        /**
         * @var Closure(string): string
         */
        protected Closure $callback,
    ) {
        // empty
    }

    #[Override]
    public static function nodes(): array {
        return [
            LinkNode::class,
            ImageNode::class,
            ReferenceNode::class,
        ];
    }

    #[Override]
    public function mutagens(Document $document, Node $node): array {
        // Skipped?
        if ($node instanceof AbstractWebResource && ReferenceData::get($node) !== null) {
            return [];
        }

        // Update?
        $url    = $node instanceof ReferenceNode ? $node->getDestination() : $node->getUrl();
        $url    = rawurldecode($url);
        $url    = $document->path !== null && Utils::isPath($url)
            ? $document->path->resolve(Path::make($url))->path
            : $url;
        $target = ($this->callback)($url);

        if ($url === $target) {
            return [];
        }

        // Update
        $location = $node instanceof ReferenceNode
            ? Utils::getReferenceDestinationLocation($document, $node)
            : Utils::getLinkDestinationLocation($document, $node);
        $origin   = (string) $document->mutate(new Text($location));
        $wrap     = mb_substr(mb_ltrim($origin), 0, 1) === '<';
        $text     = Utils::getLinkTarget($node, $target, $wrap !== false ? true : null);

        return [
            new Replace($location, $text),
        ];
    }
}
