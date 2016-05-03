<?php
namespace Helhum\TYPO3\Distribution\Error;

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
