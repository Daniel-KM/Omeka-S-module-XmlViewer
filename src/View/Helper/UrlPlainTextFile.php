<?php declare(strict_types=1);

namespace XmlViewer\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;

class UrlPlainTextFile extends AbstractHelper
{
    /**
     * Get the url of the dynamic xml to html converter.
     *
     * @param AbstractResourceEntityRepresentation|string $resourceOrUrl
     */
    public function __invoke($resourceOrUrl): ?string
    {
        if (is_string($resourceOrUrl)) {
            return $this->getView()->url('xml', [], ['source' => $resourceOrUrl]);
        } elseif (is_object($resourceOrUrl) && $resourceOrUrl instanceof AbstractResourceEntityRepresentation) {
            return $this->getView()->url('xml/resource-id', ['id' => $resourceOrUrl->id()]);
        }
        return null;
    }
}
