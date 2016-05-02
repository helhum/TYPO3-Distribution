<?php
// We let the loader load context and environment specific configuration
// No other code must go in here!
$context = \TYPO3\CMS\Core\Utility\GeneralUtility::getApplicationContext()->isProduction() ? 'production' : 'development';
$rootDir = dirname(dirname(__DIR__));
$confDir = $rootDir . '/conf';
$cacheDir = $rootDir . '/var/cache';
if (getenv('CONFIGURATION_CACHE_IDENTIFIER')) {
    // Freeze configuration with fixed identifier if requested
    $cacheIdentifier = getenv('CONFIGURATION_CACHE_IDENTIFIER');
} else {
    $cacheIdentifier = file_exists($rootDir . '/.env') ? md5($context . filemtime($rootDir . '/.env') . filemtime($rootDir . '/web/typo3conf/LocalConfiguration.php')) :  null;
}

$GLOBALS['TYPO3_CONF_VARS'] = (new \Helhum\ConfigLoader\CachedConfigurationLoader(
    $GLOBALS['TYPO3_CONF_VARS'],
    $cacheDir,
    $cacheIdentifier,
    function() use ($confDir, $context) {
        return new \Helhum\ConfigLoader\ConfigurationLoader(array(
                new \Helhum\ConfigLoader\Reader\PhpFileReader($confDir . '/default.php'),
                new \Helhum\ConfigLoader\Reader\PhpFileReader($confDir . '/' . $context . '.php'),
                new \Helhum\ConfigLoader\Reader\EnvironmentReader('TYPO3'),
                new \Helhum\ConfigLoader\Reader\PhpFileReader($confDir . '/override.php'),
            ));
    }
))->load();
