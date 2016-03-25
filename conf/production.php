<?php
namespace Helhum\TYPO3\CMS\Base\Distribution;
includeIfExists(__DIR__ . '/../Settings.php');

// All production values should be in LocalConfiguration.php
// However, we enforce reasonable error reporting
$GLOBALS['TYPO3_CONF_VARS']['SYS']['exceptionalErrors'] = E_USER_DEPRECATED | E_RECOVERABLE_ERROR;
$GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLog'] = 'error_log';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLogLevel'] = 2;
