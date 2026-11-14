<?php

namespace Vbq23\TopLayerBundle\Tests\Integration;

use PHPUnit\Framework\Attributes\DataProvider;
use Twig\Environment;
use Twig\Error\RuntimeError;

final class TemplateRenderingTest extends IntegrationTestCase
{
    public function testModalWithTitleIsLabelledByIt(): void
    {
        $html = $this->render('<twig:TopLayer:Modal id="edit-user" frame="edit-user-frame" title="Edit user" />');

        self::assertStringContainsString('aria-labelledby="edit-user-title"', $html);
        self::assertStringContainsString('<h2 id="edit-user-title" class="top-layer-title">Edit user</h2>', $html);
    }

    public function testModalWithoutTitleHasNoLabel(): void
    {
        $html = $this->render('<twig:TopLayer:Modal id="edit-user" frame="edit-user-frame" />');

        self::assertStringNotContainsString('aria-labelledby', $html);
        self::assertStringNotContainsString('<h2', $html);
    }

    public function testModalRendersTurboFrameWithGivenId(): void
    {
        $html = $this->render('<twig:TopLayer:Modal id="edit-user" frame="edit-user-frame" />');

        self::assertStringContainsString('<turbo-frame id="edit-user-frame" class="top-layer-frame">', $html);
    }

    public function testModalSizeAndFrameClassAreApplied(): void
    {
        $html = $this->render('<twig:TopLayer:Modal id="edit-user" frame="edit-user-frame" width="600px" height="70vh" frameClass="p-4" />');

        self::assertStringContainsString('style="--top-layer-width: 600px"', $html);
        self::assertStringContainsString('style="--top-layer-height: 70vh"', $html);
        self::assertStringContainsString('class="top-layer-frame p-4"', $html);
    }

    public function testDrawerRendersWithDrawerClass(): void
    {
        $html = $this->render('<twig:TopLayer:Drawer id="audit-log" frame="audit-log-frame" />');

        self::assertStringContainsString('class="top-layer-drawer"', $html);
        self::assertStringContainsString('<turbo-frame id="audit-log-frame" class="top-layer-frame">', $html);
    }

    public function testDrawerWidthIsApplied(): void
    {
        $html = $this->render('<twig:TopLayer:Drawer id="audit-log" frame="audit-log-frame" width="480px" />');

        self::assertStringContainsString('style="--top-layer-drawer-width: 480px"', $html);
    }

    #[DataProvider('components')]
    public function testExtraAttributesArePassedToDialog(string $component, string $class, string $widthVariable): void
    {
        $html = $this->render("<twig:TopLayer:{$component} id=\"d\" frame=\"f\" width=\"600px\" class=\"danger\" style=\"color: red;\" data-foo=\"bar\" />");

        self::assertStringContainsString("class=\"{$class} danger\"", $html);
        self::assertStringContainsString("style=\"{$widthVariable}: 600px; color: red\"", $html);
        self::assertStringContainsString('data-foo="bar"', $html);
    }

    #[DataProvider('componentNames')]
    public function testComponentUsesBundleControllerIdentifier(string $component): void
    {
        $html = $this->render("<twig:TopLayer:{$component} id=\"d\" frame=\"f\" />");

        self::assertStringContainsString('data-23vbq--top-layer-bundle--dialog-target="dialog"', $html);
        self::assertStringContainsString('data-action="23vbq--top-layer-bundle--dialog#close"', $html);
    }

    #[DataProvider('componentNames')]
    public function testCloseButtonHasDefaultLabel(string $component): void
    {
        $html = $this->render("<twig:TopLayer:{$component} id=\"d\" frame=\"f\" />");

        self::assertStringContainsString('aria-label="Close"', $html);
    }

    #[DataProvider('componentNames')]
    public function testCloseLabelCanBeChanged(string $component): void
    {
        $html = $this->render("<twig:TopLayer:{$component} id=\"d\" frame=\"f\" closeLabel=\"Zamknij\" />");

        self::assertStringContainsString('aria-label="Zamknij"', $html);
        self::assertStringNotContainsString('aria-label="Close"', $html);
    }

    #[DataProvider('componentNames')]
    public function testAriaLabelIsPassedToDialog(string $component): void
    {
        $html = $this->render("<twig:TopLayer:{$component} id=\"d\" frame=\"f\" aria-label=\"Preview\" />");

        self::assertMatchesRegularExpression('/<dialog[^>]*aria-label="Preview"/', $html);
    }

    #[DataProvider('requiredProps')]
    public function testMissingRequiredPropFails(string $component, string $attributes, string $missing): void
    {
        $this->expectException(RuntimeError::class);
        // ux-twig-component 3.x quotes the prop name: 'Prop "id" should be defined'
        $this->expectExceptionMessageMatches("/^(Prop \")?{$missing}\"? should be defined/");

        $this->render("<twig:TopLayer:{$component} {$attributes} />");
    }

    /**
     * @return iterable<string, array{string, string, string}>
     */
    public static function components(): iterable
    {
        yield 'modal' => ['Modal', 'top-layer-modal', '--top-layer-width'];
        yield 'drawer' => ['Drawer', 'top-layer-drawer', '--top-layer-drawer-width'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function componentNames(): iterable
    {
        yield 'modal' => ['Modal'];
        yield 'drawer' => ['Drawer'];
    }

    /**
     * @return iterable<string, array{string, string, string}>
     */
    public static function requiredProps(): iterable
    {
        foreach (['Modal', 'Drawer'] as $component) {
            yield "{$component} without id" => [$component, 'frame="f"', 'id'];
            yield "{$component} without frame" => [$component, 'id="d"', 'frame'];
        }
    }

    private function render(string $source): string
    {
        $twig = self::getContainer()->get('twig');
        self::assertInstanceOf(Environment::class, $twig);

        return $twig->createTemplate($source)->render();
    }
}
