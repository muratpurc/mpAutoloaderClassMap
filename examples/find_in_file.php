<?php
declare(strict_types=1);

/**
 * Example for finding tokens in a single file.
 *
 * Usage:
 * ------
 * 1. Modify path to file (see `$file`) to your requirements
 * 2. Call this script from command line as follows:
 *     $ php find_in_file.php
 * 3. See output
 *
 * @category   Development
 * @package    AutoloaderClassMap
 * @author     Murat Purç <murat@purc.de>
 * @copyright  Murat Purç (http://www.purc.de)
 * @license    http://www.gnu.org/licenses/gpl-2.0.html - GNU General Public License, version 2
 */

require_once __DIR__ . '/../vendor/autoload.php';

$file = __DIR__ . '/files/namespace.php';

$classTypeFinder = new \Purc\AutoloaderClassMap\ClassTypeFinder();
$result = $classTypeFinder->findInFile(
    new SplFileInfo($file)
);
print_r($result);
