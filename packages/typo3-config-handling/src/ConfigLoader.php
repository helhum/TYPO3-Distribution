<?php
declare(strict_types=1);
namespace Helhum\Typo3ConfigHandling;

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

use Helhum\ConfigLoader\ConfigurationLoader;
use Helhum\ConfigLoader\Reader\PhpFileReader;
use Helhum\Typo3ConfigHandling\Processor\ConfigFileImportProcessor;
use Helhum\Typo3ConfigHandling\Reader\ProcessedConfigFileReader;

class ConfigLoader
{
    /**
     * @var ConfigurationLoader
     */
    private $loader;

    /**
     * Override this to match the config file structure you like
     *
     * @param string $confDir
     * @param string $context
     * @return ConfigLoader
     */
    public static function create(string $confDir, string $context = 'prod'): self
    {
        return new self($confDir . '/' . $context . '.settings.php');
    }

    public function __construct(string $configFile)
    {
        $this->loader = $this->buildLoader($configFile);
    }

    public function populate()
    {
        $GLOBALS['TYPO3_CONF_VARS'] = array_replace_recursive(
            $GLOBALS['TYPO3_CONF_VARS'],
            $this->load()
        );
    }

    public function load(): array
    {
        return $this->loader->load();
    }

    private function buildLoader(string $configFile): ConfigurationLoader
    {
        return new ConfigurationLoader(
            [
                new ProcessedConfigFileReader(
                    new PhpFileReader($configFile),
                    new ConfigFileImportProcessor($configFile)
                ),
            ],
            [
                new ExtensionSettingsSerializer(),
            ]
        );
    }
}
