<?php
// We let the loader load context and environment specific configuration
// No other code must go in here!
$context = \TYPO3\CMS\Core\Utility\GeneralUtility::getApplicationContext()->isProduction() ? 'production' : 'development';
$confDir = dirname(dirname(__DIR__)) . '/conf';
$configLoader = new \Helhum\ConfigLoader\ConfigurationLoader(
    array(
        new \Helhum\ConfigLoader\Reader\PhpFileReader($confDir . '/default.php'),
        new \Helhum\ConfigLoader\Reader\PhpFileReader($confDir . '/' . $context . '.php'),
        new \Helhum\ConfigLoader\Reader\EnvironmentReader('TYPO3'),
        new \Helhum\ConfigLoader\Reader\PhpFileReader($confDir . '/override.php'),
    )
);
$GLOBALS['TYPO3_CONF_VARS'] = array_replace_recursive($GLOBALS['TYPO3_CONF_VARS'], $configLoader->load());
