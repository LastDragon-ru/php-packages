<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Formats;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Format;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Resolver;
use Override;

/**
 * @implements Format<string, Document>
 */
class MarkdownFile implements Format {
    public function __construct(
        protected readonly Markdown $markdown,
    ) {
        // empty
    }

    #[Override]
    public function read(Resolver $resolver, File $file): mixed {
        return $this->markdown->parse($file->content, $file->path);
    }

    #[Override]
    public function write(Resolver $resolver, File $file, mixed $content): mixed {
        $content->path = $file->path;
        $string        = (string) $content;

        return $string;
    }
}
