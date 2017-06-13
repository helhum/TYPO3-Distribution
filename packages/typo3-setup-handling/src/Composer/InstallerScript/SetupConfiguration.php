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

use Composer\IO\IOInterface;
use Composer\Script\Event as ScriptEvent;
use Helhum\ConfigLoader\Reader\RootConfigFileReader;
use Helhum\TYPO3\ConfigHandling\ConfigCleaner;
use Helhum\TYPO3\ConfigHandling\ConfigDumper;
use Helhum\TYPO3\ConfigHandling\ConfigExtractor;
use Helhum\TYPO3\ConfigHandling\ConfigLoader;
use Helhum\TYPO3\ConfigHandling\EnvConfigFinder;
use Helhum\TYPO3\ConfigHandling\RootConfig;
use Helhum\Typo3Console\Mvc\Cli\CommandDispatcher;
use Helhum\Typo3ConsolePlugin\InstallerScriptInterface;
use Symfony\Component\Dotenv\Dotenv;
use TYPO3\CMS\Core\Configuration\ConfigurationManager;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use Typo3Console\PhpServer\Command\ServerCommandController;

class SetupConfiguration implements InstallerScriptInterface
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
     * @var string
     */
    private $dotEnvInstallFile;

    public function __construct()
    {
        $this->dotEnvFile = getenv('TYPO3_PATH_COMPOSER_ROOT') . '/.env';
        $this->dotEnvDistFile = getenv('TYPO3_PATH_COMPOSER_ROOT') . '/.env.dist';
        $this->dotEnvInstallFile = getenv('TYPO3_PATH_COMPOSER_ROOT') . '/.env.install';
    }

    /**
     * @param ScriptEvent $event
     * @return bool
     */
    public function shouldRun(ScriptEvent $event)
    {
        return getenv('TYPO3_IS_SET_UP');
    }

    /**
     * Call the TYPO3 setup
     *
     * @param ScriptEvent $event
     * @throws \RuntimeException
     * @return bool
     * @throws \Helhum\Typo3Console\Mvc\Cli\FailedSubProcessCommandException
     * @internal
     */
    public function run(ScriptEvent $event)
    {
        $io = $event->getIO();
        $io->writeError('');
        $io->writeError('<info>Setting up TYPO3 Configuration</info>');

        $configurationManager = new ConfigurationManager();
        $configCleaner = new ConfigCleaner();
        // We are not interested in any settings that match the default configuration
        $typo3InstallConfig = $configCleaner->cleanConfig($configurationManager->getLocalConfiguration(), $configurationManager->getDefaultConfiguration());

        if (class_exists(Dotenv::class)) {
            $typo3InstallConfig = $this->generateDotEnvFile($io, $typo3InstallConfig);
        } else {
            touch(getenv('TYPO3_PATH_COMPOSER_ROOT') . '/.installed');
        }

        $this->extractConfig($event, $io, $typo3InstallConfig);

        $io->writeError('');
        $io->writeError('<info>Your TYPO3 installation is now ready to use</info>');
        if (class_exists(ServerCommandController::class)) {
            $io->writeError('');
            $io->writeError(sprintf('Run <comment>%s server:run</comment> in your project root directory, to start the PHP builtin webserver.', substr($event->getComposer()->getConfig()->get('bin-dir') . '/typo3cms', strlen(getcwd()) + 1)));
        }
        $io->writeError('');
        return true;
    }

    /**
     * @param string $dotEnvFile
     * @return array
     */
    private function getParsedEnvFileValues($dotEnvFile)
    {
        if (!file_exists($dotEnvFile)) {
            return [];
        }
        return (new Dotenv())->parse(file_get_contents($dotEnvFile), $dotEnvFile);
    }

    /**
     * @param IOInterface $io
     * @param $typo3InstallConfig
     * @return array
     * @throws \RuntimeException
     */
    private function generateDotEnvFile(IOInterface $io, $typo3InstallConfig): array
    {
        $io->writeError('Generating .env file', true, $io::VERBOSE);

        $config = (new RootConfigFileReader(RootConfig::getRootConfigFile()))->readConfig();
        $foundEnvVarsInConfig = (new EnvConfigFinder())->findEnvVars($config);
        $foundEnvVarsInDotEnvFile = $this->getParsedEnvFileValues($this->dotEnvDistFile);
        $installationDefaults = $this->getParsedEnvFileValues($this->dotEnvInstallFile);

        $dotEnvConfig = [
            'TYPO3_CONTEXT' => 'Development'
        ];
        foreach ($foundEnvVarsInConfig as $name => $places) {
            try {
                if (!empty($places['paths'])) {
                    foreach ($places['paths'] as $path) {
                        $foundValue = ArrayUtility::getValueByPath($typo3InstallConfig, $path, '.');
                        $typo3InstallConfig = ArrayUtility::removeByPath($typo3InstallConfig, $path, '.');
                    }
                }
            } catch (\RuntimeException $e) {
            }
            if (isset($foundValue)) {
                $dotEnvConfig[$name] = $foundValue;
                unset($foundValue);
            } else {
                if (false !== getenv($name)) {
                    $io->writeError(sprintf('Skipping "%s" env var, as it is already set. You may need to put it into your .env file though.',
                        $name), true, $io::VERBOSE);
                    continue;
                }
                if (strpos($name, 'TYPO3_INSTALL_DB_') !== false) {
                    // Skip DB connection values that were stripped out from LocalConfiguration.php by TYPO
                    // as they are not needed in this system
                    $dotEnvConfig[$name] = '';
                    continue;
                }
                if (empty($infoShown)) {
                    $io->writeError('<info>Please provide some required settings for your distribution:</info>');
                }
                $infoShown = true;
                $question = empty($foundEnvVarsInDotEnvFile[$name]) ? $name : $foundEnvVarsInDotEnvFile[$name];
                $defaultValue = $installationDefaults[$name] ?? null;
                do {
                    $answer = $io->ask(
                        '<comment>' . $question . ($defaultValue ? sprintf(' (%s):', $defaultValue) : ':') . '</comment> ',
                        $defaultValue
                    );
                } while ($answer === null);
                $dotEnvConfig[$name] = $answer;
            }
        }
        $missingEnvVars = array_diff_key($foundEnvVarsInDotEnvFile, $dotEnvConfig);
        $dotEnvConfigContent = '';
        foreach ($dotEnvConfig as $name => $value) {
            $dotEnvConfigContent .= "$name='$value'\n";
        }
        file_put_contents($this->dotEnvFile, $dotEnvConfigContent);
        return $typo3InstallConfig;
    }

    /**
     * @param ScriptEvent $event
     * @param IOInterface $io
     * @param $typo3InstallConfig
     * @throws \Helhum\Typo3Console\Mvc\Cli\FailedSubProcessCommandException
     */
    private function extractConfig(ScriptEvent $event, IOInterface $io, $typo3InstallConfig)
    {
        $io->writeError('Merging installed TYPO3 config with project settings', true, $io::VERBOSE);
        $configExtractor = new ConfigExtractor(
            new ConfigDumper(),
            new ConfigCleaner(),
            new ConfigLoader(RootConfig::getRootConfigFile(true))
        );
        $configExtractor->extractExtensionConfig($typo3InstallConfig);
        $configExtractor->extractMainConfig($typo3InstallConfig, (new ConfigurationManager())->getDefaultConfiguration());
        $commandDispatcher = CommandDispatcher::createFromComposerRun($event);
        $commandDispatcher->executeCommand('settings:dump', ['--no-dev' => !$event->isDevMode()]);
    }
}
