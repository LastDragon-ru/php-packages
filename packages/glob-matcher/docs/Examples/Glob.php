<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Docs\Examples;

use LastDragon_ru\GlobMatcher\Glob\Glob;
use LastDragon_ru\GlobMatcher\Glob\Options;
use LastDragon_ru\GlobMatcher\Glob\Parser\Parser;
use LastDragon_ru\LaraASP\Dev\App\Example;

$pattern = '/**/**/?.txt';
$options = new Options();
$glob    = new Glob($pattern);
$node    = (new Parser($options))->parse($pattern);

Example::dump($glob->match('/a/b/c.txt'));
Example::dump($glob->match('a.txt'));
Example::dump($node);
