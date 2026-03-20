<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Utils;

use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Language\Printer;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Nuwave\Lighthouse\Execution\Arguments\ArgumentSet;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\Directive;

use function array_first;
use function assert;
use function is_string;

class ArgumentFactory {
    public function __construct(
        protected ArgumentSetFactory $factory,
    ) {
        // empty
    }

    public function getArgument(InputValueDefinitionNode|string $node, mixed $value): Argument {
        $arguments = $this->getArgumentSet($node, $value);
        $argument  = array_first($arguments->arguments);

        assert($argument instanceof Argument, 'Arguments cannot be empty');

        return $argument;
    }

    public function getArgumentSet(InputValueDefinitionNode|string $node, mixed $value): ArgumentSet {
        $type      = is_string($node) ? $node : Printer::doPrint($node->type);
        $input     = Parser::inputObjectTypeDefinition("input A { value: {$type} }");
        $arguments = $this->factory->wrapArgs($input, ['value' => $value]);

        return $arguments;
    }

    /**
     * @return list<string>
     */
    public static function getArgumentsNames(ArgumentSet $set): array {
        // If `@rename` directive is used, the keys will be a renamed to internal
        // names. We should not expose these internal names to the end-user.
        // We must find/use the public names instead. Fortunately, the public name can
        // be found in any directive attached to Argument.
        $names = [];

        foreach ($set->arguments as $name => $argument) {
            $directive = $argument->directives->first(static function (Directive $directive): bool {
                return $directive instanceof BaseDirective;
            });

            if ($directive instanceof BaseDirective) {
                $name = $directive->definitionNode->name->value;
            }

            $names[] = $name;
        }

        return $names;
    }
}
