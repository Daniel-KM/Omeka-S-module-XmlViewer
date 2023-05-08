<?php declare(strict_types=1);

namespace XmlViewer\Service\File;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use XmlViewer\File\Validator;

class ValidatorFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $settings = $services->get('Omeka\Settings');
        return new Validator(
            $settings->get('media_type_whitelist', []),
            $settings->get('extension_whitelist', []),
            $settings->get('disable_file_validation', false)
        );
    }
}
