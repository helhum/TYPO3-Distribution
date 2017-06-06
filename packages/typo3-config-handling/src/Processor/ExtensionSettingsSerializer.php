<?php
declare(strict_types=1);
namespace Helhum\Typo3ConfigHandling\Processor;

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

use Helhum\ConfigLoader\Config;
use Helhum\ConfigLoader\Processor\ConfigProcessorInterface;

class ExtensionSettingsSerializer implements ConfigProcessorInterface
{
    /**
     * @param array $config
     * @throws \InvalidArgumentException
     * @return array
     */
    public function processConfig(array $config): array
    {
        try {
            $extensionsSettings = Config::getValue($config, 'EXT.extConf');
            if (!is_array($extensionsSettings)) {
                return $config;
            }
            foreach ($extensionsSettings as $extensionKey => $extensionSettings) {
                if (is_array($extensionSettings)) {
                    $config['EXT']['extConf'][$extensionKey] = serialize($this->addDotsToTypoScript($extensionSettings));
                }
            }
            return $config;
        } catch (\RuntimeException $e) {
            return $config;
        }
    }

    /**
     * @param array $typoScript TypoScript configuration array
     * @return array TypoScript configuration array without dots at the end of all keys
     */
    private function addDotsToTypoScript(array $typoScript): array
    {
        $out = [];
        foreach ($typoScript as $key => $value) {
            if (is_array($value)) {
                $key .= '.';
                $out[$key] = $this->addDotsToTypoScript($value);
            } else {
                $out[$key] = $value;
            }
        }
        return $out;
    }
}
