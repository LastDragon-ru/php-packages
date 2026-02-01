<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Xml\Matchers;

use DOMDocument;
use Override;
use SplFileInfo;

/**
 * @deprecated 10.1.0
 * @internal
 */
class DomDocumentRelaxNgSchemaMatcher extends DomDocumentSchemaMatcher {
    #[Override]
    protected function isMatchesSchemaValidate(SplFileInfo $schema, DOMDocument $document): bool {
        return @$document->relaxNGValidate($schema->getPathname());
    }
}
