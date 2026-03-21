<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Mixins;

use Closure;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Constraint\Constraint;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
class TestResponseMixin {
    /**
     * @return Closure(Constraint, string): TestResponse<Response>
     */
    public function assertThat(): Closure {
        return function (Constraint $constraint, string $message = ''): TestResponse {
            /** @var TestResponse<Response> $this */
            Assert::assertThatResponse($this, $constraint, $message);

            return $this;
        };
    }
}
