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

/**
 * A quiet exception handler which catches but ignores any exception.
 */
class ProductionExceptionHandler extends AbstractExceptionHandler
{
    /**
     * Default title for error messages
     *
     * @var string
     */
    protected $defaultTitle = 'Oops, an error occurred!';

    /**
     * Default message for error messages
     *
     * @var string
     */
    protected $defaultMessage = '';

    /**
     * Constructs this exception handler - registers itself as the default exception handler.
     */
    public function __construct()
    {
        set_exception_handler(array($this, 'handleException'));
    }

    /**
     * Echoes an exception for the web.
     *
     * @param \Exception|\Throwable $exception The exception
     * @return void
     */
    public function echoExceptionWeb($exception)
    {
        $this->sendStatusHeaders($exception);
        $this->writeLogEntries($exception, self::CONTEXT_WEB);
        $messageObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Messaging\ErrorpageMessage::class,
            $this->defaultMessage,
            $this->defaultTitle
        );
        $messageObj->output();
    }
}
