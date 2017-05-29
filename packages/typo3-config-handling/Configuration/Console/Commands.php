<?php
return [
    'controllers' => [
        \Helhum\Typo3ConfigHandling\Command\SettingsCommandController::class,
    ],
    'runLevels' => [
        'settings:*' => \Helhum\Typo3Console\Core\Booting\RunLevel::LEVEL_COMPILE,
    ],
    'bootingSteps' => [
    ],
];
