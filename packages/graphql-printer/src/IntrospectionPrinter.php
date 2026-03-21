<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQL\Printer;

use GraphQL\Type\Schema;
use LastDragon_ru\GraphQL\Printer\Contracts\Settings;
use LastDragon_ru\GraphQL\Printer\Filters\IntrospectionFilter;
use LastDragon_ru\GraphQL\Printer\Misc\Context;
use LastDragon_ru\GraphQL\Printer\Misc\IntrospectionContext;
use LastDragon_ru\GraphQL\Printer\Settings\DefaultSettings;
use LastDragon_ru\GraphQL\Printer\Settings\ImmutableSettings;
use Override;

/**
 * Introspection schema printer.
 *
 * Following settings has no effects:
 * - {@see Settings::getTypeFilter()}
 * - {@see Settings::getTypeDefinitionFilter()}
 * - {@see Settings::getDirectiveFilter}
 * - {@see Settings::getDirectiveDefinitionFilter}
 * - {@see Settings::isPrintUnusedDefinitions}
 * - {@see Settings::isPrintDirectiveDefinitions}
 */
class IntrospectionPrinter extends Printer {
    #[Override]
    public function setSettings(?Settings $settings): static {
        $settings ??= new DefaultSettings();
        $filter     = new IntrospectionFilter();

        return parent::setSettings(
            ImmutableSettings::createFrom($settings)
                ->setPrintUnusedDefinitions(true)
                ->setPrintDirectiveDefinitions(true)
                ->setTypeDefinitionFilter($filter)
                ->setTypeFilter($filter)
                ->setDirectiveDefinitionFilter($filter)
                ->setDirectiveFilter($filter),
        );
    }

    #[Override]
    protected function getContext(?Schema $schema): Context {
        return new IntrospectionContext($this->getSettings(), $this->getDirectiveResolver(), $schema);
    }
}
