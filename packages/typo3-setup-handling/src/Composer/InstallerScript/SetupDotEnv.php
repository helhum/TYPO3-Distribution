<?php
namespace Helhum\TYPO3\SetupHandling\Composer\InstallerScript;

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

use Composer\Script\Event as ScriptEvent;
use Helhum\Typo3Console\Mvc\Cli\CommandDispatcher;
use Helhum\Typo3ConsolePlugin\InstallerScriptInterface;

class SetupDotEnv implements InstallerScriptInterface
{
    /**
     * @var string
     */
    private $dotEnvFile;

    /**
     * @var string
     */
    private $dotEnvDistFile;

    /**
     * @param ScriptEvent $event
     * @return bool
     */
    public function shouldRun(ScriptEvent $event)
    {
        return !file_exists($this->dotEnvFile = getenv('TYPO3_PATH_COMPOSER_ROOT') . '/.env')
            && file_exists($this->dotEnvDistFile = getenv('TYPO3_PATH_COMPOSER_ROOT') . '/.env.dist');
    }

    /**
     * Call the TYPO3 setup
     *
     * @param ScriptEvent $event
     * @throws \RuntimeException
     * @return bool
     * @internal
     */
    public function run(ScriptEvent $event)
    {
        copy($this->dotEnvDistFile, $this->dotEnvFile);

        $envConfig = file_get_contents($this->dotEnvFile);
        $localConfiguration = require getenv('TYPO3_PATH_ROOT') . '/typo3conf/LocalConfiguration.php';

        $envConfig = str_replace('${TYPO3_INSTALL_DB_USER}', $localConfiguration['DB']['Connections']['Default']['user'], $envConfig);
        $envConfig = str_replace('${TYPO3_INSTALL_DB_PASSWORD}', $localConfiguration['DB']['Connections']['Default']['password'], $envConfig);
        $envConfig = str_replace('${TYPO3_INSTALL_DB_HOST}', $localConfiguration['DB']['Connections']['Default']['host'], $envConfig);
        $envConfig = str_replace('${TYPO3_INSTALL_DB_PORT}', $localConfiguration['DB']['Connections']['Default']['port'], $envConfig);
        $envConfig = str_replace('${TYPO3_INSTALL_DB_DBNAME}', $localConfiguration['DB']['Connections']['Default']['dbname'], $envConfig);
        $envConfig = str_replace('${TYPO3_INSTALL_SITE_NAME}', $localConfiguration['SYS']['sitename'], $envConfig);

        file_put_contents($this->dotEnvFile, $envConfig);

        return true;
    }
}
