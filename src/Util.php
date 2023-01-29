<?php
declare(strict_types=1);

/**
 * Contains util class.
 *
 * @category   Development
 * @package    AutoloaderClassMap
 * @author     Murat Purç <murat@purc.de>
 * @copyright  Murat Purç (https://www.purc.de)
 * @license    https://www.gnu.org/licenses/gpl-2.0.html - GNU General Public License, version 2
 */

namespace Purc\AutoloaderClassMap;

use Exception;

/**
 * Class with helper functions.
 *
 * @category  Development
 * @package   AutoloaderClassMap
 * @author    Murat Purç <murat@purc.de>
 */
class Util
{

    /**
     * Flag to enable debugging, all messages will be collected in property _debugMessages, if enabled
     *
     * @var  bool
     */
    private static $_enableDebug = false;

    /**
     * List of debugging messages, will e filled, if debugging is active
     *
     * @var  string[]
     */
    protected static $_debugMessages = [];

    /**
     * @return bool
     */
    public static function isEnableDebug(): bool
    {
        return self::$_enableDebug;
    }

    /**
     * @param bool $enableDebug
     */
    public static function setEnableDebug(bool $enableDebug)
    {
        self::$_enableDebug = $enableDebug;
    }

    /**
     * @return string[]
     */
    public static function getDebugMessages(): array
    {
        return self::$_debugMessages;
    }

    /**
     * Returns debug messages in a formatted way.
     *
     * @param string $delimiter Delimiter between each message
     * @param string $wrap String with %s type specifier used to wrap all
     *                     messages
     * @return  string  Formatted string
     * @throws Exception if the given wrap does not contain %s
     */
    public static function getFormattedDebugMessages(string $delimiter = "\n", string $wrap = '%s'): string
    {
        if (strpos($wrap, '%s') === false) {
            throw new Exception('Missing type specifier %s in parameter wrap!');
        }
        $messages = implode($delimiter, self::getDebugMessages());
        return sprintf($wrap, $messages);
    }

    /**
     * Adds passed message to debug list, if debugging is enabled
     *
     * @param string $message
     */
    public static function addDebugMessage(string $message)
    {
        if (self::isEnableDebug()) {
            self::$_debugMessages[] = $message;
        }
    }

    /**
     * Replaces windows style directory separator (backslash against slash)
     *
     * @param   string  $path
     * @return  string
     */
    public static function normalizePathSeparator(string $path): string
    {
        if (DIRECTORY_SEPARATOR == '\\') {
            $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
        }
        return $path;
    }

}
