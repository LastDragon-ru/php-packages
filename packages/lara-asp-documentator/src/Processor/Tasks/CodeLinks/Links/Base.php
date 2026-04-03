<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links;

use LastDragon_ru\LaraASP\Documentator\Composer\Package;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Resolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Formats\Php\PhpFile;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Contracts\Link;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\LinkTarget;
use LastDragon_ru\LaraASP\Documentator\Utils\PhpDoc;
use LastDragon_ru\Path\FilePath;
use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeFinder;

use function mb_ltrim;
use function mb_strrpos;
use function mb_substr;

abstract class Base implements Link {
    public function __construct(
        public readonly string $class,
    ) {
        // empty
    }

    #[Override]
    public function getTitle(): ?string {
        $title = $this->un((string) $this);
        $title = $title !== '' ? $title : null;

        return $title;
    }

    #[Override]
    public function isSimilar(Link $link): bool {
        // Self?
        if ($link === $this) {
            return false;
        }

        // Base?
        if ($link instanceof self) {
            if ($link->class === $this->class) {
                return false;
            }

            if ($this->un($link->class) === $this->un($this->class)) {
                return true;
            }
        }

        // Same title?
        if ($link->getTitle() !== null && $link->getTitle() === $this->getTitle()) {
            return true;
        }

        // Else
        return (string) $link === (string) $this;
    }

    #[Override]
    public function getSource(File $file, Package $package): array|FilePath|null {
        return $package->resolve($this->class);
    }

    #[Override]
    public function getTarget(Resolver $resolver, File $source): ?LinkTarget {
        // Class?
        $expected = mb_ltrim($this->class, '\\');
        $phpFile  = $source->as(PhpFile::class);
        $class    = (new NodeFinder())->findFirst(
            $phpFile->content->stmts,
            static function (Node $node) use ($expected): bool {
                return $node instanceof ClassLike
                    && (string) $node->namespacedName === $expected;
            },
        );

        if (!($class instanceof ClassLike)) {
            return null;
        }

        // Resolve
        $path       = $resolver->directory->relative($source->path);
        $node       = $this->getTargetNode($class);
        $deprecated = (new PhpDoc($class->getDocComment()?->getText()))->isDeprecated();
        $target     = $this->target($path, $node, $deprecated);

        // Return
        return $target;
    }

    abstract protected function getTargetNode(ClassLike $class): ?Node;

    private function target(?FilePath $path, ?Node $node, bool $deprecated): ?LinkTarget {
        if ($path === null || $node === null) {
            return null;
        }

        $comment    = $node->getDocComment();
        $endLine    = null;
        $startLine  = null;
        $deprecated = $deprecated || (new PhpDoc($comment?->getText()))->isDeprecated();

        if (!($node instanceof ClassLike)) {
            $endLine   = $node->getEndLine();
            $endLine   = $endLine >= 0 ? $endLine : null;
            $startLine = $comment?->getStartLine() ?? $node->getStartLine();
            $startLine = $startLine >= 0 ? $startLine : null;
        }

        return new LinkTarget($path, $deprecated, $startLine, $endLine);
    }

    private function un(string $class): string {
        $class    = mb_ltrim($class, '\\');
        $position = mb_strrpos($class, '\\');

        if ($position !== false) {
            $class = mb_substr($class, $position + 1);
        }

        return $class;
    }
}
