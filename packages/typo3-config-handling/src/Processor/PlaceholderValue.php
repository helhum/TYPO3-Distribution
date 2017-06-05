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

class PlaceholderValue implements ConfigProcessorInterface
{
    /**
     * @param array $config
     * @throws \InvalidArgumentException
     * @return array
     */
    public function processConfig(array $config)
    {
        $processedConfig = $config;
        foreach ($config as $name => $value) {
            if ($this->isPlaceHolder($name)) {
                $name = $this->replacePlaceHolder($name);
            }
            if (is_array($value)) {
                $processedConfig[$name] = $this->processConfig($value);
            } elseif ($this->isPlaceHolder($value)) {
                $processedConfig[$name] = $this->replacePlaceHolder($value);
            }
        }

        return $processedConfig;
    }

    private function isPlaceHolder($value)
    {
        return is_string($value);
    }

    private function replacePlaceHolder(string $value)
    {
        $value = $this->replaceEnv($value);
        $value = $this->replaceConst($value);
        return $value;
    }

    private function replaceEnv(string $value)
    {
        return preg_replace_callback(
            '/%env[(]([^)]*)[)]%/',
            function ($matches) {
                return getenv($matches[1]);
            },
            $value
        );
    }

    private function replaceConst(string $value)
    {
        return preg_replace_callback(
            '/%constant[(]([^)]*)[)]%/',
            function ($matches) {
                return constant($matches[1]);
            },
            $value
        );
    }
}
