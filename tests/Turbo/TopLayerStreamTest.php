<?php

namespace Vbq23\TopLayerBundle\Tests\Turbo;

use PHPUnit\Framework\TestCase;
use Vbq23\TopLayerBundle\Turbo\TopLayerStream;

final class TopLayerStreamTest extends TestCase
{
    public function testCloseDialog(): void
    {
        self::assertSame(
            '<turbo-stream action="close_dialog" target="modal-edit"></turbo-stream>',
            TopLayerStream::closeDialog('modal-edit'),
        );
    }

    public function testReloadFrame(): void
    {
        self::assertSame(
            '<turbo-stream action="reload_frame" target="list"></turbo-stream>',
            TopLayerStream::reloadFrame('list'),
        );
    }

    public function testRefreshFrame(): void
    {
        self::assertSame(
            '<turbo-stream action="refresh_frame" target="list" url="/employees"></turbo-stream>',
            TopLayerStream::refreshFrame('list', '/employees'),
        );
    }

    public function testRefreshFrameWithAdvance(): void
    {
        self::assertSame(
            '<turbo-stream action="refresh_frame" target="list" url="/employees" advance="true"></turbo-stream>',
            TopLayerStream::refreshFrame('list', '/employees', true),
        );
    }

    public function testRefreshFrameWithoutAdvanceHasNoAdvanceAttribute(): void
    {
        self::assertStringNotContainsString('advance', TopLayerStream::refreshFrame('list', '/employees', false));
    }

    public function testVisitParentFrame(): void
    {
        self::assertSame(
            '<turbo-stream action="visit_parent_frame" target="employee-form" url="/employees/1"></turbo-stream>',
            TopLayerStream::visitParentFrame('employee-form', '/employees/1'),
        );
    }

    public function testUrlIsEscaped(): void
    {
        self::assertSame(
            '<turbo-stream action="refresh_frame" target="list" url="/search?q=&quot;&gt;&lt;script&gt;&amp;page=2"></turbo-stream>',
            TopLayerStream::refreshFrame('list', '/search?q="><script>&page=2'),
        );
    }

    public function testSingleQuotesAreEscaped(): void
    {
        self::assertSame(
            '<turbo-stream action="visit_parent_frame" target="f" url="/search?q=O&#039;Brien"></turbo-stream>',
            TopLayerStream::visitParentFrame('f', "/search?q=O'Brien"),
        );
    }

    public function testInvalidUtf8IsReplacedInsteadOfDroppingTheValue(): void
    {
        self::assertSame(
            "<turbo-stream action=\"refresh_frame\" target=\"list\" url=\"/search?q=\u{FFFD}\"></turbo-stream>",
            TopLayerStream::refreshFrame('list', "/search?q=\xFF"),
        );
    }

    public function testTargetIsEscaped(): void
    {
        self::assertSame(
            '<turbo-stream action="close_dialog" target="&quot; onclick=&quot;alert(1)"></turbo-stream>',
            TopLayerStream::closeDialog('" onclick="alert(1)'),
        );
    }
}
