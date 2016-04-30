<?php
// Reasonable error reporting by default
return [
    'SYS' => [
        'exceptionalErrors' => E_USER_DEPRECATED | E_RECOVERABLE_ERROR,
        'systemLogLevel' => 2,
    ]
];
