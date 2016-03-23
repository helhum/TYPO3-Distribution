<?php
// If nothing is set up yet, we ignore the environment based configuration
if (!getenv('TYPO3__SYS__encryptionKey')) {
    return;
}

// Mandatory TYPO3 configuration
$GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] = getenv('TYPO3__SYS__encryptionKey');
$GLOBALS['TYPO3_CONF_VARS']['BE']['installToolPassword'] = getenv('TYPO3__BE__installToolPassword');
$GLOBALS['TYPO3_CONF_VARS']['DB'] = array(
    'database' => getenv('TYPO3__DB__database'),
    'host' => getenv('TYPO3__DB__host'),
    'password' => getenv('TYPO3__DB__password'),
    'port' => (int)getenv('TYPO3__DB__port'),
    'username' => getenv('TYPO3__DB__username'),
);
$GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'] = getenv('TYPO3__SYS__sitename');
$GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path'] = getenv('TYPO3__GFX__im_path');
$GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw'] = getenv('TYPO3__GFX__im_path_lzw');

// Possible environment dependent extension configuration
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['site_configuration']['domainNames'] = array(
    'default' => getenv('TYPO3__EXTCONF__site_configuration__domainNames__default'),
    'en' => getenv('TYPO3__EXTCONF__site_configuration__domainNames__en'),
);

// Optional values
if (getenv('TYPO3__BE__adminOnly')) {
    $GLOBALS['TYPO3_CONF_VARS']['BE']['adminOnly'] = getenv('TYPO3__BE__adminOnly');
}

// Default settings
$GLOBALS['TYPO3_CONF_VARS']['SYS']['syslogErrorReporting'] = 1;
$GLOBALS['TYPO3_CONF_VARS']['SYS']['belogErrorReporting'] = 0;
$GLOBALS['TYPO3_CONF_VARS']['LOG']['writerConfiguration'][\TYPO3\CMS\Core\Log\LogLevel::WARNING][\TYPO3\CMS\Core\Log\Writer\FileWriter::class] = array(
    'logFile' => PATH_site . '../Data/Logs/typo3-default.log'
);