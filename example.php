<?php
declare(strict_types=1);

/**
 * Example usage for autoloader class map file generator.
 *
 * Parses full PEAR directory and creates a class map file!
 *
 * Usage:
 * ------
 * 1. Modify settings to your requirements
 * 2. Call this script from command line as follows:
 *     $ php example.php
 * 3. Check created class map file
 *
 * @category   Development
 * @package    AutoloaderClassMap
 * @author     Murat Purç <murat@purc.de>
 * @copyright  Murat Purç (http://www.purc.de)
 * @license    http://www.gnu.org/licenses/gpl-2.0.html - GNU General Public License, version 2
 */


################################################################################
##### Initialization/Settings

require_once __DIR__ . '/vendor/autoload.php';

$classMapPath = __DIR__ . '/classmap.configuration.php';

// Set the environment variable, PURC_AUTOLOADER_CLASS_MAP_FILE, you can set the global variable alternatively, e.g.
// $GLOBALS['PURC_AUTOLOADER_CLASS_MAP_FILE'] = $classMapPath;
putenv('PURC_AUTOLOADER_CLASS_MAP_FILE="' . $classMapPath . '"');

// the destination file where the class map configuration should be written in
$destinationFile = $classMapPath;

// list of paths from where all class/interface names should be found
// NOTE: Path depends on used environment and should be adapted
$pathsToParse = [
    __DIR__ .'/_data/class-map-generator-fixtures'
];

// list to collect class maps
$classMapList = [];

// class file finder options
$options = [
    // exclude following folder names
    'excludeDirs'       => ['temp', 'session', 'docs', 'tests'],
    // no specific file exclusion
    'excludeFiles'      => [],
    // parse all files with '.php' extension
    'extensionsToParse' => ['.php'],
    // disable debugging
    'enableDebug'       => false,
];


################################################################################
##### Process

// Collect all found class/interface names with their paths
$classTypeFinder = new \Purc\AutoloaderClassMap\ClassTypeFinder($options);
foreach ($pathsToParse as $pos => $dir) {
    $classMap = $classTypeFinder->findInDir(new SplFileInfo($dir), true);
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

