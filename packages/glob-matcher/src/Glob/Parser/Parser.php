<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Parser;

use Closure;
use LastDragon_ru\GlobMatcher\Glob\Ast\Factories\CharacterNodeFactory;
use LastDragon_ru\GlobMatcher\Glob\Ast\Factories\GlobNodeFactory;
use LastDragon_ru\GlobMatcher\Glob\Ast\Factories\NameNodeFactory;
use LastDragon_ru\GlobMatcher\Glob\Ast\Factories\PatternListNodeFactory;
use LastDragon_ru\GlobMatcher\Glob\Ast\Factories\PatternNodeFactory;
use LastDragon_ru\GlobMatcher\Glob\Ast\Node;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\AsteriskNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\CharacterClass;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\CharacterClassNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\CharacterCollatingSymbolNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\CharacterEquivalenceClassNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\CharacterNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\CharacterNodeChild;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\GlobNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\GlobNodeChild;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\GlobstarNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\NameNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\NameNodeChild;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\PatternListNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\PatternListQuantifier;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\QuestionNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\SegmentNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\StringNode;
use LastDragon_ru\GlobMatcher\Glob\Options;
use LastDragon_ru\TextParser\Iterables\TransactionalIterable;
use LastDragon_ru\TextParser\Tokenizer\Token;

class Parser {
    public function __construct(
        protected Options $options,
    ) {
        // empty
    }

    public function parse(string $pattern): ?GlobNode {
        // TODO(glob-matcher): Better limits (count/max length/etc)
        // TODO(glob-matcher): Error if transaction is not finished?
        $iterable = (new Tokenizer())->tokenize([$pattern]);
        $glob     = $this->parseGlob($iterable);

        return $glob;
    }

    /**
     * @param iterable<mixed, Token<Lexeme>> $iterable
     */
    protected function parseGlob(iterable $iterable): ?GlobNode {
        $iterable = new TransactionalIterable($iterable, 1, 3);
        $factory  = new GlobNodeFactory();
        $pattern  = [];

        while ($iterable->valid()) {
            $child = $this->parseGlobChild($iterable);

            if ($child !== null) {
                $factory->push($this->parseName($pattern));
                $factory->push($child);

                $pattern = [];
            } else {
                $pattern[] = $iterable->current();

                $iterable->next();
            }
        }

        $factory->push($this->parseName($pattern));

        return $factory->create();
    }

    /**
     * @param TransactionalIterable<Token<Lexeme>> $iterable
     */
    protected function parseGlobChild(TransactionalIterable $iterable): (Node&GlobNodeChild)|null {
        return $this->parseGlobstar($iterable)
            ?? $this->parseSegment($iterable);
    }

    /**
     * @param TransactionalIterable<Token<Lexeme>> $iterable
     */
    protected function parseGlobstar(TransactionalIterable $iterable): ?GlobstarNode {
        // Enabled?
        if (!$this->options->globstar) {
            return null;
        }

        // Globstar?
        if (
            $iterable[0]?->name !== Lexeme::Asterisk
            || $iterable[1]?->name !== Lexeme::Asterisk
            || ($iterable[2] !== null && $iterable[2]->name !== Lexeme::Slash)
        ) {
            return null;
        }

        if ($iterable[-1] !== null && $iterable[-1]->name !== Lexeme::Slash) {
            return null;
        }

        // Skip **
        $iterable->next(2);

        // Skip trailing `/` if it is not the last of the stream
        if ($iterable[0]?->name === Lexeme::Slash && $iterable[1] !== null) {
            $iterable->next();
        }

        // Return
        return new GlobstarNode();
    }

    /**
     * @param TransactionalIterable<Token<Lexeme>> $iterable
     */
    protected function parseSegment(TransactionalIterable $iterable): ?SegmentNode {
        $node = null;

        if ($iterable[0]?->name === Lexeme::Slash) {
            $node = new SegmentNode();

            $iterable->next();
        }

        return $node;
    }

    /**
     * @param iterable<mixed, Token<Lexeme>> $iterable
     */
    protected function parseName(iterable $iterable): ?NameNode {
        $iterable = new TransactionalIterable($iterable, 4096, 5);
        $factory  = new NameNodeFactory();

        while ($iterable->valid()) {
            $factory->push($this->parseNameChild($iterable));
        }

        return $factory->create();
    }

    /**
     * @param TransactionalIterable<Token<Lexeme>> $iterable
     */
    protected function parseNameChild(TransactionalIterable $iterable): (Node&NameNodeChild)|null {
        return $this->parsePatternList($iterable)
            ?? $this->parseCharacter($iterable)
            ?? $this->parseAsterisk($iterable)
            ?? $this->parseQuestion($iterable)
            ?? $this->parseString($iterable);
    }

    /**
     * @param TransactionalIterable<Token<Lexeme>> $iterable
     */
    protected function parseString(TransactionalIterable $iterable): ?StringNode {
        $node = null;

        if ($iterable[0] !== null) {
            if ($iterable[0]->name !== Lexeme::Backslash && $iterable[0]->value !== '') {
                $node = new StringNode($iterable[0]->value);
            }

            $iterable->next();
        }

        return $node;
    }

    /**
     * @param TransactionalIterable<Token<Lexeme>> $iterable
     */
    protected function parseQuestion(TransactionalIterable $iterable): ?QuestionNode {
        $node = null;

        if ($iterable[0]?->name === Lexeme::Question) {
            $node = new QuestionNode();

            $iterable->next();
        }

        return $node;
    }

