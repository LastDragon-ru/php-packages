<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Docs\Examples;

use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\TextParser\Docs\Calculator\Lexeme;
use LastDragon_ru\TextParser\Tokenizer\Tokenizer;

use function iterator_to_array;

$input     = '2 - (1 + 2) / 3';
$tokenizer = new Tokenizer(Lexeme::class);
$tokens    = $tokenizer->tokenize([$input]);

Example::dump(iterator_to_array($tokens));
