<?php declare(strict_types=1);

namespace XmlViewer\File;

use Laminas\EventManager\EventManagerAwareTrait;
use XmlViewer\Mvc\Controller\Plugin\SpecifyMediaType;

class TempFileFactory extends \Omeka\File\TempFileFactory
{
    use EventManagerAwareTrait;

    /**
     * @var \XmlViewer\Mvc\Controller\Plugin\SpecifyMediaType
     */
    protected $specifyMediaType;

    public function build()
    {
        $tempFile = new TempFile($this->tempDir, $this->mediaTypeMap,
            $this->store, $this->thumbnailManager, $this->validator
        );
        $tempFile->setEventManager($this->getEventManager());
        return $tempFile
            ->setSpecifyMediaType($this->specifyMediaType);
    }

    public function setSpecifyMediaType(SpecifyMediaType $specifyMediaType): self
    {
        $this->specifyMediaType = $specifyMediaType;
        return $this;
    }
}
