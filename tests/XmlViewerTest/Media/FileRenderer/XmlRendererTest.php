<?php declare(strict_types=1);

namespace XmlViewerTest\Media\FileRenderer;

use CommonTest\AbstractHttpControllerTestCase;
use XmlViewer\Media\FileRenderer\Xml;
use XmlViewerTest\XmlViewerTestTrait;

/**
 * Tests for the XML file renderer.
 */
class XmlRendererTest extends AbstractHttpControllerTestCase
{
    use XmlViewerTestTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->loginAdmin();
    }

    public function tearDown(): void
    {
        $this->cleanupResources();
        parent::tearDown();
    }

    /**
     * Test that the XML renderer is registered in the service manager.
     */
    public function testXmlRendererIsRegistered(): void
    {
        $services = $this->getServiceLocator();
        $renderers = $services->get('Omeka\Media\FileRenderer\Manager');
        $this->assertTrue($renderers->has('xml'));
    }

    /**
     * Test that the XML renderer returns an instance of the correct class.
     */
    public function testXmlRendererInstance(): void
    {
        $services = $this->getServiceLocator();
        $renderers = $services->get('Omeka\Media\FileRenderer\Manager');
        $renderer = $renderers->get('xml');
        $this->assertInstanceOf(Xml::class, $renderer);
    }

    /**
     * Test default options of the renderer.
     */
    public function testRendererDefaultOptions(): void
    {
        $renderer = new Xml();
        $reflection = new \ReflectionClass($renderer);
        $property = $reflection->getProperty('defaultOptions');
        $property->setAccessible(true);
        $defaults = $property->getValue($renderer);

        $this->assertArrayHasKey('template', $defaults);
        $this->assertEquals('common/xml', $defaults['template']);

        $this->assertArrayHasKey('attributes', $defaults);
        $this->assertEquals('xml-viewer', $defaults['attributes']['class']);
        $this->assertEquals('fullscreen', $defaults['attributes']['allow']);

        $this->assertArrayHasKey('url', $defaults);
        $this->assertFalse($defaults['url']['original']);
        $this->assertFalse($defaults['url']['generic']);
        $this->assertNull($defaults['url']['render']);
        $this->assertFalse($defaults['url']['force_canonical']);
    }

    /**
     * Test partial name constant.
     */
    public function testPartialNameConstant(): void
    {
        $this->assertEquals('common/xml', Xml::PARTIAL_NAME);
    }
}
