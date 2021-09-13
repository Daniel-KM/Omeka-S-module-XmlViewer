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
     * @param bool|null $genericUrl If null or false, use the site url, else use
     *   the generic one.
     */
    public function __invoke($resourceOrUrl, ?bool $genericUrl = null): ?string
    {
        $plugins = $this->getView()->getHelperPluginManager();
        $url = $plugins->get('url');

        $siteSlug = $genericUrl ? '' : $plugins->get('params')->fromRoute('site-slug');
        if (is_string($resourceOrUrl)) {
            return $genericUrl || !$siteSlug
                ? $url('xml', [], ['source' => $resourceOrUrl])
                : $url('site/xml', ['site-slug' => $siteSlug], ['source' => $resourceOrUrl]);
        } elseif (is_object($resourceOrUrl) && $resourceOrUrl instanceof AbstractResourceEntityRepresentation) {
            return $genericUrl || !$siteSlug
                ? $url('xml/resource-id', ['id' => $resourceOrUrl->id()])
                : $url('site/xml/resource-id', ['site-slug' => $siteSlug, 'id' => $resourceOrUrl->id()]);
        }
        return null;
    }
}
