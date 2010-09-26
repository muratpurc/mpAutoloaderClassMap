<?php
/**
 * Example usage for autoloader class map file generator.
 *
 * Parses full PEAR directory and creates a class map file!
 *
 * Usage:
 * ------
 * 1. Modifiy settings to youre requriements
 * 2. call this cript from command line as follows:
 *     $ php example.php
 * 3. Check created class map file
 *
 * @category    Development
 * @package 	mpAutoloaderClassMap
 * @author		Murat Purc <murat@purc.de>
 * @copyright   Copyright (c) 2009-2010 Murat Purc (http://www.purc.de)
 * @license     http://www.gnu.org/licenses/gpl-2.0.html - GNU General Public License, version 2
 * @version     $Id$
 */


################################################################################
##### Initialization/Settings

// create a page context class, better than spamming global scope
$context = new stdClass();

// current path
$context->currentPath = str_replace('\\', '/', realpath(dirname(__FILE__) . '/')) . '/';

// the destination file where the class map configuration should be written in
$context->destinationFile = $context->currentPath . '/classmap.configuration.php';

// list of paths from where all class/interface names should be found
// NOTE: Path depends on used environment and should be adapted
$context->pathsToParse = array(
    'C:/Progs/xampp/php/PEAR/'
);

// list to collect class maps
$context->classMapList = array();

// class file finder options
$context->options = array(
    'excludeDirs'       => array('.channels', '.registry', 'data', 'docs', 'test', 'tests'),
    'excludeFiles'      => array(),
    'extensionsToParse' => '.php',
    'enableDebug'       => false,
);


################################################################################
##### Proccess

// include required classes
include_once($context->currentPath . 'lib/mpClassTypeFinder.php');
include_once($context->currentPath . 'lib/mpClassMapFileCreator.php');

// collect all found class/interface names with their paths
$context->classTypeFinder = new mpClassTypeFinder($context->options);
foreach ($context->pathsToParse as $pos => $dir) {
    $classMap = $context->classTypeFinder->findInDir(new SplFileInfo($dir), true);
    if ($classMap) {
        $context->classMapList = array_merge($context->classMapList, $classMap);
    }
}

// uncomment following line to get some debug messages
#echo $context->classTypeFinder->getFormattedDebugMessages();


// write the class map configuration
$context->classMapCreator = new mpClassMapFileCreator();
$context->classMapCreator->create($context->classMapList, $context->destinationFile);


// cleanup
unset($context);