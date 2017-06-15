<?php
declare(strict_types=1);
namespace Typo3Console\RemoteCommand\Command;

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

use Helhum\ConfigLoader\Reader\RootConfigFileReader;
use Helhum\TYPO3\ConfigHandling\EnvConfigFinder;
use Helhum\TYPO3\ConfigHandling\RootConfig;
use Helhum\Typo3Console\Mvc\Controller\CommandController;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Typo3Console\RemoteCommand\Node;
use Typo3Console\RemoteCommand\RemoteCommand;

class RemoteCommandController extends CommandController
{
    /**
     * @param string $remote
     * @param string $url
     */
    public function addCommand(string $remote, string $url)
    {
        $parsedUrl = parse_url($url);
        $this->writeConfig(
            $remote,
            $this->validateConfig([
                'userName' => $parsedUrl['user'] ?? '',
                'hostName' => $parsedUrl['host'] ?? null,
                'path' => $parsedUrl['path'] ?? null,
            ])
        );
    }

    /**
     * @param string $command
     * @param string $name
     * @param string $projectName
     * @param string $deploymentHost
     * @param string $deploymentPath
     */
    public function surfCommand(
        string $command = 'init',
        string $name = '',
        string $projectName = '',
        string $deploymentHost = '',
        string $deploymentPath = ''
    ) {
        if ($command !== 'init') {
            $this->outputLine('<error>Can do nothing else than "init"</error>');
        }
        if (!$name) {
            do {
                $name = strtolower($this->output->ask('<comment>Name:</comment> '));
            } while (!$name);
        }
        if (!file_exists($deploymentFile = getenv('TYPO3_PATH_COMPOSER_ROOT') . '/.surf/' . $name . '.php')) {
            $this->createSurfDeploymentFile(
                $deploymentFile,
                $name,
                $projectName,
                $deploymentHost,
                $deploymentPath
            );
        }
        require $deploymentFile;

        $remoteCommand = new RemoteCommand();
        $remoteCommand->execute(
            new Node($deploymentHost),
            (new ProcessBuilder(['mkdir', '-p', $deploymentPath . '/shared/Configuration']))->getProcess()->getCommandLine()
        );

        $environmentConfig = (new RootConfigFileReader(RootConfig::getRootConfigFile()))->readConfig();
        $envVars = (new EnvConfigFinder())->findEnvVars($environmentConfig);
        foreach ($envVars as $envName => $finds) {
            if ($envName === 'TYPO3_PATH_COMPOSER_ROOT') {
                continue;
            }
            $value = $this->output->ask($envName . ': ');
            foreach ($finds['paths'] as $path) {
                $environmentConfig = ArrayUtility::setValueByPath($environmentConfig, $path, $value, '.');
            }
        }
        $remoteCommand = new RemoteCommand();
        $remoteCommand->execute(
            new Node($deploymentHost),
            'cat > ' . $deploymentPath . '/shared/Configuration/env.yml',
            Yaml::dump($environmentConfig, 5)
        );

        $this->outputLine('<info>Initialized deployment "%s" for target host "%s"', [$projectName, $deploymentHost]);
    }

    /**
     * Execute a TYPO3 Console command on a remote
     *
     * @param string $remote Name of the remote.
     */
    public function execCommand(string $remote)
    {
        try {
            $config = $this->getConfig($remote);
        } catch (\Throwable $e) {
            $this->outputLine('<error>%s</error>', [$e->getMessage()]);
            $this->quit(1);
        }
        // Not nice, but works to get all exceeding arguments and options
        $remoteCommands = array_slice($_SERVER['argv'], 3);
        array_unshift($remoteCommands, $config['path']);

        $callback = function ($type, $data) {
            if ($type === Process::OUT) {
                // Explicitly just echo out for now (avoid Symfony console formatting)
                echo $data;
            } elseif ($type === Process::ERR) {
                $this->output->getSymfonyConsoleOutput()->getErrorOutput()->write($data);
            }
        };

        $processBuilder = new ProcessBuilder($remoteCommands);
        $remoteCommand = new RemoteCommand();
        $exitCode = $remoteCommand->execute(
            new Node($config['hostName'], $config['userName']),
            $processBuilder->getProcess()->getCommandLine(),
            // TODO figure out a better way to determine commands that wait for STDIN
            (($remoteCommands[1] ?? null) === 'database:import') ? STDIN : null,
            $callback
        );
        $this->quit($exitCode);
    }

    private function getConfig(string $remote): array
    {
        if (!file_exists($remoteConfig = getenv('TYPO3_PATH_COMPOSER_ROOT') . '/.remotes/' . $remote . '.yml')) {
            throw new \RuntimeException('Remote does not exist', 1497617449);
        }
        $config = Yaml::parse(file_get_contents($remoteConfig));
        return $this->validateConfig($config);
    }

    private function writeConfig(string $remote, array $config)
    {
        if (!file_exists($remoteConfigFolder = getenv('TYPO3_PATH_COMPOSER_ROOT') . '/.remotes')) {
            GeneralUtility::mkdir($remoteConfigFolder);
        }
        file_put_contents($remoteConfigFolder . '/' . $remote . '.yml', Yaml::dump($config));
    }

    private function validateConfig(array $config): array
    {
        if (
            !is_array($config)
            || !isset($config['hostName'], $config['path'])
        ) {
            throw new \RuntimeException('Remote file is invalid', 1497617449);
        }
        return $config;
    }

    /**
     * @param $deploymentFile
     * @param string $name
     * @param string $projectName
     * @param string $deploymentHost
     * @param string $deploymentPath
     */
    private function createSurfDeploymentFile(
        string $deploymentFile,
        string $name,
        string $projectName,
        string $deploymentHost,
        string $deploymentPath
    ) {
        if (!$projectName) {
            $projectName = strtolower($this->output->ask("<comment>Project Name:</comment> ($name) ", $name));
        }
        if (!$deploymentHost) {
            do {
                $deploymentHost = strtolower($this->output->ask('<comment>Target host name:</comment> '));
            } while (!$deploymentHost);
        }
        if (!$deploymentPath) {
            do {
                $deploymentPath = strtolower($this->output->ask('<comment>Absolute deployment path:</comment> '));
            } while (!$deploymentPath);
        }

        $template = <<<'EOF'
<?php
// Mandatory settings
$projectName = '%1$s';
$deploymentHost = '%2$s';
$deploymentPath = '%3$s';

// Set this if you do not have a remote repository
//$repositoryUrl = 'https://github.com/foo/bar';

// Set this if you want to deploy a different branch than master
//$repositoryBranch = 'master';

// Set this if your composer command is not available in PATH
//$localComposerCommandPath = 'composer';

// Set this, if on remote host the correct PHP binary is not available in PATH
//$remotePhpBinary = '/usr/local/bin/php5-56LATEST-CLI';

if (isset($deployment)) {
    require __DIR__ . '/surf.php.dist';
}

EOF;
        file_put_contents($deploymentFile, sprintf($template, $projectName, $deploymentHost, $deploymentPath));
    }
}
