<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQL\Printer\Filters;

use LastDragon_ru\GraphQL\Printer\Contracts\DirectiveFilter;
use LastDragon_ru\GraphQL\Printer\Contracts\TypeFilter;
use Override;

/**
 * @internal
 */
class IntrospectionFilter implements TypeFilter, DirectiveFilter {
    #[Override]
    public function isAllowedDirective(string $directive, bool $isStandard): bool {
        return true;
    }

    #[Override]
    public function isAllowedType(string $type, bool $isStandard): bool {
        return true;
    }
}
