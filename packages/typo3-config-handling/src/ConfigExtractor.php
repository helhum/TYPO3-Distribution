<?php
declare(strict_types=1);
namespace Helhum\TYPO3\ConfigHandling;

use Helhum\ConfigLoader\Reader\RootConfigFileReader;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2017 Helmut Hummel <info@helhum.io>
 *  All rights reserved
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the text file GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

class ConfigExtractor
{
    /**
     * @var ConfigDumper
     */
    private $configDumper;

    /**
     * @var ConfigCleaner
     */
    private $configCleaner;

    /**
     * @var ConfigLoader
     */
    private $configLoader;

    public function __construct(
        ConfigDumper $configDumper = null,
        ConfigCleaner $configCleaner = null,
        ConfigLoader $configLoader = null
    ) {
        $this->configDumper = $configDumper ?: new ConfigDumper();
        $this->configCleaner = $configCleaner ?: new ConfigCleaner();
        $this->configLoader = $configLoader ?: new ConfigLoader(RootConfig::getRootConfigFile());
    }

    public function extractExtensionConfig(array $config, string $extensionConfigFile = null): bool
    {
        if (empty($config['EXT']['extConf'])) {
            return false;
        }
        $extensionConfigFile = $extensionConfigFile ?: RootConfig::getExtensionConfigFile();
        if ($extensionConfig = $this->getExtensionConfig($config['EXT']['extConf'], $extensionConfigFile)) {
            $this->configDumper->dumpToFile($extensionConfig, $extensionConfigFile);
            return true;
        }
        return false;
    }

    public function extractMainConfig(array $config, array $defaultConfig, string $mainConfigFile = null): bool
    {
        $mainConfigFile = $mainConfigFile ?: RootConfig::getMainConfigFile();
        $configToExtract = $this->getMainConfig($config, $defaultConfig, $mainConfigFile);
        if (!empty($configToExtract)) {
            $this->configDumper->dumpToFile($configToExtract, $mainConfigFile);
            return true;
        }
        return false;
    }

    private function getExtensionConfig(array $config, string $extensionConfigFile): array
    {
        $extensionConfig = [];
        try {
            foreach ($config as $extensionKey => $typo3ExtSettings) {
                $extensionConfig['EXT']['extConf'][$extensionKey] = GeneralUtility::removeDotsFromTS(unserialize($typo3ExtSettings, [false]));
            }
        } catch (\RuntimeException $e) {
        }
        $currentConfig = [];
        if (file_exists($extensionConfigFile)) {
            $currentConfig = (new RootConfigFileReader($extensionConfigFile, null, false))->readConfig();
        }
        return $this->configCleaner->cleanConfig(
            array_replace_recursive($currentConfig, $extensionConfig),
            []
        );
    }

    private function getMainConfig(array $config, array $defaultConfig, string $mainConfigFile): array
    {
        unset($config['EXT']['extConf']);
        $mainConfig = $this->configCleaner->cleanConfig(
            $config,
            $this->configLoader->load()
        );
        if (empty($mainConfig)) {
            return [];
        }
        $currentConfig = [];
        if (file_exists($mainConfigFile)) {
            $currentConfig = (new RootConfigFileReader($mainConfigFile, null, false))->readConfig();
        }
        return $this->configCleaner->cleanConfig(
            array_replace_recursive($currentConfig, $mainConfig),
            $defaultConfig
        );
    }
}
