<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Validation\Rules;

use Closure;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LastDragon_ru\LaraASP\Spa\Package;
use Override;
use ReflectionClass;

use function assert;
use function is_string;
use function preg_replace;

abstract class Rule implements ValidationRule {
    protected Translator $translator;

    public function __construct(Translator $translator) {
        $this->translator = $translator;
    }

    // <editor-fold desc="ValidationRule">
    // =========================================================================
    #[Override]
    public function validate(string $attribute, mixed $value, Closure $fail): void {
        if (!$this->isValid($attribute, $value)) {
            $fail($this->getMessage());
        }
    }

    protected function isValid(string $attribute, mixed $value): bool {
        return false;
    }
    // </editor-fold>

    // <editor-fold desc="Functions">
    // =========================================================================
    protected function getMessage(): string {
        $replace     = $this->getMessageReplace();
        $variants    = $this->getMessageVariants();
        $translation = (new Collection($variants))
            ->mapWithKeys(function (string $variant) use ($replace) {
                return [$variant => $this->translate($variant, $replace)];
            })
            ->first(static function (string $value, string $key): bool {
                return $key !== $value && $value !== '';
            })
            ?? $this->getMessageDefault();

        return $translation;
    }

    protected function getMessageDefault(): string {
        return $this->translate(Package::Name.'::validation.default');
    }

    /**
     * @return array<array-key, mixed>
     */
    protected function getMessageReplace(): array {
        return [];
    }

    /**
     * @return array<array-key, string>
     */
    protected function getMessageVariants(): array {
        $name    = Str::snake((new ReflectionClass($this))->getShortName());
        $name    = preg_replace('/_rule$/', '', $name);
        $package = Package::Name;

        return [
            "validation.{$package}.{$name}",  // application
            "{$package}::validation.{$name}", // package
        ];
    }

    /**
     * @param array<array-key, mixed> $replace
     */
    private function translate(string $string, array $replace = []): string {
        $translated = $this->translator->get($string, $replace);

        assert(is_string($translated));

        return $translated;
    }
    // </editor-fold>
}
