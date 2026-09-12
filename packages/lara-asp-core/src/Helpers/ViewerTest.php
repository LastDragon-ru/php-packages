<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Helpers;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View as ViewContract;
use InvalidArgumentException;
use LastDragon_ru\LaraASP\Core\Package\TestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles;

use function sprintf;

/**
 * @internal
 */
#[CoversClass(Viewer::class)]
#[DisableReturnValueGenerationForTestDoubles]
final class ViewerTest extends TestCase {
    public function testGet(): void {
        $view    = 'view';
        $data    = ['a' => 123];
        $package = 'package';
        $factory = self::createMock(ViewFactory::class);
        $factory
            ->expects(self::once())
            ->method('make')
            ->with("{$package}::{$view}", $data)
            ->willReturn(
                self::createStub(ViewContract::class),
            );
        $factory
            ->expects(self::once())
            ->method('exists')
            ->with("{$package}::{$view}")
            ->willReturn(
                true,
            );

        $viewer = new class($factory, $package) extends Viewer {
            public function __construct(
                ViewFactory $factory,
                private readonly string $package,
            ) {
                parent::__construct($factory);
            }

            #[Override]
            protected function getName(): string {
                return $this->package;
            }
        };

        $viewer->get($view, $data);
    }

    public function testGetNoView(): void {
        $view    = 'view';
        $data    = ['a' => 123];
        $package = 'package';
        $factory = self::createMock(ViewFactory::class);
        $factory
            ->expects(self::never())
            ->method('make');
        $factory
            ->expects(self::once())
            ->method('exists')
            ->with("{$package}::{$view}")
            ->willReturn(false);

        $viewer = new class($factory, $package) extends Viewer {
            public function __construct(
                ViewFactory $factory,
                private readonly string $package,
            ) {
                parent::__construct($factory);
            }

            #[Override]
            protected function getName(): string {
                return $this->package;
            }
        };

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage(sprintf('View `%s` not found.', "{$package}::{$view}"));

        $viewer->get($view, $data);
    }

    public function testRender(): void {
        $view     = 'view';
        $data     = ['a' => 123];
        $package  = 'package';
        $content  = 'content';
        $template = self::createMock(ViewContract::class);
        $template
            ->expects(self::once())
            ->method('render')
            ->willReturn($content);

        $factory = self::createMock(ViewFactory::class);
        $factory
            ->expects(self::once())
            ->method('make')
            ->with("{$package}::{$view}", $data)
            ->willReturn($template);
        $factory
            ->expects(self::once())
            ->method('exists')
            ->with("{$package}::{$view}")
            ->willReturn(true);

        $viewer = new class($factory, $package) extends Viewer {
            public function __construct(
                ViewFactory $factory,
                private readonly string $package,
            ) {
                parent::__construct($factory);
            }

            #[Override]
            protected function getName(): string {
                return $this->package;
            }
        };

        self::assertSame($content, $viewer->render($view, $data));
    }
}
