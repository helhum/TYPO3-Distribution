<?php
return [
    'controllers' => [
        \Typo3Console\RemoteCommand\Command\RemoteCommandController::class
    ],
    'runLevels' => [
        'helhum/remote-command:remote:*' => \Helhum\Typo3Console\Core\Booting\RunLevel::LEVEL_COMPILE,
    ],
    'bootingSteps' => [
    ],
];
