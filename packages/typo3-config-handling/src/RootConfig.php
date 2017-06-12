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

use TYPO3\CMS\Core\Utility\GeneralUtility;

class RootConfig
{
    public static function getRootConfigFile(bool $isProduction = null): string
    {
        $isProduction = $isProduction ?? GeneralUtility::getApplicationContext()->isProduction();
        return $isProduction ? self::getRootConfig()['prod-config'] : self::getRootConfig()['dev-config'];
    }

    public static function getMainConfigFile(): string
    {
        return self::getRootConfig()['main-config'];
    }

    public static function getExtensionConfigFile(): string
    {
        return self::getRootConfig()['ext-config'];
    }

    public static function getInitConfigFileContent(): string
    {
        return <<<'EOF'
(new \Helhum\TYPO3\ConfigHandling\ConfigLoader(
    \Helhum\TYPO3\ConfigHandling\RootConfig::getRootConfigFile()
))->populate();
EOF;
    }

    private static function getRootConfig(): array
    {
        $composerRoot = getenv('TYPO3_PATH_COMPOSER_ROOT');
        $rootConfig = [
            'prod-config' => $composerRoot . '/conf/config.yml',
            'dev-config' => $composerRoot . '/conf/dev.config.yml',
            'main-config' => null,
            'ext-config' => null,
        ];
        $composerConfig = \json_decode(file_get_contents($composerRoot . '/composer.json'), true);
        foreach ($rootConfig as $name => $defaultValue) {
            if (!empty($composerConfig['extra']['helhum/typo3-config-handling'][$name])) {
                $rootConfig[$name] = $composerRoot . '/' . $composerConfig['extra']['helhum/typo3-config-handling'][$name];
            }
        }
        if (empty($rootConfig['main-config'])) {
            $rootConfig['main-config'] = $rootConfig['prod-config'];
        }
        if (empty($rootConfig['ext-config'])) {
            $rootConfig['ext-config'] = $rootConfig['main-config'];
        }
        return $rootConfig;
    }
}
