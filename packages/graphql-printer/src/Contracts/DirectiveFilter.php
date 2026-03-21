<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQL\Printer\Contracts;

interface DirectiveFilter {
    public function isAllowedDirective(string $directive, bool $isStandard): bool;
}
