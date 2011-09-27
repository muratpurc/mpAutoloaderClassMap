=mpAutoloaderClassMap=

Is able to parse several defined directories/files for existing class type tokens (class names, abstract class names and interface names) and to generate a class map configuration file which is usable for a autoloader implementation.

----

=Reason=
Solving dependencies, loading required class files, in PHP can be done via different ways.

*1. PSR-0: Mapping class name to file system location*
{{{
// example:
Location:  /path/to/your/lib/Com/Foobar/Helloworld.php
Classname: Com_Foobar_Helloworld

// usage:
// a.) first you have to register a autoloader which maps
// Com_Foobar_Helloworld to Com/Foobar/Helloworld.php within
// /path/to/your/lib/
// b.) then you can use something like
$foo = new Com_Foobar_Helloworld();
// or
echo (class_exists('Com_Foobar_Helloworld')) ? 'yes' : 'no';
}}}

*2. Includes: include/require statements, whenever the class is needed. This is the old way which still works fine. Require the file each time when needed.*
{{{
// example:
Location:  /path/to/your/lib/Com/Foobar/Helloworld.php
Classname: Com_Foobar_Helloworld

// usage:
require_once('/path/to/your/lib/Com/Foobar/Helloworld.php');
$foo = new Com_Foobar_Helloworld();
// or
if (!class_exists('Com_Foobar_Helloworld')) {
    require_once('/path/to/your/lib/Com/Foobar/Helloworld.php');
    echo 'Now Com_Foobar_Helloworld exists';
}
}}}

*3. Class map: This solution requires a configuration file which contains a reference to the file on the file system for each available class.*
{{{
// example:
Location:  /path/to/your/lib/Com/Foobar/Helloworld.php
Classname: Com_Foobar_Helloworld

// Class map configuration file having a content like
return array(
    ...
    'Com_Foobar_Helloworld' => '/path/to/your/lib/Com/Foobar/Helloworld.php',
    'Com_Foobar_Response_Html' => '/path/to/your/lib/Com/Foobar/Response/Html.php',
    ...
);

// usage:
// a.) first you have to register a autoloader which loads required classes by
// using the class map array structure.
// b.) then you can use something like
$foo = new Com_Foobar_Helloworld();
// or
echo (class_exists('Com_Foobar_Helloworld')) ? 'yes' : 'no';
}}}


If the project is not PSR-0 compatible or there is no way to map automatically required class name to file system location and you want to get rid of all require/include statements, using a class map configuration is probably the convenient solution. And this is the main idea behind this tool, it creates a class map configuration file by fetching all found classes/interfaces within a defined path.

----

=Options=

There a some options to control class map creation described as follows:

*excludeDirs:*

(array) List of directories to ignore (note: is case insensitive)

Default value is {{{array('.svn', '.cvs')}}}


*excludeFiles:*

(array) List of files to ignore, regexp pattern is also accepted (note: is case insensitive)

Default value is {{{array('/^~*.\.php$/', '/^~*.\.inc$/')}}}


*extensionsToParse:*

(array) List of file extensions to parse (note: is case insensitive)

Default value is {{{array('.php', '.inc')}}}


*enableDebug:*

(bool) Flag to enable debugging, collects some helpful state information's

Default value is {{{false}}}


=Usage=

==Creating a class map configuration==

Create a script named "example.php" with following content or use [http://code.google.com/p/mpautoloaderclassmap/source/browse/trunk/example.php example.php] as a blueprint.

{{{
<?php

################################################################################
##### Initialization/Settings

// create a page context class, better than spamming global scope
$context = new stdClass();

// current path
$context->currentPath = str_replace('\\', '/', realpath(dirname(__FILE__) . '/')) . '/';

// the destination file where the class map configuration should be written in
$context->destinationFile = $context->currentPath . '/classmap.configuration.php';

// list of paths from where all class/interface names should be found
$context->pathsToParse = array(
    '/path/to/my/project'
);

// list to collect class maps
$context->classMapList = array();

// class file finder options
$context->options = array(
    // exclude following folder names
    'excludeDirs'       => array('temp', 'session', 'docs', 'tests'),
    // no specific file exclusion
    'excludeFiles'      => array(),
    // parse all files with '.php' extension
    'extensionsToParse' => '.php',
    // disbale debugging
    'enableDebug'       => false,
);


################################################################################
##### Process

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
}}}

This is it. Now run this script from the command line by typing following command:
{{{
$ php example.php
}}}
It should generate the class map configuration file "classmap.configuration.php" in same/configured directory.

==Using the class map configuration file with an autoloader==
You can use/extend a existing autoloader implementation or setup your own. The example below describes one simple way how to do implement a autoloader which uses the class map configuration:
{{{
/**
 * Simple autoloader class
 */
class myAutoloader
{
    /**
     * Class map configuration
     * @var array
     */
    protected static $_classMap;

    /**
     * Autoloader implementation
     * @param string $name The required class name
     * @throws Exception if autoloader couldn't set class map configuration in initial call
     */
    public static function autoload($name)
    {
        if (!isset(self::$_classMap)) {
            self::$_classMap = include_once('/path/to/classmap.configuration.php');
            if (!is_array(self::$_classMap) || empty(self::$_classMap)) {
                throw Exception(__CLASS__ . ": Couldn't load classmap configuration");
            }
        }
        if (isset(self::$_classMap) && isset(self::$_classMap[$name])) {
            require_once(self::$_classMap[$name]);
        }
    }
}

// Register your autoloader implementation
spl_autoload_register(array('myAutoloader', 'autoload'));

// Now use your classes ...
}}}
This example, you'll fin it also at [http://code.google.com/p/mpautoloaderclassmap/source/browse/trunk/autoloader.php autoloader.php] contains the class definition and the registration of the autoloader together for the sake of simplicity. This is up to you to separate the logic to your requirements/wishes.
