<?php
/**
 * Example autoload implementation which uses the generated class map configuration
 *
 * This file contains the class definition and the registration of the autoloader 
 * together for the sake of simplicity. This is up to you to separate the logic to 
 * your requirements/wishes.
 *
 * Usage:
 * <pre>
 * // include this file, e. g. at the beginning of your scripts or in a bootstrap 
 * // implementation
 * require_once('/path/to/autoloader.php');
 * // ... then use your classes ...
 * </pre>
 *
 * @category    Development
 * @package 	mpAutoloaderClassMap
 * @author		Murat Purc <murat@purc.de>
 * @copyright   Copyright (c) 2009-2011 Murat Purc (http://www.purc.de)
 * @license     http://www.gnu.org/licenses/gpl-2.0.html - GNU General Public License, version 2
 * @version     $Id$
 */


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
            // NOTE: Adapt the path to the class map configuration file or make it configurable!
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
