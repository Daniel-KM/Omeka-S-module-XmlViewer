<?php declare(strict_types=1);

namespace XmlViewerTest\Controller;

use CommonTest\AbstractHttpControllerTestCase;
use XmlViewerTest\XmlViewerTestTrait;

/**
 * Tests for the XmlViewer controller.
 *
 * The controller serves XML content rendered via XSLT/CSS in iframes.
 * Since it requires real media files, many tests check routing and error cases.
 */
class IndexControllerTest extends AbstractHttpControllerTestCase
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
     * Test that the generic xml route exists and is matched.
     */
    public function testGenericXmlRouteExists(): void
    {
        $this->dispatch('/xml/1');
        // Route should be matched even if media doesn't exist (404 from API).
        $this->assertControllerName('XmlViewer\Controller\IndexController');
        $this->assertActionName('show');
    }

    /**
     * Test that accessing a non-existent media returns 404.
     */
    public function testShowActionReturns404ForInvalidMedia(): void
    {
        $this->dispatch('/xml/999999');
        $this->assertResponseStatusCode(404);
    }

    /**
     * Test that the xml route without an id does not match (may_terminate = false).
     */
    public function testXmlRouteWithoutIdDoesNotMatch(): void
    {
        $this->dispatch('/xml');
        $this->assertResponseStatusCode(404);
    }

    /**
     * Test that the controller is publicly accessible (ACL allows null role).
     */
    public function testXmlRouteIsPubliclyAccessible(): void
    {
        $this->dispatchUnauthenticated('/xml/999999');
        // Should get 404 (media not found), not 403 (forbidden).
        $this->assertResponseStatusCode(404);
    }
}
