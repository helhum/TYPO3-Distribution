<?php
namespace Helhum\TYPO3\Distribution\Configuration;

/*
 * This file is part of the helhum TYPO3 distribution package.
 *
 * (c) Helmut Hummel <info@helhum.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Core\ApplicationContext;
use TYPO3\CMS\Core\Utility\ArrayUtility;

/**
 * Class ConfigurationLoader
 */
class ConfigurationLoader
{
    /**
     * @var ApplicationContext
     */
    protected $applicationContext;

    /**
     * @var string
     */
    protected $configDir;

    /**
     * ConfigurationLoader constructor.
     *
     * @param ApplicationContext $applicationContext
     * @param string $configDir
     */
    public function __construct(ApplicationContext $applicationContext, $configDir)
    {
        $this->applicationContext = $applicationContext;
        $this->configDir = $configDir;
    }

    public function load()
    {
        $configName = $this->getContextSlug();
        $configType = 'php';
        $this->includeIfExists("{$this->configDir}/default.{$configType}");
        $this->includeIfExists("{$this->configDir}/{$configName}.{$configType}");
        $this->loadConfigurationFromEnvironment();
        $this->includeIfExists("{$this->configDir}/override.{$configType}");
    }

    protected function getContextSlug() {
        return strtr(strtolower($this->applicationContext), '/', '-');
    }

    /**
     * @param string $file
     */
    protected function includeIfExists($file) {
        file_exists($file) && include $file;
    }

    /**
     * Dynamically loads TYPO3 specific environment variable into TYPO3_CONF_VARS
     * Env vars must start with TYPO3__ and separate sections with __
     *
     * Example: TYPO3__DB__database will be loaded into $GLOBALS['TYPO3_CONF_VARS']['DB']['database']
     */
    protected function loadConfigurationFromEnvironment()
    {
        foreach ($_ENV as $name => $value) {
            if (strpos($name, 'TYPO3__') !== 0) {
                continue;
            }
            $GLOBALS['TYPO3_CONF_VARS'] = ArrayUtility::setValueByPath(
                $GLOBALS['TYPO3_CONF_VARS'],
                str_replace('__', '/', substr($name, 7)),
                $value
            );
        }
    }
}