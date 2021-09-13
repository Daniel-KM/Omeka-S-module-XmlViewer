<?php declare(strict_types=1);

namespace XmlViewer\File;

use XMLReader;

class TempFile extends \Omeka\File\TempFile
{
    /**
     * @var array
     */
    protected $mediaTypeIdentifiers = [];

    public function setMediaTypeIdentifiers(array $mediaTypeIdentifiers): \Omeka\File\TempFile
    {
        $this->mediaTypeIdentifiers = $mediaTypeIdentifiers;
        return $this;
    }

    public function getMediaType()
    {
        if (isset($this->mediaType)) {
            return $this->mediaType;
        }

        parent::getMediaType();

        if ($this->mediaType === 'text/xml' || $this->mediaType === 'application/xml') {
            $this->mediaType = $this->getMediaTypeXml() ?: $this->mediaType;
        }
        if ($this->mediaType === 'application/zip') {
            $this->mediaType = $this->getMediaTypeZip() ?: $this->mediaType;
        }
        return $this->mediaType;
    }

    /**
     * Extract a more precise xml media type when possible.
     *
     * @return string
     */
    protected function getMediaTypeXml()
    {
        $filepath = $this->getTempPath();

        libxml_clear_errors();

        $reader = new XMLReader();
        if (!$reader->open($filepath)) {
            // TODO The logger is not available.
            return null;
        }

        $type = null;

        // Don't output error in case of a badly formatted file since there is no logger.
        while (@$reader->read()) {
            if ($reader->nodeType === XMLReader::DOC_TYPE) {
                $type = $reader->name;
                break;
            }

            // To be improved or skipped.
            if ($reader->nodeType === XMLReader::PI
                && !in_array($reader->name, [
                    'xml-model',
                    'xml-stylesheet',
                    'oxygen',
                ])
            ) {
                $matches = [];
                if (preg_match('~href="(.+?)"~mi', $reader->value, $matches)) {
                    $type = $matches[1];
                    break;
                }
            }

            if ($reader->nodeType === XMLReader::ELEMENT) {
                if ($reader->namespaceURI === 'urn:oasis:names:tc:opendocument:xmlns:office:1.0') {
                    $type = $reader->getAttributeNs('mimetype', 'urn:oasis:names:tc:opendocument:xmlns:office:1.0');
                } else {
                    $type = $reader->namespaceURI ?: $reader->getAttribute('xmlns');
                }
                if (!$type) {
                    $type = $reader->name;
                }
                break;
            }
        }

        $reader->close();

        /*
        // TODO The logger is not available.
        $error = libxml_get_last_error();
        if ($error) {
            $message = new \Omeka\Stdlib\PsrMessage(
                'Error level {level}, code {code}, for file "{file}", line {line}, column {column}: {message}',
                ['level' => $error->level, 'code' => $error->code, 'file' => $error->file, 'line' => $error->line, 'column' => $error->column, 'message' => $error->message]
            );
            $this->logger->err($message);
        }
        */

        return $this->mediaTypeIdentifiers[$type] ?? null;
    }

    /**
     * Extract a more precise zipped media type when possible.
     *
     * In many cases, the media type is saved in a uncompressed file "mimetype"
     * at the beginning of the zip file. If present, get it.
     *
     * @return string
     */
    protected function getMediaTypeZip()
    {
        $filepath = $this->getTempPath();
        $handle = fopen($filepath, 'rb');
        $contents = fread($handle, 256);
        fclose($handle);
        return substr($contents, 30, 8) === 'mimetype'
            ? substr($contents, 38, strpos($contents, 'PK', 38) - 38)
            : null;
    }
}
