<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast;

use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\AsteriskNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\GlobstarNode;
use LastDragon_ru\GlobMatcher\Package\TestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Factory::class)]
final class FactoryTest extends TestCase {
    public function testCreate(): void {
        $factory = new FactoryTest__Factory();
        $factory->push(new AsteriskNode(2));
        $factory->push(new AsteriskNode(1));
        $factory->push(new GlobstarNode(1));
        $factory->push(new GlobstarNode(2));

        self::assertEquals(
            new FactoryTest__Parent([
                new AsteriskNode(3),
                new GlobstarNode(3),
            ]),
            $factory->create(),
        );
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 *
 * @implements NodeParent<Node>
 */
class FactoryTest__Parent implements NodeParent {
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
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 *
 * @extends Factory<FactoryTest__Parent, Node>
 */
class FactoryTest__Factory extends Factory {
    #[Override]
    protected function make(): ?object {
        return new FactoryTest__Parent($this->children);
    }
}
