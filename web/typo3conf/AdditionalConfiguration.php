<?php
// We let the loader load context and environment specific configuration
// No other code must go in here!
(new \Helhum\ConfigLoader\ConfigurationLoader(
    $GLOBALS['TYPO3_CONF_VARS'],
    \TYPO3\CMS\Core\Utility\GeneralUtility::getApplicationContext(),
    dirname(dirname(__DIR__)) . '/conf',
    'TYPO3',
    '__'
))->load();
