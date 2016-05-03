<?php
// Reasonable error reporting by default
return [
    'SYS' => [
        'errorHandler' => \Helhum\TYPO3\Distribution\Error\ErrorHandler::class,
        'productionExceptionHandler' => \Helhum\TYPO3\Distribution\Error\ProductionExceptionHandler::class,
        'debugExceptionHandler' => \Helhum\TYPO3\Distribution\Error\DebugExceptionHandler::class,
        'errorHandlerErrors' => E_STRICT | E_WARNING | E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE | E_RECOVERABLE_ERROR | E_DEPRECATED | E_USER_DEPRECATED,
        'exceptionalErrors' => E_USER_ERROR | E_RECOVERABLE_ERROR,
        'systemLogLevel' => 2,
        'systemLog' => 'error_log',
    ]
];
