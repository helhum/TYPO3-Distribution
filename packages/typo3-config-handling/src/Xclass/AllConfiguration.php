<?php
declare(strict_types=1);
namespace Helhum\TYPO3\ConfigHandling\Xclass;

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
use Helhum\TYPO3\ConfigHandling\RootConfig;

class AllConfiguration extends \TYPO3\CMS\Install\Controller\Action\Tool\AllConfiguration
{
    private $backup;

    protected function setUpConfigurationData()
    {
        $this->overrideConfig();
        try {
            return parent::setUpConfigurationData();
        } finally {
            $this->restoreConfig();
        }
    }

    protected function updateLocalConfigurationValues()
    {
        $this->overrideConfig();
        try {
            return parent::updateLocalConfigurationValues();
        } finally {
            $this->restoreConfig();
        }
    }

    private function overrideConfig()
    {
        $this->backup = $GLOBALS['TYPO3_CONF_VARS'];
        $GLOBALS['TYPO3_CONF_VARS'] = array_replace_recursive(
            $GLOBALS['TYPO3_CONF_VARS'],
            (new RootConfigFileReader(RootConfig::getRootConfigFile()))->readConfig()
        );
    }

    private function restoreConfig()
    {
        $GLOBALS['TYPO3_CONF_VARS'] = $this->backup;
        $this->backup = null;
    }
}
