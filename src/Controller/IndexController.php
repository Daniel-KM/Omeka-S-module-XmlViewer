<?php declare(strict_types=1);

namespace XmlViewer\Controller;

use Common\Stdlib\TextTransliterator;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Omeka\File\Exception\InvalidArgumentException;
use Omeka\Stdlib\Message;

class IndexController extends AbstractActionController
{
    const TEXT_RENDERINGS = ['text', 'plain', 'text/plain', 'true'];

    /**
     * @var string
     */
    protected $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
    }

    public function indexAction()
    {
        return $this->showAction();
    }

    public function showAction()
    {
        $id = $this->params('id');
        // Not found is automatically thrown with "read".
        /** @var \Omeka\Api\Representation\MediaRepresentation $resource */
        $resource = $this->api()->read('media', ['id' => $id])->getContent();

        if ($resource->renderer() !== 'file') {
            throw new InvalidArgumentException((string) new Message(
                'Media #%s is not a file.', // @translate
                $id
            ));
        }

        if (!$resource->hasOriginal()) {
            throw new InvalidArgumentException((string) new Message(
                'Media #%s has no original file.', // @translate
                $id
            ));
        }

        $originalUrl = $resource->originalUrl();
        if (!$originalUrl) {
            throw new InvalidArgumentException((string) new Message(
                'Media #%s has no original file url.', // @translate
                $id
            ));
        }

        $filepath = $resource->filename();
        $filepath = sprintf('%s/original/%s', $this->basePath, $filepath);
        if (!is_readable($filepath)) {
            throw new InvalidArgumentException((string) new Message(
                'Media #%s has no readable file.', // @translate
                $resource->id()
            ));
        }

        $siteSlug = $this->params('site-slug');
        $renderings = $siteSlug
           ? $this->siteSettings()->get('xmlviewer_renderings', [])
           : $this->settings()->get('xmlviewer_renderings', []);
        $mediaType = (string) $resource->mediaType();
        $rendering = trim((string) ($renderings[$mediaType] ?? ''));
        $filesize = (int) filesize($filepath);

        $render = $this->params()->fromQuery('render');
        if (is_array($render)) {
            $render = implode('|', array_filter($render, 'is_string'));
        }
        $render = is_string($render) ? trim($render) : '';

        if (!$filesize
            || !$mediaType
            // For security, there should be a default rendering even when a
            // render is set in the query.
            || in_array($rendering, ['', 'false', 'no'], true)
            || in_array($render, ['false', 'no'], true)
        ) {
            $view = new ViewModel([
                'resource' => $resource,
                'media' => $resource,
                'options' => [],
            ]);
            return $view
                ->setTemplate('common/xml-fallback')
                ->setTerminal(true);
        }

        if ($render !== '') {
            $rendering = $this->checkRender($render, $renderings) ?: $rendering;
        }

        // In order to be displayed as xml in an iframe, the content-type should
        // be "text/plain". "text/html" skips the xml instructions. "text/xml" or
        // "application/xml" are silently read like html, even if it works in a
        // full page, except with a stylesheet xsl.

        $filename = $resource->source() ?: basename($filepath);

        $contentMediaType = 'text/plain';
        $piStylesheet = '';
        if ($rendering === 'original') {
            // The media type is forced because some browsers on some operating
            // systems force downloading for precise xml media-types.
            // "text/xml" and "application/xml" are synonymous for browsers.
            $contentMediaType = 'application/xml';
        } elseif (!in_array($rendering, self::TEXT_RENDERINGS, true)) {
            $assetUrl = $this->viewHelpers()->get('assetUrl');
            foreach ($this->stylesheets($rendering) as $stylesheet) {
                $extension = strtolower(pathinfo($stylesheet, PATHINFO_EXTENSION));
                if (!$this->isUrl($stylesheet)) {
                    $stylesheet = $assetUrl($stylesheet, 'XmlViewer', true);
                }
                $piStylesheet .= sprintf(
                    "\n" . '<?xml-stylesheet type="text/%s" href="%s" ?>',
                    $extension === 'css' ? 'css' : 'xsl',
                    htmlspecialchars($stylesheet, ENT_QUOTES | ENT_XML1)
                );
            }
            if ($piStylesheet) {
                $contentMediaType = 'application/xml';
            }
        }

        // Without stylesheet, the file is sent as is.
        if (!$piStylesheet) {
            return $this->sendFile($filepath, [
                'content_type' => $contentMediaType,
                'filename' => $filename,
                'cache' => 3600,
            ]);
        }

        $filename = strtr($filename, ["\r" => '', "\n" => '', "\0" => '', '"' => '']);
        $filenameAscii = TextTransliterator::toAsciiFilename($filename);

        /** @var \Laminas\Http\PhpEnvironment\Response $response */
        $response = $this->getResponse();
        $response->getHeaders()
            ->addHeaderLine(sprintf('Content-Type: %s', $contentMediaType))
            ->addHeaderLine($filenameAscii === $filename
                ? sprintf('Content-Disposition: inline; filename="%s"', $filename)
                : sprintf("Content-Disposition: inline; filename=\"%s\"; filename*=UTF-8''%s", $filenameAscii, rawurlencode($filename)))
            ->addHeaderLine(sprintf('Content-Length: %s', $filesize + strlen($piStylesheet)))
            ->addHeaderLine('Cache-Control: private');
        // Send headers separately to handle large files.
        $response->sendHeaders();

        // Clears all active output buffers to avoid memory overflow.
        $response->setContent('');
        while (ob_get_level()) {
            ob_end_clean();
        }

        // The stylesheet should be inside the prolog, so after the xml
        // declaration if any. The file is streamed to avoid memory issues.
        $handle = fopen($filepath, 'rb');
        $head = (string) fread($handle, 8192);
        $position = $this->prologPosition($head);
        echo substr($head, 0, $position), $piStylesheet, substr($head, $position);
        fpassthru($handle);
        fclose($handle);

        // Return response to avoid default view rendering and to manage events.
        return $response;
    }

    /**
     * Keep only the safe renderings set in the query.
     *
     * A query can use any rendering set in settings, but only local assets as
     * stylesheet, to avoid injection of remote stylesheets.
     */
    protected function checkRender(string $render, array $renderings): string
    {
        if (in_array($render, self::TEXT_RENDERINGS, true) || $render === 'original') {
            return $render;
        }
        $allowed = [];
        foreach ($renderings as $rendering) {
            $allowed = array_merge($allowed, $this->stylesheets((string) $rendering));
        }
        $stylesheets = array_filter(
            $this->stylesheets($render),
            fn ($stylesheet) => in_array($stylesheet, $allowed, true)
                || preg_match('~^[\w-]+(?:[/.][\w-]+)*\.(?:css|xslt?)$~', $stylesheet)
        );
        return implode('|', $stylesheets);
    }

    /**
     * Get the list of stylesheets (css, xsl, xslt) of a rendering.
     */
    protected function stylesheets(string $rendering): array
    {
        return array_values(array_filter(
            array_map('trim', explode('|', $rendering)),
            fn ($stylesheet) => in_array(strtolower(pathinfo($stylesheet, PATHINFO_EXTENSION)), ['css', 'xsl', 'xslt'], true)
        ));
    }

    protected function isUrl(string $stylesheet): bool
    {
        return substr($stylesheet, 0, 1) === '/'
            || substr($stylesheet, 0, 8) === 'https://'
            || substr($stylesheet, 0, 7) === 'http://';
    }

    /**
     * Get the position where a processing instruction can be inserted.
     *
     * The position is after the byte order mark and the xml declaration if any.
     */
    protected function prologPosition(string $head): int
    {
        $start = strncmp($head, "\xEF\xBB\xBF", 3) === 0 ? 3 : 0;
        if (!preg_match('~\G<\?xml\s~', $head, $matches, 0, $start)) {
            return $start;
        }
        $end = strpos($head, '?>', $start);
        return $end === false ? $start : $end + 2;
    }
}
