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

class EnvConfigFinder
{
    const PLACEHOLDER_PATTERN = '/%env\(([^)]+)\)%/';

    /**
     * @param array $config
     * @param array $accumulatedEnvVars
     * @param string $path
     * @return array
     */
    public function findEnvVars(array $config, array $accumulatedEnvVars = [], string $path = ''): array
    {
        foreach ($config as $name => $value) {
            if (is_array($value)) {
                if ($foundEnvVar = $this->extractEnvVar($name)) {
                    $accumulatedEnvVars[$foundEnvVar]['keys'][] = $path;
                }
                $accumulatedEnvVars = $this->findEnvVars($value, $accumulatedEnvVars, $path ? $path . '.' . $name : $name);
            } else {
                if ($foundEnvVar = $this->extractEnvVar($name)) {
                    $accumulatedEnvVars[$foundEnvVar]['keys'][] = $path;
                }
                if ($foundEnvVar = $this->extractEnvVar($value)) {
                    $accumulatedEnvVars[$foundEnvVar]['paths'][] = $path ? $path . '.' . $name : $name;
                }
            }
        }
        return $accumulatedEnvVars;
    }

    private function isPlaceHolder($value)
    {
        return is_string($value) && preg_match(self::PLACEHOLDER_PATTERN, $value);
    }

    private function extractEnvVar($value)
    {
        if (!$this->isPlaceHolder($value)) {
            return false;
        }
        preg_match(self::PLACEHOLDER_PATTERN, $value, $matches);
        return $matches[1];
    }
}
