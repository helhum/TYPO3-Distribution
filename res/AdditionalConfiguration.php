<?php
(new \Helhum\TYPO3\ConfigHandling\ConfigLoader(
    // Change this to match your preferred format (php or yaml)
    // and your preferred config file structure
    getenv('TYPO3_PATH_COMPOSER_ROOT') . '/conf/'
    . (\TYPO3\CMS\Core\Utility\GeneralUtility::getApplicationContext()->isProduction() ? 'prod' : 'dev')
    . '.config.yml'
))->populate();
