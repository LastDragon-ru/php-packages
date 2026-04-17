<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Docs\Examples;

use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\TextParser\Ast\NodeFactory;
use LastDragon_ru\TextParser\Ast\NodeString;
use Override;

// phpcs:disable PSR1.Files.SideEffects
// phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses

interface Node {
    // empty
}

readonly class ChildNode extends NodeString implements Node {
    // empty
}

class ParentNode implements Node {
    public function __construct(
        /**
         * @var list<Node>
         */
        public array $children,
    ) {
        // empty
    }
}

/**
 * @extends NodeFactory<ParentNode, ChildNode>
 */
class Factory extends NodeFactory {
    #[Override]
    protected function make(): ?object {
        return $this->children !== [] ? new ParentNode($this->children) : null;
    }
}

$factory = new Factory();

$factory->push(new ChildNode('a'));
$factory->push(new ChildNode('b'));
$factory->push(new ChildNode('c'));

Example::dump($factory->create()); // create and reset
Example::dump($factory->create()); // `null`
