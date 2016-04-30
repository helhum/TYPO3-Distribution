<?php
return [
    'SYS' => [
        'systemLog' => 'error_log',
        'syslogErrorReporting' => 1,
        'belogErrorReporting' => 0,
    ],
    'LOG' => [
        'writerConfiguration' => [
            \TYPO3\CMS\Core\Log\LogLevel::WARNING => [
                \TYPO3\CMS\Core\Log\Writer\FileWriter::class => [
                    'logFile' => dirname(PATH_site) . '/var/log/typo3-default.log'
                ]
            ]
        ]
    ],
];
