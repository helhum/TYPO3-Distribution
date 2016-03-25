<?php
namespace Helhum\TYPO3\CMS\Base\Distribution;

use \TYPO3\CMS\Core\Utility\GeneralUtility;

if (!function_exists('Helhum\\TYPO3\\CMS\\Base\\Distribution\\includeIfExists')) {
    function includeIfExists($file) {
        file_exists($file) && include $file;
    }
}

// Application Context configuration
includeIfExists(__DIR__ . '/../../conf/' . strtr(strtolower(GeneralUtility::getApplicationContext()), '/', '-') . '.php');

// Respect environment variables
require dirname(dirname(__DIR__)) . '/conf/.env.php';

// Enforced default settings
$GLOBALS['TYPO3_CONF_VARS']['SYS']['syslogErrorReporting'] = 1;
$GLOBALS['TYPO3_CONF_VARS']['SYS']['belogErrorReporting'] = 0;
$GLOBALS['TYPO3_CONF_VARS']['LOG']['writerConfiguration'][\TYPO3\CMS\Core\Log\LogLevel::WARNING][\TYPO3\CMS\Core\Log\Writer\FileWriter::class] = array(
    'logFile' => dirname(PATH_site) . '/var/log/typo3-default.log'
);