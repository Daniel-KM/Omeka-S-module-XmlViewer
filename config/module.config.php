<?php declare(strict_types=1);

namespace XmlViewer;

$allowedMediaTypes = require dirname(__DIR__) . '/data/media-types/media-type-xml.php';

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
    'form_elements' => [
        'invokables' => [
            Form\SettingsFieldset::class => Form\SettingsFieldset::class,
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
        ]
        + array_fill_keys($allowedMediaTypes, Media\FileRenderer\Xml::class),
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
    'xmlviewer' => [
        'settings' => [
            'xmlviewer_renderings' => [
                'text/xml' => 'text',
                'application/xml' => 'text',
            ],
        ],
    ],
];
