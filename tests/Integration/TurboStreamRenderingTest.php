<?php

namespace Vbq23\TopLayerBundle\Tests\Integration;

use PHPUnit\Framework\Attributes\DataProvider;
use Twig\Environment;
use Vbq23\TopLayerBundle\Turbo\TopLayerStream;

final class TurboStreamRenderingTest extends IntegrationTestCase
{
    #[DataProvider('functions')]
    public function testTwigFunctionRendersSameMarkupAsHelper(string $source, string $expected): void
    {
        self::assertSame($expected, $this->render($source));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function functions(): iterable
    {
        yield 'close dialog' => [
            "{{ top_layer_close_dialog('modal-edit') }}",
            TopLayerStream::closeDialog('modal-edit'),
        ];
        yield 'reload frame' => [
            "{{ top_layer_reload_frame('list') }}",
            TopLayerStream::reloadFrame('list'),
        ];
        yield 'refresh frame' => [
            "{{ top_layer_refresh_frame('list', '/employees') }}",
            TopLayerStream::refreshFrame('list', '/employees'),
        ];
        yield 'refresh frame with advance' => [
            "{{ top_layer_refresh_frame('list', '/employees', true) }}",
            TopLayerStream::refreshFrame('list', '/employees', true),
        ];
        yield 'refresh frame with named advance' => [
            "{{ top_layer_refresh_frame('list', '/employees', advance: true) }}",
            TopLayerStream::refreshFrame('list', '/employees', true),
        ];
        yield 'visit parent frame' => [
            "{{ top_layer_visit_parent_frame('employee-form', '/employees/1') }}",
            TopLayerStream::visitParentFrame('employee-form', '/employees/1'),
        ];
        // is_safe must stop Twig from escaping the markup a second time
        yield 'escaped url is not double escaped' => [
            "{{ top_layer_refresh_frame('list', '/search?q=\"<x>&page=2') }}",
            TopLayerStream::refreshFrame('list', '/search?q="<x>&page=2'),
        ];
    }

    private function render(string $source): string
    {
        $twig = self::getContainer()->get('twig');
        self::assertInstanceOf(Environment::class, $twig);

        return $twig->createTemplate($source)->render();
    }
}
