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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * An abstract exception handler
 */
abstract class AbstractExceptionHandler
{
    const CONTEXT_WEB = 'WEB';
    const CONTEXT_CLI = 'CLI';

    /**
     * Displays the given exception
     *
     * @param \Exception|\Throwable $exception The exception(PHP 5.x) or throwable(PHP >= 7.0) object.
     *
     * @throws \Exception
     */
    public function handleException($exception)
    {
        if ($exception instanceof \Throwable || $exception instanceof \Exception) {
            switch (PHP_SAPI) {
                case 'cli':
                    $this->echoExceptionCLI($exception);
                    break;
                default:
                    $this->echoExceptionWeb($exception);
            }
        } else {
            throw new \Exception('handleException was called the wrong way.', 1450714322);
        }
    }

    /**
     * Echoes an exception for the command line.
     *
     * @param \Exception|\Throwable $exception The exception
     * @return void
     */
    public function echoExceptionCLI($exception)
    {
        $filePathAndName = $exception->getFile();
        $exceptionCodeNumber = $exception->getCode() > 0 ? '#' . $exception->getCode() . ': ' : '';
        $this->writeLogEntries($exception, self::CONTEXT_CLI);
        echo '
Uncaught TYPO3 Exception ' . $exceptionCodeNumber . $exception->getMessage() . LF;
        echo 'thrown in file ' . $filePathAndName . LF;
        echo 'in line ' . $exception->getLine() . LF . LF;
        die(1);
    }

    /**
     * Writes exception to different logs
     *
     * @param \Exception|\Throwable $exception The exception(PHP 5.x) or throwable(PHP >= 7.0) object.
     * @param string $context The context where the exception was thrown, WEB or CLI
     * @return void
     * @see \TYPO3\CMS\Core\Utility\GeneralUtility::sysLog(), \TYPO3\CMS\Core\Utility\GeneralUtility::devLog()
     */
    protected function writeLogEntries($exception, $context)
    {
        // Do not write any logs for this message to avoid filling up tables or files with illegal requests
        if ($exception->getCode() === 1396795884) {
            return;
        }
        $filePathAndName = $exception->getFile();
        $exceptionCodeNumber = $exception->getCode() > 0 ? '#' . $exception->getCode() . ': ' : '';
        $logTitle = 'Core: Exception handler (' . $context . ')';
        $logMessage = 'Uncaught TYPO3 Exception: ' . $exceptionCodeNumber . $exception->getMessage() . ' | '
            . get_class($exception) . ' thrown in file ' . $filePathAndName . ' in line ' . $exception->getLine();
        if ($context === self::CONTEXT_WEB) {
            $logMessage .= '. Requested URL: ' . GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL');
        }
        // Write error message to the configured syslogs
        GeneralUtility::sysLog($logMessage, $logTitle, GeneralUtility::SYSLOG_SEVERITY_FATAL);
    }

    /**
     * Sends the HTTP Status 500 code, if $exception is *not* a
     * TYPO3\CMS\Core\Error\Http\StatusException and headers are not sent, yet.
     *
     * @param \Exception|\Throwable $exception The exception(PHP 5.x) or throwable(PHP >= 7.0) object.
     * @return void
     */
    protected function sendStatusHeaders($exception)
    {
        if (method_exists($exception, 'getStatusHeaders')) {
            $headers = $exception->getStatusHeaders();
        } else {
            $headers = [\TYPO3\CMS\Core\Utility\HttpUtility::HTTP_STATUS_500];
        }
        if (!headers_sent()) {
            foreach ($headers as $header) {
                header($header);
            }
        }
    }
}
