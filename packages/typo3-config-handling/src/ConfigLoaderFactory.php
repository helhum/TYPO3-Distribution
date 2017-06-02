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

use Helhum\ConfigLoader\ConfigurationLoader;
use Helhum\ConfigLoader\Reader\EnvironmentReader;
use Helhum\ConfigLoader\Reader\PhpFileReader;

class ConfigLoaderFactory
{
    /**
     * @param string $context
     * @param string $confDir
     * @return ConfigurationLoader
     */
    public static function buildLoader($context, $confDir = null)
    {
        $confDir = $confDir ?: getenv('TYPO3_PATH_COMPOSER_ROOT') . '/conf';
        return new ConfigurationLoader(
            [
                new PhpFileReader($confDir . '/settings.php'),
                new PhpFileReader($confDir . '/' . $context . '.settings.php'),
                new ExtensionSettingsReader($confDir . '/extension'),
                new EnvironmentReader('TYPO3'),
                new PhpFileReader($confDir . '/env.php'),
            ]
        );
    }
}
