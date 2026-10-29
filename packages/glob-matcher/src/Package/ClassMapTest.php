<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Package;

use LastDragon_ru\GlobMatcher\Package\ClassMapTest\A;
use LastDragon_ru\GlobMatcher\Package\ClassMapTest\A\AA;
use LastDragon_ru\GlobMatcher\Package\ClassMapTest\I;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(ClassMap::class)]
final class ClassMapTest extends TestCase {
    public function testGet(): void {
        self::assertEqualsCanonicalizing(
            [
                A::class,
                AA::class,
            ],
            ClassMap::get(I::class),
        );
    }
}
