<?php
return [
    'controllers' => [
        \Helhum\Typo3ConfigHandling\Command\SettingsCommandController::class,
    ],
    'runLevels' => [
        'helhum/typo3-config-handling:settings:*' => \Helhum\Typo3Console\Core\Booting\RunLevel::LEVEL_COMPILE,
    ],
    'bootingSteps' => [
    ],
];
