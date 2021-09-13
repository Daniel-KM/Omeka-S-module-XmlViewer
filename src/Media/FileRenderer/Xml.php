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
            'style' => 'height: 70vh; min-height: 600px; width: 100%; border: none;',
        ],
    ];

    /**
     * Render a xml file via a xslt converter to html inside an iframe.
     *
     * @param PhpRenderer $view,
     * @param MediaRepresentation $media
     * @param array $options These options are managed for sites:
     *   - template: the partial to use.
     *   - attributes: set the attributes of the iframe as a array; the default
     *     class "xml-viewer" is added and default height and width too (style).
     *   - original: use original file.
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
                if (empty($options['attributes']['class'])) {
                    $options['attributes']['class'] = 'xml-viewer';
                }
            }
        } else {
            $template = $this->defaultOptions['template'];
            $options['attributes'] = $this->defaultOptions['attributes'];
        }

        $options['attributes']['id'] = 'xml-viewer-' . $media->id();

        $options['attributes']['src'] = empty($options['original'])
            ? $plugins->get('urlPlainTextFile')->__invoke($media)
            : $media->originalUrl();

        $mediaType = (string) $media->mediaType();
        if (strpos($options['attributes']['class'], 'xml-viewer') === false) {
            $options['attributes']['class'] = 'xml-viewer ' . $options['attributes']['class'];
        }
        if (!in_array($mediaType, ['', 'text/xml', 'application/xml'])) {
            $options['attributes']['class'] .= ' '
                . str_replace(['vnd.', '+', '.', ';'], ['', '-', '-', '-'], substr($mediaType, strpos($mediaType, '/') + 1));
        }

        $vars = [
            'resource' => $media,
            'media' => $media,
            'options' => $options,
        ];

        unset($options['template']);
        $partial = $plugins->get('partial');
        return $template !== self::PARTIAL_NAME && $view->resolver($template)
            ? $partial($template, $vars)
            : $partial(self::PARTIAL_NAME, $vars);
    }
}
