<?php
declare(strict_types=1);

/**
 * Example usage for autoloader class map file generator.
 *
 * Parses defined directory/directories and creates a class map file!
 *
 * Usage:
 * ------
 * 1. Modify settings to your requirements
 * 2. Call this script from command line as follows:
 *     $ php class_map_generation.php
 * 3. Check created class map file
 *
 * @category   Development
 * @package    AutoloaderClassMap
 * @author     Murat Purç <murat@purc.de>
 * @copyright  Murat Purç (http://www.purc.de)
 * @license    http://www.gnu.org/licenses/gpl-2.0.html - GNU General Public License, version 2
 */


// ##### Initialization/Settings

require_once __DIR__ . '/../vendor/autoload.php';

// Path and name of class map file to store the class map definitions.
// Note: PHP needs write-permissions for the folder!
$classMapPath = __DIR__ . '/classmap.configuration.php';

// Set the environment variable, PURC_AUTOLOADER_CLASS_MAP_FILE, you can set the global variable alternatively, e.g.
// $GLOBALS['PURC_AUTOLOADER_CLASS_MAP_FILE'] = $classMapPath;
putenv('PURC_AUTOLOADER_CLASS_MAP_FILE="' . $classMapPath . '"');

// The destination file where the class map configuration should be written in
$destinationFile = $classMapPath;

// List of paths from where all class/interface names should be found
// NOTE: Path depends on used environment and should be adapted
$pathsToParse = [
    __DIR__ . '/files'
];

// List to collect class maps
$classMapList = [];

// Class file finder options
$options = [
    // exclude following folder names
    'excludeDirs' => ['temp', 'session', 'docs', 'tests'],
    // no specific file exclusion
    'excludeFiles' => [],
    // parse all files with '.php' extension
    'extensionsToParse' => ['.php'],
    // disable debugging
    'enableDebug' => false,
];


// ##### Process

// Collect all found class/interface names with their paths
$classTypeFinder = new \Purc\AutoloaderClassMap\ClassTypeFinder($options);
foreach ($pathsToParse as $pos => $dir) {
    $classMap = $classTypeFinder->findInDir(new SplFileInfo($dir));
    if ($classMap) {
        $classMapList = array_merge($classMapList, $classMap);
    }
}
ksort($classMapList);

// Uncomment following line to get some debug messages
#echo $classTypeFinder->getFormattedDebugMessages();

// Write the class map configuration
$classMapCreator = new \Purc\AutoloaderClassMap\FileCreator();
$classMapCreator->create($classMapList, $destinationFile);
