<?php declare(strict_types=1);

namespace XmlViewer\File;

use Laminas\EventManager\EventManagerAwareTrait;

class TempFileFactory extends \Omeka\File\TempFileFactory
{
    use EventManagerAwareTrait;

    public function build()
    {
        $tempFile = new TempFile($this->tempDir, $this->mediaTypeMap,
            $this->store, $this->thumbnailManager, $this->validator
        );
        $tempFile->setEventManager($this->getEventManager());

        $mediaTypeIdentifiers = require dirname(__DIR__, 2) . '/data/media-types/media-type-identifiers.php';
        return $tempFile
            ->setMediaTypeIdentifiers($mediaTypeIdentifiers);
    }
}
