<?php
declare(strict_types=1);
namespace Helhum\TYPO3\ConfigHandling;

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

use Helhum\ConfigLoader\Reader\RootConfigFileReader;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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

    /**
     * @var string
     */
    private $mainConfigFile;

    /**
     * @var string
     */
    private $extensionConfigFile;

    public function __construct(
        ConfigDumper $configDumper = null,
        ConfigCleaner $configCleaner = null,
        ConfigLoader $configLoader = null,
        string $mainConfigFile = null,
        string $extensionConfigFile = null
    ) {
        $this->configDumper = $configDumper ?: new ConfigDumper();
        $this->configCleaner = $configCleaner ?: new ConfigCleaner();
        $this->configLoader = $configLoader ?: new ConfigLoader(RootConfig::getRootConfigFile());
        $this->mainConfigFile = $mainConfigFile ?: RootConfig::getMainConfigFile();
        $this->extensionConfigFile = $extensionConfigFile ?: RootConfig::getExtensionConfigFile();
    }

    public function extractConfig(array $config, array $defaultConfig): bool
    {
        $extractedConfig = false;
        $mainConfig = $this->cleanFromAlreadyActiveValues($config);
        $extensionConfig = array_intersect_key($mainConfig, ['EXT' => ['extConf' => []]]);
        $mainConfig = array_diff_key($mainConfig,  ['EXT' => ['extConf' => []]]);

        if (!empty($mainConfig)) {
            $this->configDumper->dumpToFile(
                $this->cleanMergedValuesFromDefaultValues($mainConfig, $defaultConfig, $this->mainConfigFile),
                $this->mainConfigFile
            );
            $extractedConfig = true;
        }

        if (!empty($extensionConfig)) {
            $this->configDumper->dumpToFile(
                $this->cleanMergedValuesFromDefaultValues($extensionConfig, [], $this->extensionConfigFile),
                $this->extensionConfigFile
            );
            $extractedConfig = true;
        }

        return $extractedConfig;
    }

    private function cleanFromAlreadyActiveValues(array $config): array
    {
        return $this->configCleaner->cleanConfig(
            $this->unserializeExtensionConfig($config),
            $this->unserializeExtensionConfig($this->configLoader->load())
        );
    }

    private function cleanMergedValuesFromDefaultValues(array $config, array $defaultConfig, string $configFile): array
    {
        $currentConfig = [];
        if (file_exists($configFile)) {
            $currentConfig = (new RootConfigFileReader($configFile, null, false))->readConfig();
        }
        return $this->configCleaner->cleanConfig(
            array_replace_recursive($currentConfig, $this->unserializeExtensionConfig($config)),
            $this->unserializeExtensionConfig($defaultConfig)
        );
    }

    private function unserializeExtensionConfig(array $config): array
    {
        if (!isset($config['EXT']['extConf']) || !is_array($config['EXT']['extConf'])) {
            return $config;
        }
        $unserializedConfig = $config;
        try {
            foreach ($config['EXT']['extConf'] as $extensionKey => $typo3ExtSettings) {
                if (!is_string($typo3ExtSettings)) {
                    continue;
                }
                $unserializedConfig['EXT']['extConf'][$extensionKey] = GeneralUtility::removeDotsFromTS(unserialize($typo3ExtSettings, [false]));
            }
        } catch (\RuntimeException $e) {
        }
        return $unserializedConfig;
    }
}
