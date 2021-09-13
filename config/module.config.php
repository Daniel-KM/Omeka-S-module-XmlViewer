<?php declare(strict_types=1);

namespace XmlViewer;

return [
    'service_manager' => [
        'factories' => [
            'Omeka\File\TempFileFactory' => Service\File\TempFileFactoryFactory::class,
        ],
    ],
];
