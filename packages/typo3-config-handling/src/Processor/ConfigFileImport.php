<?php
declare(strict_types=1);
namespace Helhum\Typo3ConfigHandling\Processor;

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

use Helhum\ConfigLoader\Processor\ConfigProcessorInterface;
use Helhum\ConfigLoader\Reader\ConfigReaderInterface;
use Helhum\ConfigLoader\Reader\EnvironmentReader;
use Helhum\ConfigLoader\Reader\PhpFileReader;
use Helhum\ConfigLoader\Reader\YamlReader;
use Helhum\Typo3ConfigHandling\Reader\CollectionReader;
use Helhum\Typo3ConfigHandling\Reader\ProcessedConfigFileReader;

class ConfigFileImport implements ConfigProcessorInterface
{
    /**
     * @var string
     */
    private $resourceFile;

    public function __construct(string $resourceFile)
    {
        $this->resourceFile = $resourceFile;
    }

    /**
     * @param array $config
     * @throws \InvalidArgumentException
     * @return array
     */
    public function processConfig(array $config)
    {
        if (!isset($config['imports'])) {
            return $config;
        }
        if (!is_array($config['imports'])) {
            throw new \InvalidArgumentException(sprintf('The "imports" key should contain an array in "%s"', $this->resourceFile), 1496583179);
        }
        $importedConfig = [];
        foreach ($config['imports'] as $import) {
            if (!is_array($import)) {
                throw new \InvalidArgumentException(sprintf('The "imports" must be an array in "%s"', $this->resourceFile), 1496583180);
            }
            $reader = $this->createReader($import['resource'], $import['type'] ?? null);
            if (isset($import['ignore_errors']) && !$import['ignore_errors'] && !$reader->hasConfig()) {
                throw new \RuntimeException(sprintf('Could not import mandatory resource "%s" in "%s"', $import['resource'], $this->resourceFile), 1496585828);
            }
            $importedConfig = array_replace_recursive($importedConfig, $reader->readConfig());
        }
        unset($config['imports']);
        return array_replace_recursive($config, $importedConfig);
    }

    /**
     * @param string $resource
     * @return ConfigReaderInterface
     */
    private function createReader(string $resource, string $type = null)
    {
        $type = $type ?: pathinfo($resource, PATHINFO_EXTENSION);
        $resourceFile = $this->makeAbsolute($resource);
        switch ($type) {
            case 'yml':
                return new ProcessedConfigFileReader(
                    new YamlReader($resourceFile),
                    new self($resourceFile)
                );
            case 'env':
                return new EnvironmentReader($resource);
            case 'glob':
                return new CollectionReader($this->createReaderCollection($resourceFile));
            default:
                return new ProcessedConfigFileReader(
                    new PhpFileReader($resourceFile),
                    new self($resourceFile)
                );
        }
    }

    private function createReaderCollection(string $resource, string $type = null)
    {
        $readers = [];
        $configFiles = glob($resource);
        foreach ($configFiles as $settingsFile) {
            $readers[] = $this->createReader($settingsFile, $type);
        }
        return $readers;
    }

    private function makeAbsolute(string $path)
    {
        if ($path[0] === '/' || $path[1] === ':') {
            return $path;
        }
        return dirname($this->resourceFile) . '/' . $path;
    }
}
