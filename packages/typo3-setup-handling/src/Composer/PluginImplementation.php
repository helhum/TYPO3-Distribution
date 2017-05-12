<?php
namespace Helhum\TYPO3\SetupHandling\Composer;

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
use Helhum\TYPO3\SetupHandling\Composer\InstallerScript\PrepareTypo3;
use Helhum\TYPO3\SetupHandling\Composer\InstallerScript\SetupDotEnv;
use Helhum\TYPO3\SetupHandling\Composer\InstallerScript\SetupTypo3;
use Helhum\Typo3ConsolePlugin\ScriptDispatcher;

class PluginImplementation
{
    /**
     * @var Event
     */
    private $event;

    /**
     * PluginImplementation constructor.
     *
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    public function preAutoloadDump()
    {
        ScriptDispatcher::addInstallerScript(SetupDotEnv::class, 10);
        ScriptDispatcher::addInstallerScript(SetupTypo3::class, 20);
        ScriptDispatcher::addInstallerScript(PrepareTypo3::class, 30);
    }

    /**
     * Action called after autoload dump
     */
    public function postAutoloadDump()
    {
    }
}
