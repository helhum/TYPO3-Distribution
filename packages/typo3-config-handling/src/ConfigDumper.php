<?php
declare(strict_types=1);
namespace Helhum\TYPO3\ConfigHandling;

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

use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Utility\ArrayUtility;

class ConfigDumper
{
    public function dumpToFile(array $config, string $file, string $comment = '')
    {
        $type = pathinfo($file, PATHINFO_EXTENSION);
        $fileContent = '';
        switch ($type) {
            case 'yml':
            case 'yaml':
                $fileContent .= $this->generateCommentBlock($comment, '#');
                if (!empty($config['imports'])) {
                    $fileContent .= Yaml::dump(['imports' => $config['imports']], 2) . chr(10);
                    unset($config['imports']);
                }
                $fileContent .= Yaml::dump($config, 5);
                break;
            case 'php':
            default:
                $exportedConfig = ArrayUtility::arrayExport($config);
                $fileContent = <<<EOF
<?php
{$this->generateCommentBlock($comment)}
return $exportedConfig;

EOF;
        }

        file_put_contents(
            $file,
            $fileContent
        );
    }

    private function generateCommentBlock(string $comment, string $commentChar = '//'): string
    {
        if (empty($comment)) {
            return '';
        }
        return implode(
            chr(10),
            array_map(function($line) use ($commentChar) {
                return $commentChar . ' ' . $line;
            }, explode(chr(10), $comment))
        );
    }
}
