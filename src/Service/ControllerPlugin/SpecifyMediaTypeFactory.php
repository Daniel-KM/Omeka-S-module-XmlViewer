<?php declare(strict_types=1);

namespace XmlViewer\Service\ControllerPlugin;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use XmlViewer\Mvc\Controller\Plugin\SpecifyMediaType;

class SpecifyMediaTypeFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $config = $services->get('Config');
        $mediaTypesIdentifiers = require_once dirname(__DIR__, 3) . '/data/media-types/media-type-identifiers.php';
        return new SpecifyMediaType(
            $services->get('Omeka\Logger'),
            $services->get('Omeka\Connection'),
            $config['file_store']['local']['base_path'] ?: (OMEKA_PATH . '/files'),
            $mediaTypesIdentifiers
        );
    }
}
