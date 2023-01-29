<?php
declare(strict_types=1);

/**
 * Example autoload implementation which uses the generated class map configuration
 *
 * This file contains the class definition and the registration of the autoloader
 * together for the sake of simplicity. This is up to you to separate the logic to
 * your requirements/wishes.
 *
 * Usage:
 * 1. Include this file, e.g. at the beginning of your scripts or in a bootstrap process
 * <pre>
 * require_once '{project_root}/vendor/purc/autoloader-class-map/src/Autoloader.php';
 * // ... then use your classes ...
 * </pre>
 * 2. or define additional autoloader in your composer.json
 * <pre>
 * {
 *     "autoload": {
 *         "files": ["vendor/purc/autoloader-class-map/src/Autoloader.php"]
 *     }
 * }
 * </pre>
 *
 * @category    Development
 * @package     AutoloaderClassMap
 * @author      Murat Purç <murat@purc.de>
 * @copyright   Murat Purç (http://www.purc.de)
 * @license     http://www.gnu.org/licenses/gpl-2.0.html - GNU General Public License, version 2
 */

namespace Purc\AutoloaderClassMap;

use Exception;

/**
 * Simple autoloader class
 */
class Autoloader
{
    /**
     * Class map configuration
     * @var string[]
     */
    protected $classMap;

    /**
     * Autoloader constructor, loads the passed class map file.
     *
     * @param string $classMapPath Path to class map file to include
     * @return void
     * @throws Exception if autoloader couldn't set class map configuration in initial call
     */
    public function __construct(string $classMapPath)
    {
        // NOTE: Adapt the path to the class map configuration file or make it configurable!
        $this->classMap = include_once($classMapPath);
        if (!is_array($this->classMap) || empty($this->classMap)) {
            throw new Exception(__CLASS__ . ": Couldn't load classmap configuration");
        }

        spl_autoload_register([$this, 'autoload']);
    }

    /**
     * Autoload implementation, loads the passed class name from the class map.
     *
     * @param string $name The required class name
     */
    public function autoload(string $name)
    {
        if (isset($this->classMap[$name])) {
            require_once $this->classMap[$name];
        }
    }

}

(function() {
    $file = getenv('PURC_AUTOLOADER_CLASS_MAP_FILE');
    if (empty($file)) {
        $file = $GLOBALS['PURC_AUTOLOADER_CLASS_MAP_FILE'] ?? '';
    }
    new Autoloader($file);
})();
