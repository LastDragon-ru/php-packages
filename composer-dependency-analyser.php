<?php declare(strict_types = 1);

use Orchestra\Testbench\TestCase as TestbenchTestCase;
use ShipMonk\ComposerDependencyAnalyser\ComposerJson;
use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;
use ShipMonk\ComposerDependencyAnalyser\Path;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\Glob;

// It is not loaded by default, and may lead to 'Class "Symfony\Component\Finder\Finder"
// not found' error.
require_once __DIR__.'/vendor-bin/composer-dependency-analyser/vendor/autoload.php';

// General
$config = (new Configuration())
    ->disableComposerAutoloadPathScan()
    ->enableAnalysisOfUnusedDevDependencies()
    ->ignoreErrorsOnPackage('bamarni/composer-bin-plugin', [ErrorType::UNUSED_DEPENDENCY])
    ->ignoreErrorsOnPackage('laravel/scout', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnExtension('ext-pdo_sqlite', [ErrorType::UNUSED_DEPENDENCY])
    ->ignoreUnknownClasses([
        TestbenchTestCase::class,
    ]);

// Load composer.json
$composerPath  = Path::realpath(getopt('', ['composer-json:'])['composer-json'] ?? 'composer.json');
$composerJson  = new ComposerJson($composerPath);
$isRootPackage = Path::realpath(dirname(__FILE__).'/composer.json') === $composerPath;

if (!$isRootPackage) {
    $config->disableReportingUnmatchedIgnores();

    // fixme: Hotfix for https://github.com/shipmonk-rnd/composer-dependency-analyser/issues/253
    $config->ignoreErrorsOnPaths(
        [
            'packages/graphql-printer/src/Blocks/Document/Argument.php',
            'packages/graphql-printer/src/Blocks/Document/InputValueDefinition.php',
            'packages/graphql-printer/src/Blocks/Document/ValueTest.php',
            'packages/graphql-printer/src/Blocks/Document/VariableDefinition.php',
        ],
        [
            ErrorType::SHADOW_DEPENDENCY,
        ],
    );
}

// Composer paths
// Custom logic used because we want to analyze excluded from class map paths too.
foreach ($composerJson->autoloadPaths as $path => $dev) {
    $config->addPathToScan($path, $dev);
}

foreach ($composerJson->autoloadExcludeRegexes as $regex => $dev) {
    if (!str_ends_with($regex, '/docs($|/)#')) {
        // TODO: Docs paths should be treated as dev.
        continue;
    }

    $config->addPathRegexToExclude($regex);
}

// Additional/Test paths
$files = Finder::create()
    ->in(dirname($composerPath))
    ->ignoreVCSIgnored(true)
    ->ignoreDotFiles(true)
    ->exclude('node_modules')
    ->exclude('vendor-bin')
    ->exclude('vendor')
    ->exclude('dev')
    ->path(Glob::toRegex('*Test.php'))
    ->path(Glob::toRegex('*/**/*Test.php'))
    ->path(Glob::toRegex('*Test~*.php'))
    ->path(Glob::toRegex('*/**/*Test~*.php'))
    ->path(Glob::toRegex('*Test/*.php'))
    ->path(Glob::toRegex('*/**/*Test/*.php'))
    ->path(Glob::toRegex('*Test/**/*.php'))
    ->path(Glob::toRegex('*/**/*Test/**/*.php'))
    ->path(Glob::toRegex('src/Package/*.php'))
    ->path(Glob::toRegex('src/Package/**/*.php'))
    ->path(Glob::toRegex('packages/*/src/Package/*.php'))
    ->path(Glob::toRegex('packages/*/src/Package/**/*.php'))
    ->files();

foreach ($files as $file) {
    $config->addPathToScan($file->getPathname(), true);
}

// Return
return $config;
