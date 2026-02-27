<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Serializer\Normalizers;

use LastDragon_ru\Path\Path;
use Override;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use function get_debug_type;
use function is_a;
use function is_string;
use function sprintf;

/**
 * Normalizes/Denormalizes an {@see Path}
 */
class PathNormalizer implements NormalizerInterface, DenormalizerInterface {
    public function __construct() {
        // empty
    }

    /**
     * @return array<class-string, bool>
     */
    #[Override]
    public function getSupportedTypes(?string $format): array {
        return [
            Path::class => self::class === static::class,
        ];
    }

    /**
     * @param array<array-key, mixed> $context
     */
    #[Override]
    public function normalize(mixed $object, ?string $format = null, array $context = []): string {
        if (!($object instanceof Path)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The `$object` expected to be a `%s`, `%s` given.',
                    Path::class,
                    get_debug_type($object),
                ),
            );
        }

        return (string) $object->normalized();
    }

    /**
     * @param array<array-key, mixed> $context
     */
    #[Override]
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool {
        return $data instanceof Path;
    }

    /**
     * @param array<array-key, mixed> $context
     */
    #[Override]
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed {
        // Just for the case
        if (!is_string($data)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The `$data` expected to be a `%s`, `%s` given.',
                    'string',
                    get_debug_type($data),
                ),
            );
        }

        if (!is_a($type, Path::class, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The `$type` expected to be a `%s`, `%s` given.',
                    Path::class,
                    $type,
                ),
            );
        }

        // Denormalize
        $result = Path::make($data);

        if (!($result instanceof $type)) {
            throw new UnexpectedValueException(
                sprintf(
                    'The `%s` cannot be parsed to `%s`.',
                    $data,
                    $type,
                ),
            );
        }

        return $result;
    }

    /**
     * @param array<array-key, mixed> $context
     */
    #[Override]
    public function supportsDenormalization(
        mixed $data,
        string $type,
        ?string $format = null,
        array $context = [],
    ): bool {
        return is_a($type, Path::class, true);
    }
}
