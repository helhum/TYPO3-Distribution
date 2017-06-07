<?php
return (
    function($populateConfig) {
        $prefix = \TYPO3\CMS\Core\Utility\GeneralUtility::getApplicationContext()->isProduction() ? 'prod' : 'dev';
        $rootDir = getenv('TYPO3_PATH_COMPOSER_ROOT');
        // Change this to match your preferred format (php or yaml)
        // and your preferred config file structure
        // Do not change anything above this line

        $rootConfigPath = "${rootDir}/conf/${prefix}.config.yml";

        // Do not change anything below this line
        if ($populateConfig) {
            (new \Helhum\TYPO3\ConfigHandling\ConfigLoader($rootConfigPath))->populate();
        }
        return $rootConfigPath;
    }
)($populateConfig ?? true);
