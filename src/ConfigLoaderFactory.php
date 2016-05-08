<?php
namespace Helhum\TYPO3\Distribution;

/**
 * Class ConfigLoaderFactory
 */
class ConfigLoaderFactory
{
    /**
     * @param string $context
     * @param string $rootDir
     * @param array $additionalFileWatches
     * @param string $fixedCacheIdentifier
     * @return \Helhum\ConfigLoader\CachedConfigurationLoader
     */
    public static function buildLoader($context, $rootDir, $fixedCacheIdentifier = null, array $additionalFileWatches = array()) {
        $confDir = $rootDir . '/conf';
        $cacheDir = $rootDir . '/var/cache';
        if ($fixedCacheIdentifier) {
            // Freeze configuration with fixed identifier if requested
            $cacheIdentifier = $fixedCacheIdentifier;
        } else {
            $fileWatches = array_merge(
                [
                    $rootDir . '/web/typo3conf/LocalConfiguration.php',
                    $rootDir . '/web/typo3conf/AdditionalConfiguration.php',
                    $rootDir . '/.env',
                    $confDir . '/default.php',
                    $confDir . '/' . $context . '.php',
                    $confDir . '/override.php',
                ],
                $additionalFileWatches
            );
            $cacheIdentifier = self::getCacheIdentifier($context, $fileWatches);
        }
        return new \Helhum\ConfigLoader\CachedConfigurationLoader
        (
            $cacheDir,
            $cacheIdentifier,
            function() use ($confDir, $context) {
                return new \Helhum\ConfigLoader\ConfigurationLoader(
                    array(
                        new \Helhum\ConfigLoader\Reader\PhpFileReader($confDir . '/default.php'),
                        new \Helhum\ConfigLoader\Reader\PhpFileReader($confDir . '/' . $context . '.php'),
                        new \Helhum\ConfigLoader\Reader\EnvironmentReader('TYPO3'),
                        new \Helhum\ConfigLoader\Reader\PhpFileReader($confDir . '/override.php'),
                    )
                );
            }
        );
    }

    /**
     * @param string $context
     * @param array $fileWatches
     * @return string
     */
    protected static function getCacheIdentifier($context, array $fileWatches = array())
    {
        $identifier = $context;
        foreach ($fileWatches as $fileWatch) {
            if (file_exists($fileWatch)) {
                $identifier .= filemtime($fileWatch);
            }
        }
        return md5($identifier);
    }
}
