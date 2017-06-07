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
    public static function getRootConfigFile(): string
    {
        $rootConfig = self::getRootConfig();
        $configPath = GeneralUtility::getApplicationContext()->isProduction() ? $rootConfig['prod-config'] : $rootConfig['dev-config'];
        return getenv('TYPO3_PATH_COMPOSER_ROOT') . '/' . $configPath;
    }

    public static function getInitConfigFileContent(): string
    {
        $rootConfig = self::getRootConfig();
        return <<<EOF
(new \Helhum\TYPO3\ConfigHandling\ConfigLoader(
    getenv('TYPO3_PATH_COMPOSER_ROOT')
    . '/'
    . (\TYPO3\CMS\Core\Utility\GeneralUtility::getApplicationContext()->isProduction() ? '${rootConfig['prod-config']}' : '${rootConfig['dev-config']}')
))->populate();

EOF;
    }

    private static function getRootConfig(): array
    {
        $rootConfig = [
            'prod-config' => 'conf/config.yml',
            'dev-config' => 'conf/dev.config.yml',
        ];
        $composerRoot = getenv('TYPO3_PATH_COMPOSER_ROOT');
        $composerConfig = \json_decode(file_get_contents($composerRoot . '/composer.json'), true);
        if (!empty($composerConfig['extra']['helhum/typo3-distribution']['prod-config'])) {
            $rootConfig['prod-config'] = $composerConfig['extra']['helhum/typo3-distribution']['prod-config'];
        }
        if (!empty($composerConfig['extra']['helhum/typo3-distribution']['dev-config'])) {
            $rootConfig['dev-config'] = $composerConfig['extra']['helhum/typo3-distribution']['dev-config'];
        }
        return $rootConfig;
    }
}
