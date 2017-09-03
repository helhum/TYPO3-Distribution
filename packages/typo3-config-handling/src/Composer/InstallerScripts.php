<?php
declare(strict_types=1);
namespace Helhum\TYPO3\ConfigHandling\Composer;

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

use Composer\Script\Event;
use Helhum\TYPO3\ConfigHandling\Composer\InstallerScript\SetupConfiguration;
use TYPO3\CMS\Composer\Plugin\Core\InstallerScriptsRegistration;
use TYPO3\CMS\Composer\Plugin\Core\ScriptDispatcher;
use Typo3Console\ComposerAutoSetup\Composer\InstallerScript\ConsoleCommand;

class InstallerScripts implements InstallerScriptsRegistration
{
    public static function register(Event $event, ScriptDispatcher $scriptDispatcher)
    {
        $scriptDispatcher->addInstallerScript(new SetupConfiguration(), 68);
        $scriptDispatcher->addInstallerScript(
            new ConsoleCommand('settings:dump', ['--no-dev' => !$event->isDevMode()]),
            61
        );
    }
}
