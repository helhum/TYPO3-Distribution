<?php
namespace Helhum\TYPO3\CMS\Base\Distribution;

use \TYPO3\CMS\Core\Utility\GeneralUtility;

if (!function_exists('Helhum\\TYPO3\\CMS\\Base\\Distribution\\includeIfExists')) {
    function includeIfExists($file) {
        file_exists($file) && include $file;
    }
}

// Application Context Configuration
includeIfExists(__DIR__ . '/../../Configuration/' . GeneralUtility::getApplicationContext() . '/Settings.php');
