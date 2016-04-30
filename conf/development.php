<?php
return [
    'SYS' => [
        // Debug
        'displayErrors' => 1,
        'devIPmask' => '*',
        'sqlDebug' => 1,
        'enableDeprecationLog' => 'file',
        'errorHandlerErrors' => E_STRICT | E_WARNING | E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE | E_RECOVERABLE_ERROR | E_DEPRECATED | E_USER_DEPRECATED,
        'exceptionalErrors' => E_STRICT | E_WARNING | E_RECOVERABLE_ERROR | E_DEPRECATED | E_USER_DEPRECATED,
        'systemLogLevel' => 0,
        'caching' => [
            'cacheConfigurations' => [
                // Uncommenting the two lines below will slow down request times dramatically
//                'cache_core' => array(
//                    'backend' => \TYPO3\CMS\Core\Cache\Backend\NullBackend::class,
//                ),
//                'fluid_template' => array(
//                    'backend' => \TYPO3\CMS\Core\Cache\Backend\NullBackend::class,
//                ),
                'cache_hash' => array(
                    'backend' => \TYPO3\CMS\Core\Cache\Backend\NullBackend::class,
                ),
                'cache_pages' => array(
                    'backend' => \TYPO3\CMS\Core\Cache\Backend\NullBackend::class,
                ),
                'cache_pagesection' => array(
                    'backend' => \TYPO3\CMS\Core\Cache\Backend\NullBackend::class,
                ),
                'cache_phpcode' => array(
                    'backend' => \TYPO3\CMS\Core\Cache\Backend\NullBackend::class,
                ),
                'cache_runtime' => array(
                    'backend' => \TYPO3\CMS\Core\Cache\Backend\TransientMemoryBackend::class,
                ),
                'cache_rootline' => array(
                    'backend' => \TYPO3\CMS\Core\Cache\Backend\NullBackend::class,
                ),
                'cache_imagesizes' => array(
                    'backend' => \TYPO3\CMS\Core\Cache\Backend\NullBackend::class,
                ),
                'l10n' => array(
                    'backend' => \TYPO3\CMS\Core\Cache\Backend\NullBackend::class,
                ),
                'extbase_object' => array(
                    'backend' => \TYPO3\CMS\Core\Cache\Backend\NullBackend::class,
                ),
                'extbase_reflection' => array(
                    'backend' => \TYPO3\CMS\Core\Cache\Backend\NullBackend::class,
                ),
                'extbase_typo3dbbackend_tablecolumns' => array(
                    'backend' => \TYPO3\CMS\Core\Cache\Backend\NullBackend::class,
                ),
                'extbase_typo3dbbackend_queries' => array(
                    'backend' => \TYPO3\CMS\Core\Cache\Backend\NullBackend::class,
                ),
                'extbase_datamapfactory_datamap' => array(
                    'backend' => \TYPO3\CMS\Core\Cache\Backend\NullBackend::class,
                ),
            ]
        ]
    ],
    'BE' => [
        'debug' => true,
        // Convenience
        'sessionTimeout' => 60 * 60 * 24 * 365 // One year!
    ],
    'FE' => [
        'debug' => true,
    ],
    'MAIL' => [
        'transport' => 'mbox',
        'transport_mbox_file' => PATH_site . '../var/log/sent-mails.log',
    ],
    'LOG' => [
        'writerConfiguration' => [
            \TYPO3\CMS\Core\Log\LogLevel::DEBUG => [
                \TYPO3\CMS\Core\Log\Writer\FileWriter::class => [
                    dirname(PATH_site) . '/var/log/typo3-default.log'
                ]
            ]
        ]
    ],
];
