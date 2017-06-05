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

use Helhum\ConfigLoader\Processor\ConfigProcessorInterface;
use Helhum\ConfigLoader\Reader\ConfigReaderInterface;

class ProcessedConfigFileReader implements ConfigReaderInterface
{
    /**
     * @var ConfigReaderInterface
     */
    private $reader;

    /**
     * @var ConfigProcessorInterface
     */
    private $processor;

    public function __construct(ConfigReaderInterface $reader, ConfigProcessorInterface $processor)
    {
        $this->reader = $reader;
        $this->processor = $processor;
    }

    public function hasConfig()
    {
        return $this->reader->hasConfig();
    }

    public function readConfig()
    {
        return $this->processor->processConfig($this->reader->readConfig());
    }
}
