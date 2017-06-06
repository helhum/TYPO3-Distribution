<?php
(new \Helhum\Typo3ConfigHandling\ConfigLoader(
    getenv('TYPO3_PATH_COMPOSER_ROOT') . '/conf/'
    . (\TYPO3\CMS\Core\Utility\GeneralUtility::getApplicationContext()->isProduction() ? 'prod' : 'dev')
    . '.config.yml'
))->populate();
