<?php
namespace Helhum\Typo3ConfigHandling\Command;

use Helhum\Typo3Console\Mvc\Controller\CommandController;
use TYPO3\CMS\Core\Utility\ArrayUtility;

class SettingsCommandController extends CommandController
{
    /**
     * @param bool $dev
     */
    public function dumpCommand($dev = false)
    {
        $localConfigurationFile = getenv('TYPO3_PATH_ROOT') . '/typo3conf/LocalConfiguration.php';
        if ($dev) {
            copy(
                dirname(dirname(__DIR__)) . '/res/AdditionalConfiguration.php',
                getenv('TYPO3_PATH_ROOT') . '/typo3conf/AdditionalConfiguration.php'
            );
            $localConfigurationFileContent = '<?php return [];';
        } else {
            $localConfigurationFileContent = '<?php return ' . chr(10);
            $configLoader = \Helhum\Typo3ConfigHandling\ConfigLoaderFactory::buildLoader(
                \TYPO3\CMS\Core\Utility\GeneralUtility::getApplicationContext()->isProduction() ? 'production' : 'development'
            );
            $localConfigurationFileContent .= ArrayUtility::arrayExport($configLoader->load());
            $localConfigurationFileContent .= ';';
        }
        file_put_contents(
            $localConfigurationFile,
            $localConfigurationFileContent
        );
    }
}
