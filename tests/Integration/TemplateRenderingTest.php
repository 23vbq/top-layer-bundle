<?php

namespace Vbq23\TopLayerBundle\Tests\Integration;

use Twig\Environment;

final class TemplateRenderingTest extends IntegrationTestCase
{
    public function testModalWithTitleIsLabelledByIt(): void
    {
        $html = $this->render('modal', ['title' => 'Edit user']);

        self::assertStringContainsString('aria-labelledby="edit-user-title"', $html);
        self::assertStringContainsString('<h2 id="edit-user-title" class="top-layer-title">Edit user</h2>', $html);
    }

    public function testModalWithoutTitleHasNoLabel(): void
    {
        $html = $this->render('modal');

        self::assertStringNotContainsString('aria-labelledby', $html);
        self::assertStringNotContainsString('<h2', $html);
    }

    public function testModalRendersTurboFrameWithGivenId(): void
    {
        $html = $this->render('modal');

        self::assertStringContainsString('<turbo-frame id="edit-user-frame" class="top-layer-frame">', $html);
    }

    public function testModalSizeAndFrameClassAreApplied(): void
    {
        $html = $this->render('modal', ['width' => '600px', 'height' => '70vh', 'frame_class' => 'p-4']);

        self::assertStringContainsString('style="--top-layer-width: 600px"', $html);
        self::assertStringContainsString('style="--top-layer-height: 70vh"', $html);
        self::assertStringContainsString('class="top-layer-frame p-4"', $html);
    }

    public function testDrawerRendersWithDrawerClass(): void
    {
        $html = $this->render('drawer');

        self::assertStringContainsString('class="top-layer-drawer"', $html);
        self::assertStringContainsString('<turbo-frame id="edit-user-frame" class="top-layer-frame">', $html);
    }

    public function testTemplatesUseBundleControllerIdentifier(): void
    {
        foreach (['modal', 'drawer'] as $template) {
            $html = $this->render($template);

            self::assertStringContainsString('data-23vbq--top-layer-bundle--dialog-target="dialog"', $html);
            self::assertStringContainsString('data-action="23vbq--top-layer-bundle--dialog#close"', $html);
        }
    }

    /**
     * @param array<string, mixed> $context
     */
    private function render(string $template, array $context = []): string
    {
        $twig = self::getContainer()->get('twig');
        self::assertInstanceOf(Environment::class, $twig);

        return $twig->render("@TopLayer/{$template}.html.twig", $context + [
            'id' => 'edit-user',
            'turboFrameId' => 'edit-user-frame',
        ]);
    }
}
