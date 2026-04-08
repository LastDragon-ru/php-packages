<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Serializer\Metadata;

use JsonSerializable;
use LastDragon_ru\LaraASP\Serializer\Attributes\TypeMap;
use LastDragon_ru\LaraASP\Serializer\Attributes\VersionMap;
use LastDragon_ru\LaraASP\Serializer\Package\TestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Serializer\Attribute\DiscriminatorMap;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Serializer\Mapping\AttributeMetadata;
use Symfony\Component\TypeInfo\Type\BuiltinType;
use Symfony\Component\TypeInfo\Type\CollectionType;
use Symfony\Component\TypeInfo\Type\GenericType;
use Symfony\Component\TypeInfo\Type\ObjectType;
use Symfony\Component\TypeInfo\Type\UnionType;
use Symfony\Component\TypeInfo\TypeIdentifier;

use function tap;

/**
 * @internal
 */
#[CoversClass(MetadataFactory::class)]
final class MetadataFactoryTest extends TestCase {
    public function testHasMetadataFor(): void {
        $factory = new MetadataFactory();

        self::assertTrue(
            $factory->hasMetadataFor(MetadataFactoryTest_A::class), // @phpstan-ignore-line method.alreadyNarrowedType
        );
        self::assertTrue(
            $factory->hasMetadataFor(new MetadataFactoryTest_A()),  // @phpstan-ignore-line method.alreadyNarrowedType
        );
        self::assertFalse(
            $factory->hasMetadataFor(JsonSerializable::class),      // @phpstan-ignore-line method.alreadyNarrowedType
        );
        self::assertFalse(
            $factory->hasMetadataFor('UnknownClass'),               // @phpstan-ignore-line method.impossibleType
        );
    }

    public function testGetMetadataFor(): void {
        $factory = new MetadataFactory();
        $a       = $factory->getMetadataFor(MetadataFactoryTest_A::class);
        $b       = $factory->getMetadataFor(MetadataFactoryTest_B::class);
        $c       = $factory->getMetadataFor(MetadataFactoryTest_C::class);
        $d       = $factory->getMetadataFor(MetadataFactoryTest_D::class);

        self::assertEquals(
            [
                'a'        => new AttributeMetadata('a'),
                'b'        => new AttributeMetadata('b'),
                'f'        => tap(
                    new AttributeMetadata('f'),
                    static function (AttributeMetadata $metadata): void {
                        $metadata->setSerializedName('ff');
                    },
                ),
                'array'    => new AttributeMetadata('array'),
                'promoted' => new AttributeMetadata('promoted'),
                'renamed'  => tap(
                    new AttributeMetadata('renamed'),
                    static function (AttributeMetadata $metadata): void {
                        $metadata->setSerializedName('promoted-renamed');
                    },
                ),
            ],
            $b->getAttributesMetadata(),
        );
        self::assertEquals(
            [
                'property' => 'version',
                'mapping'  => [
                    'a' => MetadataFactoryTest_A::class,
                    'b' => MetadataFactoryTest_B::class,
                ],
            ],
            [
                'property' => $a->getClassDiscriminatorMapping()?->getTypeProperty(),
                'mapping'  => $a->getClassDiscriminatorMapping()?->getTypesMapping(),
            ],
        );
        self::assertEquals(
            [
                'property' => null,
                'mapping'  => null,
            ],
            [
                'property' => $b->getClassDiscriminatorMapping()?->getTypeProperty(),
                'mapping'  => $b->getClassDiscriminatorMapping()?->getTypesMapping(),
            ],
        );
        self::assertEquals(
            [
                'property' => '$v',
                'mapping'  => [
                    'a' => MetadataFactoryTest_A::class,
                    'b' => MetadataFactoryTest_B::class,
                ],
            ],
            [
                'property' => $c->getClassDiscriminatorMapping()?->getTypeProperty(),
                'mapping'  => $c->getClassDiscriminatorMapping()?->getTypesMapping(),
            ],
        );
        self::assertEquals(
            [
                'property' => '$type',
                'mapping'  => [
                    'a' => MetadataFactoryTest_A::class,
                    'b' => MetadataFactoryTest_B::class,
                ],
            ],
            [
                'property' => $d->getClassDiscriminatorMapping()?->getTypeProperty(),
                'mapping'  => $d->getClassDiscriminatorMapping()?->getTypesMapping(),
            ],
        );
    }

    public function testGetType(): void {
        $factory = new MetadataFactory();
        $class   = MetadataFactoryTest_B::class;

        self::assertEquals(
            new BuiltinType(TypeIdentifier::INT),
            $factory->getType($class, 'a'),
        );
        self::assertNull(
            $factory->getType($class, 'c'),
        );
        self::assertNull(
            $factory->getType($class, 'd'),
        );
        self::assertEquals(
            new CollectionType(
                new GenericType(
                    new BuiltinType(TypeIdentifier::ARRAY),
                    new UnionType(
                        new BuiltinType(TypeIdentifier::STRING),
                        new BuiltinType(TypeIdentifier::INT),
                    ),
                    new ObjectType(MetadataFactoryTest_A::class),
                ),
            ),
            $factory->getType($class, 'array'),
        );
        self::assertEquals(
            new CollectionType(
                new GenericType(
                    new BuiltinType(TypeIdentifier::ARRAY),
                    new BuiltinType(TypeIdentifier::INT),
                    new ObjectType(MetadataFactoryTest_B::class),
                ),
            ),
            $factory->getType($class, 'promoted'),
        );
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
#[DiscriminatorMap('version', ['b' => MetadataFactoryTest_B::class, 'a' => MetadataFactoryTest_A::class])]
class MetadataFactoryTest_A implements JsonSerializable {
    public int           $a = 123;
    public bool          $b; // @phpstan-ignore-line property.uninitialized (required for tests)
    protected string     $c = 'should be ignored';
    private string       $d = 'should be ignored';
    public static string $e = 'should be ignored';
    #[SerializedName('ff')]
    public string        $f = 'string';

    #[Override]
    public function jsonSerialize(): mixed {
        return ['d' => $this->d];
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class MetadataFactoryTest_B extends MetadataFactoryTest_A {
    /**
     * @var array<array-key, MetadataFactoryTest_A>
     * @phpstan-ignore-next-line property.uninitialized (required for tests)
     */
    public array $array;

    /**
     * @param array<int, MetadataFactoryTest_B> $promoted
     */
    public function __construct(
        public array $promoted = [],
        #[SerializedName('promoted-renamed')]
        public string $renamed = 'string',
    ) {
        // empty
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
#[VersionMap(['b' => MetadataFactoryTest_B::class, 'a' => MetadataFactoryTest_A::class])]
class MetadataFactoryTest_C {
    // empty
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
#[TypeMap(['b' => MetadataFactoryTest_B::class, 'a' => MetadataFactoryTest_A::class])]
class MetadataFactoryTest_D {
    // empty
}
