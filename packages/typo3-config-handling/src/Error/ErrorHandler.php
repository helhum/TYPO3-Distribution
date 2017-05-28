<?php
namespace Helhum\Typo3ConfigHandling\Error;

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

use TYPO3\CMS\Core\Error\ErrorHandlerInterface;
use TYPO3\CMS\Core\Error\Exception;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Alternative error handler for TYPO3
 */
class ErrorHandler implements ErrorHandlerInterface
{
    /**
     * Error levels which should result in an exception thrown.
     *
     * @var array
     */
    protected $exceptionalErrors = array();

    /**
     * Registers this class as default error handler
     *
     * @param int $errorHandlerErrors The integer representing the E_* error level which should be
     */
    public function __construct($errorHandlerErrors)
    {
        $excludedErrors = E_COMPILE_WARNING | E_COMPILE_ERROR | E_CORE_WARNING | E_CORE_ERROR | E_PARSE | E_ERROR;
        // reduces error types to those a custom error handler can process
        $errorHandlerErrors = $errorHandlerErrors & ~$excludedErrors;
        set_error_handler(array($this, 'handleError'), $errorHandlerErrors);
    }

    /**
     * Defines which error levels should result in an exception thrown.
     *
     * @param int $exceptionalErrors The integer representing the E_* error level to handle as exceptions
     * @return void
     */
    public function setExceptionalErrors($exceptionalErrors)
    {
        $this->exceptionalErrors = (int)$exceptionalErrors;
    }

    /**
     * Handles an error.
     * If the error is registered as exceptionalError it will by converted into an exception, to be handled
     * by the configured exceptionhandler. Additionally the error message is written to the configured logs.
     * If TYPO3_MODE is 'BE' the error message is also added to the flashMessageQueue, in FE the error message
     * is displayed in the admin panel (as TsLog message)
     *
     * @param int $errorLevel The error level - one of the E_* constants
     * @param string $errorMessage The error message
     * @param string $errorFile Name of the file the error occurred in
     * @param int $errorLine Line number where the error occurred
     * @return bool
     * @throws Exception with the data passed to this method if the error is registered as exceptionalError
     * @throws \Exception with the data passed to this method if the error is registered as exceptionalError
     */
    public function handleError($errorLevel, $errorMessage, $errorFile, $errorLine)
    {
        // Don't do anything if error_reporting is disabled by an @ sign
        if (error_reporting() === 0) {
            return true;
        }
        $errorLevels = array(
            E_WARNING => 'Warning',
            E_NOTICE => 'Notice',
            E_USER_ERROR => 'User Error',
            E_USER_WARNING => 'User Warning',
            E_USER_NOTICE => 'User Notice',
            E_STRICT => 'Runtime Notice',
            E_RECOVERABLE_ERROR => 'Catchable Fatal Error',
            E_DEPRECATED => 'Runtime Deprecation Notice'
        );
        $message = 'PHP ' . $errorLevels[$errorLevel] . ': ' . $errorMessage . ' in ' . $errorFile . ' line ' . $errorLine;
        if ($errorLevel & $this->exceptionalErrors) {
            throw new Exception($message, 1);
        } else {
            switch ($errorLevel) {
                case E_USER_ERROR:
                case E_RECOVERABLE_ERROR:
                    $severity = 2;
                    break;
                case E_USER_WARNING:
                case E_WARNING:
                    $severity = 1;
                    break;
                default:
                    $severity = 0;
            }
            $logTitle = 'Core: Error handler (' . TYPO3_MODE . ')';
            $message = $logTitle . ': ' . $message;
            GeneralUtility::sysLog($message, 'core', $severity + 1);
            return true;
        }
    }
}
