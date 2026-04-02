<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Formats;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Format;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Resolver;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer;
use Override;

/**
 * @template TObject of object
 *
 * @implements Format<string, TObject>
 */
abstract class SerializedFile implements Format {
    protected function __construct(
        protected readonly Serializer $serializer,
        /**
         * @var class-string<TObject>
         */
        protected readonly string $class,
    ) {
        // empty
    }

    #[Override]
    public function read(Resolver $resolver, File $file): mixed {
        return $this->serializer->deserialize($this->class, $file->content, $file->extension);
    }

    #[Override]
    public function write(Resolver $resolver, File $file, mixed $content): mixed {
        return $this->serializer->serialize($content, $file->extension);
    }
}
