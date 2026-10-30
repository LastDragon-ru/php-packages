<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Converters;

use Exception;
use LastDragon_ru\GlobMatcher\Glob\Ast\Cursor;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\AsteriskNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\CharacterClassNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\CharacterCollatingSymbolNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\CharacterEquivalenceClassNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\CharacterNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\GlobNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\GlobstarNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\NameNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\PatternListNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\PatternListQuantifier;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\PatternNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\QuestionNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\SegmentNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\StringNode;
use LastDragon_ru\TextParser\Iterables\TransformIterable;
use LastDragon_ru\TextParser\Utils;

use function count;

class CursorToString {
    public function __construct() {
        // empty
    }

    /**
     * @param Cursor<*> $cursor
     */
    public function __invoke(Cursor $cursor): string {
        $string = '';

        switch (true) {
            case $cursor->node instanceof AsteriskNode:
                $string = '*';
                break;
            case $cursor->node instanceof CharacterClassNode:
                $string = "[:{$cursor->node->class->value}:]";
                break;
            case $cursor->node instanceof CharacterCollatingSymbolNode:
                $string = "[.{$cursor->node->symbol}.]";
                break;
            case $cursor->node instanceof CharacterEquivalenceClassNode:
                $string = "[={$cursor->node->class}=]";
                break;
            case $cursor->node instanceof CharacterNode:
                $negated = $cursor->node->negated ? '!' : '';
                $string  = "[{$negated}{$this->children($cursor)}]";
                break;
            case $cursor->node instanceof GlobNode:
                $string = $this->children($cursor);
                break;
            case $cursor->node instanceof GlobstarNode:
                $string = '**'.($cursor->next === null || $cursor->next->node instanceof SegmentNode ? '' : '/');
                break;
            case $cursor->node instanceof NameNode:
                $string = $this->children($cursor);
                break;
            case $cursor->node instanceof PatternListNode:
                if (count($cursor->node->children) > 0) {
                    $string = $this->children($cursor, '|');
                    $string = match ($cursor->node->quantifier) {
                        PatternListQuantifier::ZeroOrOne  => "?({$string})",
                        PatternListQuantifier::ZeroOrMore => "*({$string})",
                        PatternListQuantifier::OneOrMore  => "+({$string})",
                        PatternListQuantifier::OneOf      => "@({$string})",
                        PatternListQuantifier::Not        => "!({$string})",
                    };
                }
                break;
            case $cursor->node instanceof PatternNode:
                $string = $this->children($cursor);
                break;
            case $cursor->node instanceof QuestionNode:
                $string = '?';
                break;
            case $cursor->node instanceof SegmentNode:
                $string = '/';
                break;
            case $cursor->node instanceof StringNode:
                $string = $cursor->node->string;
                break;
            default:
                throw new Exception('not implemented');
        }

        return $string;
    }

    /**
     * @param Cursor<*> $cursor
     */
    private function children(Cursor $cursor, string $separator = ''): string {
        $children = new TransformIterable($cursor->children, fn ($child) => $this($child));
        $children = Utils::toString($children, $separator);

        return $children;
    }
}
