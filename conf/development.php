<?php
// Debug
$GLOBALS['TYPO3_CONF_VARS']['SYS']['displayErrors'] = 1;
$GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask'] = '*';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['sqlDebug'] = 1;
$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = 'file';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['errorHandlerErrors'] = E_STRICT | E_WARNING | E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE | E_RECOVERABLE_ERROR | E_DEPRECATED | E_USER_DEPRECATED;
$GLOBALS['TYPO3_CONF_VARS']['SYS']['exceptionalErrors'] = E_STRICT | E_WARNING | E_RECOVERABLE_ERROR | E_DEPRECATED | E_USER_DEPRECATED;
$GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLogLevel'] = 0;

$GLOBALS['TYPO3_CONF_VARS']['BE']['debug'] = true;
$GLOBALS['TYPO3_CONF_VARS']['FE']['debug'] = true;

// Convenience
$GLOBALS['TYPO3_CONF_VARS']['BE']['sessionTimeout'] = 60 * 60 * 24 * 365; // One year!

// Caches
$cacheConfigurations = &$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'];
// Uncommenting the two lines below will slow down request times dramatically
//$cacheConfigurations['cache_core']['backend'] = 'TYPO3\CMS\Core\Cache\Backend\NullBackend';
//$cacheConfigurations['fluid_template']['backend'] = 'TYPO3\CMS\Core\Cache\Backend\NullBackend';
$cacheConfigurations['cache_hash']['backend'] = 'TYPO3\CMS\Core\Cache\Backend\NullBackend';
$cacheConfigurations['cache_pages']['backend'] = 'TYPO3\CMS\Core\Cache\Backend\NullBackend';
$cacheConfigurations['cache_pagesection']['backend'] = 'TYPO3\CMS\Core\Cache\Backend\NullBackend';
$cacheConfigurations['cache_phpcode']['backend'] = 'TYPO3\CMS\Core\Cache\Backend\NullBackend';
$cacheConfigurations['cache_rootline']['backend'] = 'TYPO3\CMS\Core\Cache\Backend\NullBackend';
$cacheConfigurations['extbase_datamapfactory_datamap']['backend'] = 'TYPO3\CMS\Core\Cache\Backend\NullBackend';
$cacheConfigurations['extbase_object']['backend'] = 'TYPO3\CMS\Core\Cache\Backend\NullBackend';
$cacheConfigurations['extbase_reflection']['backend'] = 'TYPO3\CMS\Core\Cache\Backend\NullBackend';
$cacheConfigurations['extbase_typo3dbbackend_queries']['backend'] = 'TYPO3\CMS\Core\Cache\Backend\NullBackend';
$cacheConfigurations['extbase_typo3dbbackend_tablecolumns']['backend'] = 'TYPO3\CMS\Core\Cache\Backend\NullBackend';
$cacheConfigurations['l10n']['backend'] = 'TYPO3\CMS\Core\Cache\Backend\NullBackend';

// Mail
$GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport'] = 'mbox';
$GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport_mbox_file'] = PATH_site . '../var/log/sent-mails.log';

// Logging
$GLOBALS['TYPO3_CONF_VARS']['LOG']['writerConfiguration'][\TYPO3\CMS\Core\Log\LogLevel::DEBUG][\TYPO3\CMS\Core\Log\Writer\FileWriter::class] = array(
    'logFile' => PATH_site . '../var/log/typo3-default.log'
);