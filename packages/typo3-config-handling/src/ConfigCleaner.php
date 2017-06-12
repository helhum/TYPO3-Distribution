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

use TYPO3\CMS\Core\Utility\ArrayUtility;

class ConfigCleaner
{
    /**
     * Remove all conf settings that are identical to the ones in default config,
     * which makes the setting obsolete
     *
     * @param array $baseConfig
     * @param array[] $referenceConfigs
     * @return array
     */
    public function cleanConfig(array $baseConfig, array ...$referenceConfigs): array
    {
        $cleanedConfig = $baseConfig;
        foreach ($referenceConfigs as $referenceConfig) {
            $cleanedConfig = $this->removeIdenticalValues($cleanedConfig, $referenceConfig);
        }
        return $this->sortImports($cleanedConfig);
    }

    private function removeIdenticalValues(array $baseConfig, array $referenceConfig): array
    {
        $cleanedBaseConfig = [];
        foreach ($baseConfig as $key => $value) {
            if (is_array($value) && array_key_exists($key, $referenceConfig) && is_array($referenceConfig[$key])) {
                $cleanedBaseConfig[$key] = $this->removeIdenticalValues($value, $referenceConfig[$key]);
            } elseif (!array_key_exists($key, $referenceConfig) || $value !== $referenceConfig[$key]) {
                $cleanedBaseConfig[$key] = $value;
            }
        }
        return array_filter($cleanedBaseConfig);
    }

    private function sortImports(array $config): array
    {
        if (isset($config['imports'])) {
            $imports = $config['imports'];
            unset($config['imports']);
            return array_merge(
                [
                'imports' => $imports,
                ],
                ArrayUtility::renumberKeysToAvoidLeapsIfKeysAreAllNumeric(
                    ArrayUtility::sortByKeyRecursive($config)
                )
            );
        }
        return $config;
    }
}
