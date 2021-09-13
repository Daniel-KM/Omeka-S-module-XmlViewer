<?php declare(strict_types=1);

/**
 * List of useful renderable media types of xml files.
 *
 * This file is not used currently.
 *
 * @var array
 */
return [
    'application/xml',
    'text/xml',

    // Don't set formats that are useless or too complex rendered as xml.
    // Furtheremore, some are managed by other modules:
    // Omeka for svg, ViewerJs of office documents, Verovio for music score,
    // ModelViewer for models, etc.
    'application/alto+xml',
    'application/atom+xml',
    'application/marcxml+xml',
    'application/mets+xml',
    'application/mods+xml',
    'application/rss+xml',
    'application/tei+xml',
    'application/xhtml+xml',

    // 'application/vnd.alto+xml', // Deprecated in 2017.
    // 'application/vnd.bnf.refnum+xml',
    'application/vnd.ead+xml',
    // 'application/vnd.iccu.mag+xml',
    // 'application/vnd.marc21+xml', // Deprecated in 2011.
    // 'application/vnd.mets+xml', // Deprecated in 2011.
    // 'application/vnd.mods+xml', // Deprecated in 2011.
    // 'application/vnd.openarchives.oai-pmh+xml',
    'application/vnd.pdf2xml+xml', // Used in module IIIF Search.
    // 'application/vnd.tei+xml', // Deprecated in 2011.

    'text/html',
    // 'text/vnd.hocr+html', // Unofficial, not standard xml anyway. And recommended is alto.
    // 'text/vnd.omeka+xml',
];
