<?php
declare(strict_types=1);
namespace Helhum\Typo3ConfigHandling\Reader;

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

use Helhum\ConfigLoader\Reader\ConfigReaderInterface;
use Helhum\ConfigLoader\Reader\EnvironmentReader;
use Helhum\ConfigLoader\Reader\PhpFileReader;
use Helhum\ConfigLoader\Reader\YamlReader;

class RootConfigReader implements ConfigReaderInterface
{
    /**
     * @var ConfigReaderInterface
     */
    private $reader;

    /**
     * @var string
     */
    private $resourceFile;

    public function __construct(string $resourceFile, string $type = null)
    {
        $this->resourceFile = $resourceFile;
        $this->reader = $this->createReader($resourceFile, $type);
    }

    public function hasConfig()
    {
        return $this->reader->hasConfig();
    }

    public function readConfig()
    {
        return $this->processConfig($this->reader->readConfig());
    }

    /**
     * @param array $config
     * @throws \InvalidArgumentException
     * @return array
     */
    private function processConfig(array $config)
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
            $reader = $this->createProcessingReader($import['resource'], $import['type'] ?? null);
            $ignoreErrors = $import['type'] ?? false;
            if (!$ignoreErrors && !$reader->hasConfig()) {
                throw new \RuntimeException(sprintf('Could not import mandatory resource "%s" in "%s"', $import['resource'], $this->resourceFile), 1496585828);
            }
            $importedConfig = array_replace_recursive($importedConfig, $reader->readConfig());
        }
        unset($config['imports']);
        return array_replace_recursive($config, $importedConfig);
    }

    private function createProcessingReader(string $resource, string $type = null): ConfigReaderInterface
    {
        if ($type !== 'env') {
            $resource = $this->makeAbsolute($resource);
        }
        return new self($resource, $type);
    }

    private function createReader(string $resource, string $type = null): ConfigReaderInterface
    {
        $type = $type ?: pathinfo($resource, PATHINFO_EXTENSION);
        switch ($type) {
            case 'yml':
                return new YamlReader($resource);
            case 'env':
                return new EnvironmentReader($resource);
            case 'glob':
                return new CollectionReader($this->createReaderCollection($resource));
            default:
                return new PhpFileReader($resource);
        }
    }

    private function createReaderCollection(string $resource, string $type = null)
    {
        $readers = [];
        $configFiles = glob($resource);
        foreach ($configFiles as $settingsFile) {
            $readers[] = $this->createProcessingReader($settingsFile, $type);
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
