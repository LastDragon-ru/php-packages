<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Docs\Examples;

use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\TextParser\Docs\Calculator\Ast\Cursor;
use LastDragon_ru\TextParser\Docs\Calculator\Ast\ExpressionNode;
use LastDragon_ru\TextParser\Docs\Calculator\Parser;

use function assert;

// Parse
$ast = (new Parser())->parse('2 - (1 + 2) / 3');

assert($ast instanceof ExpressionNode);

// Create the cursor
$cursor = new Cursor($ast);

// Children can be iterated via $children axis
foreach ($cursor->children as $child) {
    Example::dump($child->node);
    break;
}

// Also possible to get n-th child
Example::dump($cursor->children->get(2));

// And next/previous
Example::dump($cursor->children->get(2)->next->node ?? null);
Example::dump($cursor->children->get(2)->previous->node ?? null);
