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
use TYPO3\CMS\Composer\Plugin\Core\InstallerScript;

class PrepareTypo3 implements InstallerScript
{
    /**
     * @param ScriptEvent $event
     * @return bool
     */
    private function shouldRun(ScriptEvent $event): bool
    {
        return !getenv('TYPO3_IS_SET_UP');
    }

    /**
     * Call the TYPO3 setup
     *
     * @param ScriptEvent $event
     * @throws \RuntimeException
     * @return bool
     * @internal
     */
    public function run(ScriptEvent $event): bool
    {
        if (!$this->shouldRun($event)) {
            return true;
        }
        $io = $event->getIO();
        $io->writeError('<info>Setting up TYPO3 Environment and Extensions</info>');

        $commandDispatcher = CommandDispatcher::createFromComposerRun($event);
        $output = $commandDispatcher->executeCommand('install:generatepackagestates');
        $io->writeError($output, true, $io::VERBOSE);
        $output = $commandDispatcher->executeCommand('install:fixfolderstructure');
        $io->writeError($output, true, $io::VERBOSE);
        $output = $commandDispatcher->executeCommand('settings:dump', ['--no-dev' => !$event->isDevMode()]);
        if ($event->isDevMode()) {
            $output = $commandDispatcher->executeCommand('install:extensionsetupifpossible');
            $io->writeError($output, true, $io::VERBOSE);
        }

        return true;
    }
}
