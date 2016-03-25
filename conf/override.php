<?php
// Enforced configuration
$GLOBALS['TYPO3_CONF_VARS']['SYS']['syslogErrorReporting'] = 1;
$GLOBALS['TYPO3_CONF_VARS']['SYS']['belogErrorReporting'] = 0;
$GLOBALS['TYPO3_CONF_VARS']['LOG']['writerConfiguration'][\TYPO3\CMS\Core\Log\LogLevel::WARNING][\TYPO3\CMS\Core\Log\Writer\FileWriter::class] = array(
    'logFile' => dirname(PATH_site) . '/var/log/typo3-default.log'
);
