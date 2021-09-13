<?php declare(strict_types=1);

namespace XmlViewer\Media\FileRenderer;

use Laminas\View\Renderer\PhpRenderer;
use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Media\FileRenderer\RendererInterface;

class Xml implements RendererInterface
{
    /**
     * The default partial view script.
     */
    const PARTIAL_NAME = 'common/xml';

    /**
     * @var array
     */
    protected $defaultOptions = [
        'attributes' => 'class="xml-viewer" allowfullscreen="allowfullscreen" style="height: 70vh; width: 100%" frameborder="0"',
        'template' => self::PARTIAL_NAME,
    ];

    /**
     * Render a xml file via a xslt converter to html inside an iframe.
     *
     * @param PhpRenderer $view,
     * @param MediaRepresentation $media
     * @param array $options These options are managed for sites:
     *   - template: the partial to use
     *   - attributes: set the attributes of the iframe as a string; the class
     *   should contain "xml-viewer" and a height should be set.
     * @return string
     */
    public function render(PhpRenderer $view, MediaRepresentation $media, array $options = [])
    {
        $status = $view->status();
        if ($status->isSiteRequest()) {
            $template = $options['template'] ?? $this->defaultOptions['template'];
            $options['attributes'] = $options['attributes'] ?? $this->defaultOptions['attributes'];
        } else {
            $template = $this->defaultOptions['template'];
            $options['attributes'] = $this->defaultOptions['attributes'];
        }

        $vars = [
            'resource' => $media,
            'options' => $options,
        ];

        unset($options['template']);
        return $template !== self::PARTIAL_NAME && $view->resolver($template)
            ? $view->partial($template, $vars)
            : $view->partial(self::PARTIAL_NAME, $vars);
    }
}
