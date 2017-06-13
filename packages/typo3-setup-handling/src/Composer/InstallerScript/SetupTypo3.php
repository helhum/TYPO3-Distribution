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
use Helhum\TYPO3\SetupHandling\Composer\ConsoleIo;
use Helhum\Typo3Console\Core\Booting\RunLevel;
use Helhum\Typo3Console\Core\ConsoleBootstrap;
use Helhum\Typo3Console\Install\CliSetupRequestHandler;
use Helhum\Typo3Console\Mvc\Cli\CommandDispatcher;
use Helhum\Typo3Console\Mvc\Cli\CommandManager;
use Helhum\Typo3Console\Mvc\Cli\ConsoleOutput;
use Helhum\Typo3ConsolePlugin\InstallerScriptInterface;
use Symfony\Component\Dotenv\Dotenv;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;

class SetupTypo3 implements InstallerScriptInterface
{
    /**
     * @var string
     */
    private $installedFile;

    public function __construct()
    {
        if (class_exists(Dotenv::class)) {
            $this->installedFile = getenv('TYPO3_PATH_COMPOSER_ROOT') . '/.env';
        } else {
            $this->installedFile = getenv('TYPO3_PATH_COMPOSER_ROOT') . '/.installed';
        }
    }

    /**
     * @param ScriptEvent $event
     * @return bool
     */
    public function shouldRun(ScriptEvent $event)
    {
        return $event->isDevMode() && !file_exists($this->installedFile);
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
        $io = $event->getIO();
        $io->writeError('');
        $io->writeError('<info>Setting up TYPO3</info>');

        $consoleIO = new ConsoleIo($event->getIO());
        $this->ensureTypo3Booted($event);
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $commandDispatcher = CommandDispatcher::createFromComposerRun($event);
        $setup = new CliSetupRequestHandler(
            $objectManager,
            $objectManager->get(CommandManager::class),
            $objectManager->get(ReflectionService::class),
            $commandDispatcher,
            new ConsoleOutput($consoleIO->getOutput(), $consoleIO->getInput())
        );
        $setup->setup($consoleIO->isInteractive(), $this->populateCommandArgumentsFromEnvironment());
        putenv('TYPO3_IS_SET_UP=1');

        return true;
    }

    /**
     * @return array
     */
    protected function populateCommandArgumentsFromEnvironment()
    {
        $envValues = [];
        if (file_exists($envInstallFile = getenv('TYPO3_PATH_COMPOSER_ROOT') . '/.env.install')) {
            $envValues = (new Dotenv())->parse(file_get_contents($envInstallFile), $envInstallFile);
        }

        $arguments = [
            'databaseUserName' => $envValues['TYPO3_INSTALL_DB_USER'] ?? getenv('TYPO3_INSTALL_DB_USER'),
            'databaseUserPassword' => $envValues['TYPO3_INSTALL_DB_PASSWORD'] ?? getenv('TYPO3_INSTALL_DB_PASSWORD'),
            'databaseHostName' => $envValues['TYPO3_INSTALL_DB_HOST'] ?? getenv('TYPO3_INSTALL_DB_HOST'),
            'databasePort' => $envValues['TYPO3_INSTALL_DB_PORT'] ?? getenv('TYPO3_INSTALL_DB_PORT'),
            'databaseSocket' => $envValues['TYPO3_INSTALL_DB_UNIX_SOCKET'] ?? getenv('TYPO3_INSTALL_DB_UNIX_SOCKET'),
            'databaseName' => $envValues['TYPO3_INSTALL_DB_DBNAME'] ?? getenv('TYPO3_INSTALL_DB_DBNAME'),
            'adminUserName' => $envValues['TYPO3_INSTALL_ADMIN_USER'] ?? getenv('TYPO3_INSTALL_ADMIN_USER'),
            'adminPassword' => $envValues['TYPO3_INSTALL_ADMIN_PASSWORD'] ?? getenv('TYPO3_INSTALL_ADMIN_PASSWORD'),
            'siteName' => $envValues['TYPO3_INSTALL_SITE_NAME'] ?? getenv('TYPO3_INSTALL_SITE_NAME'),
            'siteSetupType' => $envValues['TYPO3_INSTALL_SITE_SETUP_TYPE'] ?? getenv('TYPO3_INSTALL_SITE_SETUP_TYPE'),
        ];
        $commandArguments = array_filter($arguments, function ($value) {
            return $value !== false;
        });
        $useExistingDb = $envValues['TYPO3_INSTALL_DB_USE_EXISTING'] ?? getenv('TYPO3_INSTALL_DB_USE_EXISTING');
        if ($useExistingDb !== false) {
            $commandArguments['useExistingDatabase'] = (bool)$useExistingDb;
        }

        return $commandArguments;
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
