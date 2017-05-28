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
use Dotenv\Dotenv;
use Helhum\DotEnvConnector\Cache;
use Helhum\DotEnvConnector\DotEnvReader;
use Helhum\Typo3Console\Mvc\Cli\CommandDispatcher;
use Helhum\Typo3ConsolePlugin\InstallerScriptInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\StringUtility;

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

    public function __construct()
    {
        $this->dotEnvFile = getenv('TYPO3_PATH_COMPOSER_ROOT') . '/.env';
        $this->dotEnvDistFile = getenv('TYPO3_PATH_COMPOSER_ROOT') . '/.env.dist';
    }

    /**
     * @param ScriptEvent $event
     * @return bool
     */
    public function shouldRun(ScriptEvent $event)
    {
        return false;
        return class_exists(DotEnvReader::class)
            && !file_exists($this->dotEnvFile)
            && file_exists($this->dotEnvDistFile);
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
        $envConfig = file_get_contents($this->dotEnvDistFile);

        $envBackup = $_ENV;
        $dotEnvReader = new DotEnvReader(new Dotenv(dirname($this->dotEnvDistFile), basename($this->dotEnvDistFile)), new Cache(null, ''));
        $dotEnvReader->read();
        $modifiedEnvVars = array_diff_assoc($_ENV, $envBackup);
        $envConfig = $this->removePromptValues($envConfig);
        $envConfig = $this->removeAutoInstallValues($envConfig);

        $localConfiguration = require getenv('TYPO3_PATH_ROOT') . '/typo3conf/LocalConfiguration.php';
        $configPathsToRemove = [];
        foreach ($modifiedEnvVars as $envName => $envValue) {
            if (StringUtility::beginsWith($envName, 'TYPO3_INSTALL_PROMPT_')
                && !StringUtility::endsWith($envName, '_DEFAULT')
            ) {
                $defaultValue = getenv($envName . '_DEFAULT') ?: null;
                do {
                    $answer = $event->getIO()->ask($envValue . ($defaultValue ? sprintf(' (%s) :', $defaultValue) : ': '), $defaultValue);
                } while ($answer === null);
                $envConfig = str_replace('${' . $envName . '}', $answer, $envConfig);
            } elseif (StringUtility::beginsWith($envName, 'TYPO3__')) {
                try {
                    $configPath = str_replace(['TYPO3__', '__'], ['', '/'], $envName);
                    $answer = ArrayUtility::getValueByPath($localConfiguration, $configPath);
                    $envConfig = str_replace($envName . '=""', $envName . '="' . $answer . '"', $envConfig);
                    $configPathsToRemove[] = $configPath;
                } catch (\RuntimeException $e) {

                }
            }
        }

        file_put_contents($this->dotEnvFile, $envConfig);
        $commandDispatcher = CommandDispatcher::createFromComposerRun($event);
        $commandDispatcher->executeCommand('configuration:remove', ['--paths' => $configPathsToRemove, '--force' => true]);

        return true;
    }

    /**
     * @param string $envConfig
     * @return string
     * @throws \RuntimeException
     */
    private function removeAutoInstallValues($envConfig)
    {
        $cleanedConfig = preg_replace(
            '/(# ### TYPO3 AUTO INSTALL VALUES ###).*(# ### TYPO3 AUTO INSTALL VALUES ###)/is',
            '',
            $envConfig
        );
        if ($cleanedConfig === null) {
            throw new \RuntimeException('Failed to remove install values from .env.dist file', 1494850058);
        }
        return $cleanedConfig;
    }

    /**
     * @param string $envConfig
     * @return string
     * @throws \RuntimeException
     */
    private function removePromptValues($envConfig)
    {
        $cleanedConfig = preg_replace(
            '/(# ### INPUT VALUES ###).*(# ### INPUT VALUES ###)/is',
            '',
            $envConfig
        );
        if ($cleanedConfig === null) {
            throw new \RuntimeException('Failed to remove prompt values from .env.dist file', 1494850059);
        }
        return $cleanedConfig;
    }

    private function restoreEnvVars()
    {
        foreach ($this->modifiedEnvVars as $name) {
            putenv($name);
            unset($_ENV[$name], $_SERVER[$name]);
        }
    }
}
