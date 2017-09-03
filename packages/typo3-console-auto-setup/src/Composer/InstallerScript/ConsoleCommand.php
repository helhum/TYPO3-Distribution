<?php
declare(strict_types=1);
namespace Typo3Console\AutoSetup\Composer\InstallerScript;

/*
 * This file is part of the TYPO3 project.
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

use Composer\Script\Event;
use Helhum\Typo3Console\Mvc\Cli\CommandDispatcher;
use TYPO3\CMS\Composer\Plugin\Core\InstallerScript;

class ConsoleCommand implements InstallerScript
{
    /**
     * @var string
     */
    private $command;

    /**
     * @var array
     */
    private $arguments;

    /**
     * @var string
     */
    private $message;

    /**
     * @var callable
     */
    private $shouldRun;

    public function __construct(
        string $command,
        array $arguments = [],
        string $message = '',
        callable $shouldRun = null
    ) {
        $this->command = $command;
        $this->arguments = $arguments;
        $this->message = $message;
        $this->shouldRun = $shouldRun ?: function () {
            return true;
        };
    }

    public function run(Event $event): bool
    {
        if (!($this->shouldRun)()) {
            return true;
        }
        $io = $event->getIO();
        if ($this->message) {
            $io->writeError(sprintf('<info>%s</info>', $this->message));
        }

        $commandDispatcher = CommandDispatcher::createFromComposerRun($event);
        $output = $commandDispatcher->executeCommand($this->command, $this->arguments);
        $io->writeError($output, true, $io::VERBOSE);

        return true;
    }
}
