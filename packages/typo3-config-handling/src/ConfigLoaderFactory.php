<?php
declare(strict_types=1);
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
    private static $pathAliases = [
        'cache' => 'SYS.caching.cacheConfigurations',
        'extensions' => 'EXT.extConf',
    ];

    public static function buildLoader(string $context, string $confDir = null): ConfigurationLoader
    {
        $confDir = $confDir ?: getenv('TYPO3_PATH_COMPOSER_ROOT') . '/conf';
        $configReaders = [];

        self::addReaderCollection($configReaders, $confDir . '/settings');
        self::addReaderCollection($configReaders, $confDir . '/' . $context . '.settings');

        $configReaders[] = new EnvironmentReader('TYPO3');
        $configReaders[] = new PhpFileReader($confDir . '/env.php');

        return new ConfigurationLoader(
            $configReaders,
            [
                new ExtensionSettingsSerializer(),
            ]
        );
    }

    private static function addReaderCollection(array &$readers, string $pathPrefix)
    {
        $readers[] = new PhpFileReader($pathPrefix . '.php');
        $configFiles = glob($pathPrefix . '.*.php');
        foreach ($configFiles as $settingsFile) {
            $readers[] = new NestedConfigReader(new PhpFileReader($settingsFile), self::getConfigPathFromFile($settingsFile));
        }
    }

    private static function getConfigPathFromFile(string $file)
    {
        $configPath = preg_replace('/^.*settings\./', '', pathinfo($file, PATHINFO_FILENAME));
        if (isset(self::$pathAliases[$configPath])) {
            return self::$pathAliases[$configPath];
        }
        return $configPath;
    }
}