    /**
     * @param TransactionalIterable<Token<Lexeme>> $iterable
     */
    protected function parseAsterisk(TransactionalIterable $iterable): ?AsteriskNode {
        $node = null;

        if ($iterable[0]?->name === Lexeme::Asterisk) {
            $node = new AsteriskNode();

            $iterable->next();
        }

        return $node;
    }

    /**
     * @param TransactionalIterable<Token<Lexeme>> $iterable
     */
    protected function parseCharacter(TransactionalIterable $iterable): ?CharacterNode {
        // Match?
        if ($iterable[0]?->name !== Lexeme::LeftSquareBracket) {
            return null;
        }

        // Begin
        $iterable->begin();
        $iterable->next();

        // Negated?
        $negated = false;

        if ($iterable[0]?->name === Lexeme::ExclamationMark || $iterable[0]?->name === Lexeme::Circumflex) {
            $negated = true;

            $iterable->next();
        }

        // Parse
        $node    = null;
        $factory = new CharacterNodeFactory($negated);

        while ($iterable->valid()) {
            // End?
            if ($iterable[0]?->name === Lexeme::RightSquareBracket && !$factory->empty) {
                $node = $factory->create();

                $iterable->next();

                break;
            }

            // Child
            $factory->push(
                $this->parseCharacterClass($iterable)
                ?? $this->parseCharacterCollatingSymbol($iterable)
                ?? $this->parseCharacterCharacterEquivalenceClass($iterable)
                ?? $this->parseString($iterable),
            );
        }

        // Commit
        $iterable->end($node);

        // Return
        return $node;
    }

    /**
     * @param TransactionalIterable<Token<Lexeme>> $iterable
     */
    protected function parseCharacterClass(TransactionalIterable $iterable): ?CharacterClassNode {
        return $this->parseCharacterBrackets(
            $iterable,
            Lexeme::Colon,
            static function (string $class): ?CharacterClassNode {
                $class = CharacterClass::tryFrom($class);
                $node  = $class !== null
                    ? new CharacterClassNode($class)
                    : null;

                return $node;
            },
        );
    }

    /**
     * @param TransactionalIterable<Token<Lexeme>> $iterable
     */
    protected function parseCharacterCollatingSymbol(TransactionalIterable $iterable): ?CharacterCollatingSymbolNode {
        return $this->parseCharacterBrackets(
            $iterable,
            Lexeme::Dot,
            static function (string $symbol): CharacterCollatingSymbolNode {
                return new CharacterCollatingSymbolNode($symbol);
            },
        );
    }

    /**
     * @param TransactionalIterable<Token<Lexeme>> $iterable
     */
    protected function parseCharacterCharacterEquivalenceClass(
        TransactionalIterable $iterable,
    ): ?CharacterEquivalenceClassNode {
        return $this->parseCharacterBrackets(
            $iterable,
            Lexeme::Equal,
            static function (string $class): CharacterEquivalenceClassNode {
                return new CharacterEquivalenceClassNode($class);
            },
        );
    }

    /**
     * @template T of Node&CharacterNodeChild
     *
     * @param TransactionalIterable<Token<Lexeme>> $iterable
     * @param Closure(string): ?T                  $factory
     *
     * @return T
     */
    private function parseCharacterBrackets(
        TransactionalIterable $iterable,
        Lexeme $name,
        Closure $factory,
    ): (Node&CharacterNodeChild)|null {
        // Match?
        if (!($iterable[0]?->name === Lexeme::LeftSquareBracket && $iterable[1]?->name === $name)) {
            return null;
        }

        // Begin
        $iterable->begin();
        $iterable->next(2);

        // Join
        $string = '';

        while ($iterable->valid()) {
            if (!($iterable[0]?->name === $name && $iterable[1]?->name === Lexeme::RightSquareBracket)) {
                $string .= $iterable[0]->value ?? '';

                $iterable->next();
            } else {
                $iterable->next(2);

                break;
            }
        }

        // Cannot be empty and/or the last node in the stream
        if ($string === '' || !$iterable->valid()) {
            $iterable->rollback();

            return null;
        }

        // End
        $node = $factory($string);

        $iterable->end($node);

        // Return
        return $node;
    }

    /**
     * @param TransactionalIterable<Token<Lexeme>> $iterable
     */
    protected function parsePatternList(TransactionalIterable $iterable): ?PatternListNode {
        // Enabled?
        if (!$this->options->extended) {
            return null;
        }

        // List?
        $quantifier = match ($iterable[0]->name ?? null) {
            Lexeme::ExclamationMark => PatternListQuantifier::Not,
            Lexeme::Question        => PatternListQuantifier::ZeroOrOne,
            Lexeme::Asterisk        => PatternListQuantifier::ZeroOrMore,
            Lexeme::Plus            => PatternListQuantifier::OneOrMore,
            Lexeme::At              => PatternListQuantifier::OneOf,
            default                 => null,
        };

        if ($quantifier === null || $iterable[1]?->name !== Lexeme::LeftParenthesis) {
            return null;
        }

        // Begin
        $iterable->begin();
        $iterable->next(2);

        // Parse
        $node           = null;
        $listFactory    = new PatternListNodeFactory($quantifier);
        $patternFactory = new PatternNodeFactory();

        while ($iterable->valid()) {
            switch (true) {
                case $iterable[0]?->name === Lexeme::RightParenthesis:
                    $listFactory->push($patternFactory->create());
                    $iterable->next();

                    $node = $listFactory->create();

                    break 2;
                case $iterable[0]?->name === Lexeme::VerticalLine:
                    $listFactory->push($patternFactory->create());

                    $iterable->next();
                    break;
                default:
                    $patternFactory->push($this->parseNameChild($iterable));
                    break;
            }
        }

        // Commit
        $iterable->end($node);

        // Return
        return $node;
    }
}
