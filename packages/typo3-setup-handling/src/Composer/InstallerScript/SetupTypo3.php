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
use Helhum\TYPO3\SetupHandling\Composer\ConsoleIo;
use Helhum\Typo3Console\Core\Booting\RunLevel;
use Helhum\Typo3Console\Core\ConsoleBootstrap;
use Helhum\Typo3Console\Install\CliSetupRequestHandler;
use Helhum\Typo3Console\Mvc\Cli\CommandDispatcher;
use Helhum\Typo3Console\Mvc\Cli\CommandManager;
use Helhum\Typo3Console\Mvc\Cli\ConsoleOutput;
use Helhum\Typo3ConsolePlugin\InstallerScriptInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;

class SetupTypo3 implements InstallerScriptInterface
{
    /**
     * @var array
     */
    private $modifiedEnvVars = [];

    /**
     * @param ScriptEvent $event
     * @return bool
     */
    public function shouldRun(ScriptEvent $event)
    {
        $command = $_SERVER['argv'][1];
        return $command === 'create-project';
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
        $io = new ConsoleIo($event->getIO());

        $this->ensureTypo3Booted($event);

        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $commandDispatcher = CommandDispatcher::createFromComposerRun($event);
        $setup = new CliSetupRequestHandler(
            $objectManager,
            $objectManager->get(CommandManager::class),
            $objectManager->get(ReflectionService::class),
            $commandDispatcher,
            new ConsoleOutput($io->getOutput(), $io->getInput())
        );
        $setup->setup($io->isInteractive(), $this->populateCommandArgumentsFromEnvironment());
        return true;
    }

    /**
     * @return array
     */
    protected function populateCommandArgumentsFromEnvironment()
    {
        $this->loadDotEnvIfPossible();
        $arguments = [
            'databaseUserName' => getenv('TYPO3_INSTALL_DB_USER'),
            'databaseUserPassword' => getenv('TYPO3_INSTALL_DB_PASSWORD'),
            'databaseHostName' => getenv('TYPO3_INSTALL_DB_HOST'),
            'databasePort' => getenv('TYPO3_INSTALL_DB_PORT'),
            'databaseSocket' => getenv('TYPO3_INSTALL_DB_UNIX_SOCKET'),
            'databaseName' => getenv('TYPO3_INSTALL_DB_DBNAME'),
            'adminUserName' => getenv('TYPO3_INSTALL_ADMIN_USER'),
            'adminPassword' => getenv('TYPO3_INSTALL_ADMIN_PASSWORD'),
            'siteName' => getenv('TYPO3_INSTALL_SITE_NAME'),
            'siteSetupType' => getenv('TYPO3_INSTALL_SITE_SETUP_TYPE'),
        ];
        $commandArguments = array_filter($arguments, function($value) {
            return $value !== false;
        });
        if (getenv('TYPO3_INSTALL_DB_USE_EXISTING') !== false) {
            $commandArguments['useExistingDatabase'] = (bool)getenv('TYPO3_INSTALL_DB_USE_EXISTING');
        }
        $this->restoreEnvVars();

        return $commandArguments;
    }

    private function loadDotEnvIfPossible()
    {
        if (class_exists(DotEnvReader::class) && file_exists($envDistFile = getenv('TYPO3_PATH_COMPOSER_ROOT') . '/.env.dist')) {
            $envBackup = $_ENV;
            $dotEnvReader = new DotEnvReader(new Dotenv(dirname($envDistFile), basename($envDistFile)), new Cache(null, ''));
            $dotEnvReader->read();
            $this->modifiedEnvVars = array_keys(array_diff_assoc($_ENV, $envBackup));
        }
    }

    private function restoreEnvVars()
    {
        foreach ($this->modifiedEnvVars as $name) {
            putenv($name);
            unset($_ENV[$name], $_SERVER[$name]);
        }
    }

    /**
     * @return bool
     */
    private function hasTypo3Booted()
    {
        // Since this code is executed in composer runtime,
        // we can safely assume that TYPO3 has not been bootstrapped
        // until this API has been initialized to return true
        return ConsoleBootstrap::usesComposerClassLoading();
    }

    /**
     * @param ScriptEvent $event
     * @return ConsoleBootstrap
     */
    private function ensureTypo3Booted(ScriptEvent $event)
    {
        if (!$this->hasTypo3Booted()) {
            $_SERVER['argv'][0] = 'typo3';
            if (file_exists($dotEnvFile = $event->getComposer()->getConfig()->get('vendor-dir') . '/helhum/dotenv-include.php')) {
                require $dotEnvFile;
            }
            $bootstrap = ConsoleBootstrap::create('Production');
            $bootstrap->initialize(new \Composer\Autoload\ClassLoader());
            /** @var RunLevel $runLevel */
            $runLevel = $bootstrap->getEarlyInstance(RunLevel::class);
            $runLevel->buildSequence(RunLevel::LEVEL_COMPILE)->invoke($bootstrap);
        } else {
            $bootstrap = ConsoleBootstrap::getInstance();
        }
        return $bootstrap;
    }
}
