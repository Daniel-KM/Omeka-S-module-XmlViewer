<?php declare(strict_types=1);

namespace XmlViewerTest\Controller;

use CommonTest\AbstractHttpControllerTestCase;
use XmlViewer\Controller\IndexController;

/**
 * Tests the check of the stylesheets and their insertion in the xml.
 */
class IndexControllerStylesheetTest extends AbstractHttpControllerTestCase
{
    protected IndexController $controller;

    public function setUp(): void
    {
        parent::setUp();
        $this->controller = $this->getApplication()->getServiceManager()
            ->get('ControllerManager')->get(IndexController::class);
    }

    /**
     * @dataProvider prologProvider
     */
    public function testPrologPosition(string $head, string $prolog): void
    {
        $position = $this->call('prologPosition', $head);
        $this->assertSame($prolog, substr($head, 0, $position));
    }

    public function prologProvider(): array
    {
        return [
            'declaration' => ["<?xml version=\"1.0\"?>\n<a/>", '<?xml version="1.0"?>'],
            'bom and declaration' => ["\xEF\xBB\xBF<?xml version='1.0'?><a/>", "\xEF\xBB\xBF<?xml version='1.0'?>"],
            'no declaration' => ['<a><b/></a>', ''],
            'bom only' => ["\xEF\xBB\xBF<a/>", "\xEF\xBB\xBF"],
            'stylesheet is not a declaration' => ['<?xml-stylesheet href="x.css"?><a/>', ''],
        ];
    }

    /**
     * @dataProvider renderProvider
     */
    public function testCheckRender(string $render, string $expected): void
    {
        $renderings = [
            'application/tei+xml' => 'https://cdn.example.org/tei.css|vendor/teinte/tei2html.xsl',
        ];
        $this->assertSame($expected, $this->call('checkRender', $render, $renderings));
    }

    public function renderProvider(): array
    {
        return [
            'text' => ['text', 'text'],
            'original' => ['original', 'original'],
            'stylesheet set in settings' => ['https://cdn.example.org/tei.css', 'https://cdn.example.org/tei.css'],
            'asset' => ['xsl/xml-html.xslt', 'xsl/xml-html.xslt'],
            'remote url' => ['https://example.com/x.css', ''],
            'absolute path' => ['/x.css', ''],
            'parent path' => ['../x.css', ''],
            'injection' => ['a.css"?><x/><?a b="c.css', ''],
            'mixed' => ['xsl/a.xsl|https://example.com/x.css|x.js', 'xsl/a.xsl'],
        ];
    }

    protected function call(string $method, ...$args)
    {
        return (fn () => $this->$method(...$args))->call($this->controller);
    }
}
