<?php declare(strict_types=1);

namespace XmlViewer;

use Omeka\Mvc\Controller\Plugin\Messenger;
use Omeka\Stdlib\Message;

/**
 * @var Module $this
 * @var \Laminas\ServiceManager\ServiceLocatorInterface $serviceLocator
 * @var string $newVersion
 * @var string $oldVersion
 *
 * @var \Doctrine\DBAL\Connection $connection
 * @var \Doctrine\ORM\EntityManager $entityManager
 * @var \Omeka\Settings\Settings $settings
 * @var \Omeka\Api\Manager $api
 */
$services = $serviceLocator;
$connection = $services->get('Omeka\Connection');
$entityManager = $services->get('Omeka\EntityManager');
$plugins = $services->get('ControllerPluginManager');
$api = $plugins->get('api');
// $config = $services->get('Config');
$settings = $services->get('Omeka\Settings');

if (version_compare($oldVersion, '3.3.0.5', '<')) {
    $renderings = $settings->get('xmlviewer_renderings') ?: [];
    if (!isset($renderings['application/alto+xml'])) {
        $renderings['application/alto+xml'] = 'xsl/xml-alto-html.xslt';
    }
    $settings->set('xmlviewer_renderings', $renderings);

    $siteIds = $api->search('sites', [], ['initialize' => false, 'returnScalar' => 'id'])->getContent();
    /** @var \Omeka\Settings\SiteSettings $siteSettings */
    $siteSettings = $services->get('Omeka\Settings\Site');
    foreach ($siteIds as $siteId) {
        $siteSettings->setTargetId($siteId);
        $renderings = $siteSettings->get('xmlviewer_renderings') ?: [];
        if (isset($renderings['application/alto+xml'])) {
            continue;
        }
        $renderings['application/alto+xml'] = 'xsl/xml-alto-html.xslt';
        $siteSettings->set('xmlviewer_renderings', $renderings);
    }
}
