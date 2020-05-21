<?php
declare(strict_types=1);
namespace Helhum\TYPO3\ErrorHandling\Error;

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

use Helhum\Typo3Console\Error\ExceptionRenderer;
use Symfony\Component\Console\Output\ConsoleOutput;

trait ExceptionHandlerTrait
{
    /**
     * Formats and echoes the exception for the command line
     *
     * @param \Throwable $exception The throwable object.
     */
    public function echoExceptionCLI(\Throwable $exception): void
    {
        $exceptionRenderer = new ExceptionRenderer();
        $output = new ConsoleOutput();
        $output->setVerbosity($output::VERBOSITY_DEBUG);
        $exceptionRenderer->render($exception, $output);
        die(1);
    }

    protected function writeLog($logMessage)
    {
        // Don't write to sys_log database table
    }
}
