<?php
declare(strict_types=1);
namespace Helhum\TYPO3\ErrorHandling\Error;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

/**
 * A basic but solid exception handler which catches everything which
 * falls through the other exception handlers and provides useful debugging
 * information.
 */
class DebugExceptionHandler extends \TYPO3\CMS\Core\Error\DebugExceptionHandler
{
    use ExceptionHandlerTrait;

    public function echoExceptionWeb(\Throwable $exception)
    {
        $this->sendStatusHeaders($exception);
        $this->writeLogEntries($exception, self::CONTEXT_WEB);
        $pageHandler = new PrettyPageHandler();
        $pageHandler->handleUnconditionally(true);
        $pageHandler->addResourcePath(__DIR__ . '/../../Resources/Private/Templates');

        $editor = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['exceptionHandling']['editor'] ?? 'phpstorm';
        $pageHandler->setEditor($editor);
        if (!empty($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['exceptionHandling']['pathMapping'])) {
            $previousPageHandler = clone $pageHandler;
            $pageHandler->setEditor(
                function ($file, $line) use ($previousPageHandler) {
                    $file = str_replace('/var/www/html/', '/Users/helmut/Sites/Kunden/TYPO3-Distribution/', $file);

                    return $previousPageHandler->getEditorHref($file, $line);
                }
            );
        }

        $run = new Run();
        $run->allowQuit(false);
        $run->writeToOutput(false);
        $run->appendHandler($pageHandler);
        echo $run->handleException($exception);
    }
}
