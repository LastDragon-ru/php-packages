<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Formats\Php;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Format;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Resolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FormatNotWritable;
use Override;
use PhpParser\NameContext;
use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;

/**
 * @implements Format<string, Content>
 */
class PhpFile implements Format {
    public function __construct() {
        // empty
    }

    #[Override]
    public function read(Resolver $resolver, File $file): mixed {
        $parser  = (new ParserFactory())->createForNewestSupportedVersion();
        $stmts   = (array) $parser->parse($file->content);
        $context = self::context($stmts);
        $content = new Content($stmts, $context);

        return $content;
    }

    #[Override]
    public function write(Resolver $resolver, File $file, mixed $content): mixed {
        throw new FormatNotWritable($this::class, $file->path);
    }

    /**
     * @param array<array-key, Stmt> $stmts
     */
    private static function context(array $stmts): NameContext {
        $resolver  = new NameResolver();
        $traverser = new NodeTraverser();

        $traverser->addVisitor($resolver);
        $traverser->traverse($stmts);

        return $resolver->getNameContext();
    }
}
