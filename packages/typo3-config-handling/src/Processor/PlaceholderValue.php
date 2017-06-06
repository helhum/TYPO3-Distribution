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

use Helhum\ConfigLoader\Processor\ConfigProcessorInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;

class PlaceholderValue implements ConfigProcessorInterface
{
    const PLACEHOLDER_PATTERN = '/%(env|const|var)\(([^)]*)\)%/';

    /**
     * @var array
     */
    private $referenceConfig;

    /**
     * @param array $config
     * @throws \InvalidArgumentException
     * @return array
     */
    public function processConfig(array $config): array
    {
        if (null === $this->referenceConfig) {
            $this->referenceConfig = $config;
        }
        $processedConfig = [];
        foreach ($config as $name => $value) {
            if (is_array($value)) {
                $processedConfig[$this->replacePlaceHolder($name)] = $this->processConfig($value);
            } else {
                $processedConfig[$this->replacePlaceHolder($name)] = $this->replacePlaceHolder($value);
            }
        }

        return $processedConfig;
    }

    private function isPlaceHolder($value)
    {
        return is_string($value) && preg_match(self::PLACEHOLDER_PATTERN, $value);
    }

    private function replacePlaceHolder($value)
    {
        if (!$this->isPlaceHolder($value)) {
            return $value;
        }
        preg_match(self::PLACEHOLDER_PATTERN, $value, $matches);
        switch ($matches[1]) {
            case 'env':
                $replacedValue = getenv($matches[2]);
                break;
            case 'const':
                $replacedValue = constant($matches[2]);
                break;
            case 'var':
                $replacedValue = ArrayUtility::getValueByPath($this->referenceConfig, $matches[2], '.');
                break;
            default:
                $replacedValue = $matches[0];
        }
        if ($value === $matches[0]) {
            return $replacedValue;
        }
        return preg_replace(self::PLACEHOLDER_PATTERN, $replacedValue, $value);
    }
}
