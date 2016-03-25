<?php
// We let the loader load context specific and environmental configuration
// No other code should go in here!
(new \Helhum\TYPO3\Distribution\Configuration\ConfigurationLoader(
    \TYPO3\CMS\Core\Utility\GeneralUtility::getApplicationContext(),
    dirname(dirname(__DIR__)) . '/conf'
))->load();
