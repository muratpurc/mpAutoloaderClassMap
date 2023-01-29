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

if (is_file(__DIR__ . '/../../../autoload.php')) {
    // File is within vendor folder
    require_once __DIR__ . '/../../../autoload.php';
} elseif (is_file(__DIR__ . '/../vendor/autoload.php')) {
    // File is outside the vendor folder, e.g. {project_root}/examples
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    die('Could not locate vendor/autoload.php');
}

$file = __DIR__ . '/files/namespace.php';

$classTypeFinder = new \Purc\AutoloaderClassMap\ClassTypeFinder();
$result = $classTypeFinder->findInFile(
    new SplFileInfo($file)
);
print_r($result);
