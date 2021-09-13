<?php declare(strict_types=1);

namespace XmlViewer\File;

use Laminas\EventManager\EventManagerAwareTrait;

class TempFileFactory extends \Omeka\File\TempFileFactory
{
    use EventManagerAwareTrait;

    public function build()
    {
        // Return \XmlViewer\File\TempFile.
        $tempFile = new TempFile($this->tempDir, $this->mediaTypeMap,
            $this->store, $this->thumbnailManager, $this->validator
        );
        $tempFile->setEventManager($this->getEventManager());
        return $tempFile;
    }
}
