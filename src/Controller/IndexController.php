<?php declare(strict_types=1);

namespace XmlViewer\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Omeka\File\Exception\InvalidArgumentException;
use Omeka\Stdlib\Message;

class IndexController extends AbstractActionController
{
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
                'Media #%s has a no original file.', // @translate
                $id
            ));
        }

        $originalUrl = $resource->originalUrl();
        if (!$originalUrl) {
            throw new InvalidArgumentException((string) new Message(
                'Media #%s has a no original file.', // @translate
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
        $filename = $resource->source() ?: basename($filepath);
        $filesize = (int) $resource->size();

        // In order to be displayed as xml in an iframe, the content-type should be "text/plain".
        $mediaType = 'text/plain';

        $dispositionMode = 'inline';

        /** @var \Laminas\Http\PhpEnvironment\Response $response */
        $response = $this->getResponse();
        // Write headers.
        $response->getHeaders()
            ->addHeaderLine(sprintf('Content-Type: %s', $mediaType))
            ->addHeaderLine(sprintf('Content-Disposition: %s; filename="%s"', $dispositionMode, $filename))
            ->addHeaderLine(sprintf('Content-Length: %s', $filesize))
            ->addHeaderLine('Content-Transfer-Encoding', 'binary')
            // Use this to open files directly.
            ->addHeaderLine('Cache-Control: private');
        // Send headers separately to handle large files.
        $response->sendHeaders();

        // TODO Use Laminas stream response.

        // Clears all active output buffers to avoid memory overflow.
        $response->setContent('');
        while (ob_get_level()) {
            ob_end_clean();
        }
        readfile($filepath);

        // Return response to avoid default view rendering and to manage events.
        return $response;
    }
}
