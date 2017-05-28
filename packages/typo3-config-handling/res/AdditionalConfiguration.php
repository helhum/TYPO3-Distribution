<?php
// We let the loader load context and environment specific configuration
// No other code must go in here!
$configLoader = \Helhum\Typo3ConfigHandling\ConfigLoaderFactory::buildLoader(
    \TYPO3\CMS\Core\Utility\GeneralUtility::getApplicationContext()->isProduction() ? 'production' : 'development'
);
$GLOBALS['TYPO3_CONF_VARS'] = array_replace_recursive(
    $GLOBALS['TYPO3_CONF_VARS'],
    $configLoader->load()
);
