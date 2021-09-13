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
        'template' => self::PARTIAL_NAME,
        'attributes' => [
            'class' => 'xml-viewer',
            'allow' => 'fullscreen',
            'style' => 'height: 70vh; width: 100%; border: none;',
        ],
    ];

    /**
     * Render a xml file via a xslt converter to html inside an iframe.
     *
     * @param PhpRenderer $view,
     * @param MediaRepresentation $media
     * @param array $options These options are managed for sites:
     *   - template: the partial to use
     *   - attributes: set the attributes of the iframe as a array; the default
     *     class "xml-viewer" is added,and default height and width too (style).
     * @return string The output is the media link when the xml is not managed.
     * @see \Omeka\Media\FileRenderer\FallbackRenderer::render()
     */
    public function render(PhpRenderer $view, MediaRepresentation $media, array $options = [])
    {
        $plugins = $view->getHelperPluginManager();

        $status = $view->status();
        if ($status->isSiteRequest()) {
            $template = $options['template'] ?? $this->defaultOptions['template'];
            if (empty($options['attributes'])) {
                $options['attributes'] = $this->defaultOptions['attributes'];
            } else {
                $escapeAttr = $plugins->get('escapeHtmlAttr');
                foreach ($options['attributes'] as $name => $value) {
                    if ($value === false || preg_match('/[^\w:.-]/', $name)) {
                        unset($options['attributes'][$name]);
                    } elseif ($value === true) {
                        $options['attributes'][$name] = $name;
                    } else {
                        $options['attributes'][$name] = $escapeAttr($value);
                    }
                }
            }
        } else {
            $template = $this->defaultOptions['template'];
            $options['attributes'] = $this->defaultOptions['attributes'];
        }

        $options['attributes']['id'] = 'xml-viewer-' . $media->id();

        $options['attributes']['src'] = empty($options['native'])
            ? $plugins->get('urlPlainTextFile')->__invoke($media)
            : $media->originalUrl();

        $vars = [
            'resource' => $media,
            'media' => $media,
            'options' => $options,
        ];

        // Check the support of the media: Omeka uses the extension when media
        // type is not supported, but the extension is not the real type, in
        // particular for xml.
        // Normally already checked in controller, that returns fallback too.
        $mediaType = $media->mediaType();
        $allowed = require dirname(__DIR__, 3) . '/data/media-types/media-type-xml.php';
        if (!in_array($mediaType, $allowed)) {
            $template = 'common/xml-fallback';
        }

        unset($options['template']);
        $partial = $plugins->get('partial');
        return $template !== self::PARTIAL_NAME && $view->resolver($template)
            ? $partial($template, $vars)
            : $partial(self::PARTIAL_NAME, $vars);
    }
}
