<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Formats\Php;

use LastDragon_ru\LaraASP\Documentator\Composer\Package;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Format;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Resolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FormatNotWritable;
use Override;

/**
 * @implements Format<string, Package>
 */
class ComposerFile implements Format {
    public function __construct() {
        // empty
    }

    #[Override]
    public function read(Resolver $resolver, File $file): mixed {
        return new Package($file->as(ComposerJsonFile::class)->content);
    }

    #[Override]
    public function write(Resolver $resolver, File $file, mixed $content): mixed {
        throw new FormatNotWritable($this::class, $file->path);
    }
}
