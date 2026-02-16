<?php declare(strict_types=1);

namespace XmlViewerTest;

use CommonTest\AbstractHttpControllerTestCase;

/**
 * Tests for XmlViewer module configuration and registration.
 */
class ConfigTest extends AbstractHttpControllerTestCase
{
    use XmlViewerTestTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->loginAdmin();
    }

    /**
     * Test that the module is active.
     */
    public function testModuleIsActive(): void
    {
        $services = $this->getServiceLocator();
        $moduleManager = $services->get('Omeka\ModuleManager');
        $module = $moduleManager->getModule('XmlViewer');
        $this->assertNotNull($module);
        $this->assertEquals('active', $module->getState());
    }

    /**
     * Test that XSLTProcessor extension is available.
     */
    public function testXsltProcessorIsAvailable(): void
    {
        $this->assertTrue(
            class_exists('XSLTProcessor', false),
            'PHP XSLTProcessor extension is required.'
        );
    }

    /**
     * Test that the IndexController is registered.
     */
    public function testControllerIsRegistered(): void
    {
        $services = $this->getServiceLocator();
        $controllers = $services->get('ControllerManager');
        $this->assertTrue($controllers->has('XmlViewer\Controller\IndexController'));
    }

    /**
     * Test that the urlPlainTextFile view helper is registered.
     */
    public function testViewHelperIsRegistered(): void
    {
        $services = $this->getServiceLocator();
        $helpers = $services->get('ViewHelperManager');
        $this->assertTrue($helpers->has('urlPlainTextFile'));
    }

    /**
     * Test that the settings fieldset is registered.
     */
    public function testSettingsFieldsetIsRegistered(): void
    {
        $services = $this->getServiceLocator();
        $formElements = $services->get('FormElementManager');
        $this->assertTrue($formElements->has('XmlViewer\Form\SettingsFieldset'));
    }

    /**
     * Test that the site settings fieldset is registered.
     */
    public function testSiteSettingsFieldsetIsRegistered(): void
    {
        $services = $this->getServiceLocator();
        $formElements = $services->get('FormElementManager');
        $this->assertTrue($formElements->has('XmlViewer\Form\SiteSettingsFieldset'));
    }

    /**
     * Test default rendering settings.
     */
    public function testDefaultRenderingSettings(): void
    {
        $config = $this->getServiceLocator()->get('Config');
        $settings = $config['xmlviewer']['settings']['xmlviewer_renderings'];

        $this->assertArrayHasKey('text/xml', $settings);
        $this->assertArrayHasKey('application/xml', $settings);
        $this->assertArrayHasKey('application/alto+xml', $settings);

        $this->assertEquals('xsl/xml-html.xslt', $settings['text/xml']);
        $this->assertEquals('xsl/xml-html.xslt', $settings['application/xml']);
        $this->assertEquals('xsl/xml-alto-html.xslt', $settings['application/alto+xml']);
    }

    /**
     * Test default site rendering settings match global settings.
     */
    public function testDefaultSiteRenderingSettings(): void
    {
        $config = $this->getServiceLocator()->get('Config');
        $globalSettings = $config['xmlviewer']['settings']['xmlviewer_renderings'];
        $siteSettings = $config['xmlviewer']['site_settings']['xmlviewer_renderings'];

        $this->assertEquals($globalSettings, $siteSettings);
    }

    /**
     * Test that generic xml route is configured.
     */
    public function testGenericXmlRouteIsConfigured(): void
    {
        $config = $this->getServiceLocator()->get('Config');
        $routes = $config['router']['routes'];

        $this->assertArrayHasKey('xml', $routes);
        $this->assertEquals('/xml', $routes['xml']['options']['route']);
    }

    /**
     * Test that site xml route is configured as a child route.
     */
    public function testSiteXmlRouteIsConfigured(): void
    {
        $config = $this->getServiceLocator()->get('Config');
        $routes = $config['router']['routes'];

        $this->assertArrayHasKey('site', $routes);
        $childRoutes = $routes['site']['child_routes'];
        $this->assertArrayHasKey('xml', $childRoutes);
        $this->assertEquals('/xml', $childRoutes['xml']['options']['route']);
    }

    /**
     * Test that media-type identifiers data file exists and is valid.
     */
    public function testMediaTypeIdentifiersDataFile(): void
    {
        $path = dirname(__DIR__, 2) . '/data/media-types/media-type-identifiers.php';
        $this->assertFileExists($path);

        $identifiers = require $path;
        $this->assertIsArray($identifiers);
        $this->assertNotEmpty($identifiers);

        // Check some key entries.
        $this->assertArrayHasKey('application/xml', $identifiers);
        $this->assertArrayHasKey('text/xml', $identifiers);
        $this->assertArrayHasKey('http://www.tei-c.org/ns/1.0', $identifiers);
        $this->assertEquals('application/tei+xml', $identifiers['http://www.tei-c.org/ns/1.0']);
    }

    /**
     * Test that media-type extensions data file exists and is valid.
     */
    public function testMediaTypeExtensionsDataFile(): void
    {
        $path = dirname(__DIR__, 2) . '/data/media-types/media-type-extensions.php';
        $this->assertFileExists($path);

        $extensions = require $path;
        $this->assertIsArray($extensions);
        $this->assertNotEmpty($extensions);

        // Check some key extensions.
        $this->assertContains('xml', $extensions);
        $this->assertContains('tei', $extensions);
        $this->assertContains('alto', $extensions);
        $this->assertContains('mets', $extensions);
        $this->assertContains('svg', $extensions);
    }

    /**
     * Test that the XSLT stylesheet files exist.
     */
    public function testXsltStylesheetsExist(): void
    {
        $modulePath = dirname(__DIR__, 2);

        $this->assertFileExists($modulePath . '/asset/xsl/xml-html.xslt');
        $this->assertFileExists($modulePath . '/asset/xsl/xml-alto-html.xslt');
    }

    /**
     * Test that view templates exist.
     */
    public function testViewTemplatesExist(): void
    {
        $modulePath = dirname(__DIR__, 2);

        $this->assertFileExists($modulePath . '/view/common/xml.phtml');
        $this->assertFileExists($modulePath . '/view/common/xml-fallback.phtml');
    }

    /**
     * Test that the settings fieldset initializes correctly.
     */
    public function testSettingsFieldsetInit(): void
    {
        $services = $this->getServiceLocator();
        $formElements = $services->get('FormElementManager');
        $fieldset = $formElements->get('XmlViewer\Form\SettingsFieldset');
        $this->assertTrue($fieldset->has('xmlviewer_renderings'));

        $element = $fieldset->get('xmlviewer_renderings');
        $this->assertInstanceOf(\Omeka\Form\Element\ArrayTextarea::class, $element);
    }

    /**
     * Test that the site settings fieldset extends the main settings fieldset.
     */
    public function testSiteSettingsFieldsetExtendsSettings(): void
    {
        $services = $this->getServiceLocator();
        $formElements = $services->get('FormElementManager');
        $fieldset = $formElements->get('XmlViewer\Form\SiteSettingsFieldset');
        $this->assertInstanceOf(
            \XmlViewer\Form\SettingsFieldset::class,
            $fieldset
        );
    }

    /**
     * Test that media-type identifiers map alto namespaces correctly.
     */
    public function testAltoNamespaceMappings(): void
    {
        $identifiers = require dirname(__DIR__, 2) . '/data/media-types/media-type-identifiers.php';

        $altoNamespaces = [
            'http://bibnum.bnf.fr/ns/alto_prod',
            'http://www.loc.gov/standards/alto/ns-v2#',
            'http://www.loc.gov/standards/alto/ns-v3#',
            'http://www.loc.gov/standards/alto/ns-v4#',
            'alto',
        ];

        foreach ($altoNamespaces as $ns) {
            $this->assertArrayHasKey($ns, $identifiers, "Alto namespace '$ns' should be mapped.");
            $this->assertEquals('application/alto+xml', $identifiers[$ns]);
        }
    }
}
