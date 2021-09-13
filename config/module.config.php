<?php declare(strict_types=1);

namespace XmlViewer;

return [
    'service_manager' => [
        'factories' => [
            'Omeka\File\TempFileFactory' => Service\File\TempFileFactoryFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view',
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'urlPlainTextFile' => View\Helper\UrlPlainTextFile::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => Service\Controller\IndexControllerFactory::class,
        ],
    ],
    'file_renderers' => [
        // Aliases are not used to speed loading and to decrease memory use.
        'invokables' => [
            'xml' => Media\FileRenderer\Xml::class,
        ],
        'aliases' => [
            'application/xml' => 'xml',
            'text/xml' => 'xml',

            // Don't set formats that are useless or too complex rendered as xml.
            // Furtheremore, some are managed by other modules:
            // Omeka for svg, ViewerJs of office documents, Verovio for music score,
            // ModelViewer for models, etc.
            'application/alto+xml' => 'xml',
            'application/atom+xml' => 'xml',
            'application/marcxml+xml' => 'xml',
            'application/mets+xml' => 'xml',
            'application/mods+xml' => 'xml',
            'application/rss+xml' => 'xml',
            'application/tei+xml' => 'xml',
            'application/xhtml+xml' => 'xml',

            'application/vnd.alto+xml' => 'xml', // Deprecated in 2017.
            'application/vnd.bnf.refnum+xml' => 'xml',
            'application/vnd.ead+xml' => 'xml',
            'application/vnd.iccu.mag+xml' => 'xml',
            'application/vnd.marc21+xml' => 'xml', // Deprecated in 2011.
            // 'application/vnd.mei+xml' => 'xml',
            'application/vnd.mets+xml' => 'xml', // Deprecated in 2011.
            'application/vnd.mods+xml' => 'xml',
            // 'application/vnd.oasis.opendocument.presentation-flat-xml' => 'xml',
            // 'application/vnd.oasis.opendocument.spreadsheet-flat-xml' => 'xml',
            // 'application/vnd.oasis.opendocument.text-flat-xml' => 'xml',
            'application/vnd.openarchives.oai-pmh+xml' => 'xml',
            'application/vnd.pdf2xml+xml' => 'xml',
            // 'application/vnd.recordare.musicxml+xml' => 'xml',
            'application/vnd.tei+xml' => 'xml', // Deprecated in 2011

            // 'image/svg+xml' => 'xml',

            // 'model/vnd.collada+xml' => 'xml',

            'text/html' => 'xml',
            'text/vnd.omeka+xml' => 'xml',

            // Extensions.

            'html' => 'xml',
            'xhtml' => 'xml',
            'xml' => 'xml',

            // Model Viewer
            // 'dae' => 'xml',

            // Verovio
            // 'mei' => 'xml',
            // 'musicxml' => 'xml',
            // 'mxl' => 'xml',

            // ViewerJs
            // 'odp' => 'xml',
            // 'ods' => 'xml',
            // 'odt' => 'xml',
            // 'fodp' => 'xml',
            // 'fods' => 'xml',
            // 'fodt' => 'xml',
            // 'otp' => 'xml',
            // 'ots' => 'xml',
            // 'ott' => 'xml',
        ],
    ],
    'router' => [
        'routes' => [
            'xml' => [
                'type' => \Laminas\Router\Http\Literal::class,
                'options' => [
                    'route' => '/xml',
                    'defaults' => [
                        '__NAMESPACE__' => Controller\IndexController::class,
                        'controller' => Controller\IndexController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => false,
                'child_routes' => [
                    // Id can be a clean url id ("/" must be escaped, like in iiif).
                    'resource-id' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/:id',
                            'constraints' => [
                                'id' => '[^\/]+',
                            ],
                            'defaults' => [
                                'action' => 'show',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
