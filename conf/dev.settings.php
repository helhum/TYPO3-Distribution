<?php
return [
    'imports' => [
        ['resource' => 'settings.php', 'type' => null, 'ignore_errors' => false],
        ['resource' => 'dev.settings.*.php', 'type' => 'glob', 'ignore_errors' => true],
    ],
    'SYS' => [
        // Debug
        'displayErrors' => 1,
        'devIPmask' => '*',
        'sqlDebug' => 1,
        'enableDeprecationLog' => 'file',
        'exceptionalErrors' => E_WARNING | E_USER_ERROR | E_RECOVERABLE_ERROR | E_DEPRECATED | E_USER_DEPRECATED,
        'systemLogLevel' => 0,
    ],
    'BE' => [
        'debug' => true,
        // One year!
        'sessionTimeout' => 31536000,
    ],
    'FE' => [
        'debug' => true,
    ],
    'MAIL' => [
        'transport' => 'mbox',
        'transport_mbox_file' => dirname(PATH_site) . '/var/log/sent-mails.log',
    ],
    'LOG' => [
        'writerConfiguration' => [
            \TYPO3\CMS\Core\Log\LogLevel::DEBUG => [
                \TYPO3\CMS\Core\Log\Writer\FileWriter::class => [
                    dirname(PATH_site) . '/var/log/typo3-default.log',
                ],
            ],
        ],
    ],
];
