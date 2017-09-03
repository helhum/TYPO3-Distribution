<?php
namespace Typo3Console\AutoSetup\Composer;

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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleIo extends \Composer\IO\ConsoleIO
{
    /**
     * @var \Composer\IO\ConsoleIO
     */
    private $consoleIO;

    public function __construct(\Composer\IO\ConsoleIO $consoleIO)
    {
        $this->consoleIO = $consoleIO;
        parent::__construct($consoleIO->input, $consoleIO->output, $consoleIO->helperSet);
    }

    /**
     * @return InputInterface
     */
    public function getInput(): InputInterface
    {
        return $this->consoleIO->input;
    }

    /**
     * @return OutputInterface
     */
    public function getOutput(): OutputInterface
    {
        return $this->consoleIO->output;
    }
}
