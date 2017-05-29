<?php
namespace Helhum\Typo3ConfigHandling;

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

class ConfigLoaderFactory
{
    /**
     * @param string $context
     * @param string $confDir
     * @return \Helhum\ConfigLoader\ConfigurationLoader
     */
    public static function buildLoader($context, $confDir = null)
    {
        $confDir = $confDir ?: getenv('TYPO3_PATH_COMPOSER_ROOT') . '/conf';
        return new \Helhum\ConfigLoader\ConfigurationLoader(
            [
                new \Helhum\ConfigLoader\Reader\PhpFileReader($confDir . '/settings.php'),
                new \Helhum\ConfigLoader\Reader\PhpFileReader($confDir . '/settings.' . $context . '.php'),
                new \Helhum\ConfigLoader\Reader\EnvironmentReader('TYPO3'),
                new \Helhum\ConfigLoader\Reader\PhpFileReader($confDir . '/env.php'),
            ]
        );
    }
}
