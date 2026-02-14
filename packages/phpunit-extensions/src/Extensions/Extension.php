<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Extensions;

use LastDragon_ru\PhpUnit\Extensions\PathComparator\Extension as PathComparatorExtension;
use LastDragon_ru\PhpUnit\Extensions\Requirements\Extension as RequirementsExtension;
use LastDragon_ru\PhpUnit\Extensions\StrictScalarComparator\Extension as StrictScalarComparatorExtension;
use Override;
use PHPUnit\Runner\Extension\Extension as PHPUnitExtension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

class Extension implements PHPUnitExtension {
    public function __construct() {
        // empty
    }

    #[Override]
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void {
        $extensions = [
            new PathComparatorExtension(),
            new RequirementsExtension(),
            new StrictScalarComparatorExtension(),
        ];

        foreach ($extensions as $extension) {
            $extension->bootstrap($configuration, $facade, $parameters);
        }
    }
}
