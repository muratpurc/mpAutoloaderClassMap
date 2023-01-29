<?php
declare(strict_types=1);

/**
 * Contains token extractor.
 *
 * @category   Development
 * @package    AutoloaderClassMap
 * @author     Murat Purç <murat@purc.de>
 * @copyright  Murat Purç (https://www.purc.de)
 * @license    https://www.gnu.org/licenses/gpl-2.0.html - GNU General Public License, version 2
 */

namespace Purc\AutoloaderClassMap;

use Exception;
use SplFileInfo;

if (!defined('T_NAME_QUALIFIED')) {
    // T_NAME_QUALIFIED is available since PHP 8.0
    define('T_NAME_QUALIFIED', 314);
}
if (!defined('T_ENUM')) {
    // T_ENUM is available since PHP 8.1.0
    define('T_ENUM', 336);
}

/**
 * Class to find tokens
 *
 * @category  Development
 * @package   AutoloaderClassMap
 * @author    Murat Purç <murat@purc.de>
 */
class TokenExtractor
{

    /**
     * @var SplFileInfo
     */
    private $fileInfo;

    /**
     * @var array
     */
    private $allToken;

    /**
     * @param SplFileInfo $fileInfo
     * @throws Exception
     */
    public function __construct(SplFileInfo $fileInfo)
    {
        $this->fileInfo = $fileInfo;
        if (!$this->fileInfo->isFile() || !$this->fileInfo->isReadable()) {
            throw new Exception('Invalid/Not readable file ' . $this->fileInfo->getPathname());
        }
    }

    public function extractToken(): array
    {
        Util::addDebugMessage(
            'extractToken: Processing file ' . $this->fileInfo->getPathname()
            . ' (realpath: ' . $this->fileInfo->getRealPath() . ')'
        );

        $typeToken = [];
        $namespace = '';
        $skipPos = null;

        $this->allToken = $this->getAllToken();

        foreach ($this->allToken as $p => $token) {
            // Check for anonymous class declarations (e.g. `new class`), to skip them from collecting
            if ($token[0] === T_NEW) {
                $classPos = $p + 2;
                if (isset($this->allToken[$classPos]) && $this->allToken[$classPos][0] === T_CLASS
                    && $this->allToken[$classPos][1] === 'class')
                {
                    $skipPos = $classPos;
                    continue;
                }
            }
            if ($skipPos === $p) {
                $skipPos = null;
                continue;
            }

            // Extract namespace
            if ($token[0] === T_NAMESPACE) {
                $namespace = $this->findNamespaceStringFromPosition($p + 1);
                continue;
            }

            // Extract interface, trait, class and enum
            if (in_array($token[0], [T_INTERFACE, T_TRAIT, T_CLASS, T_ENUM])) {
                $name = $this->findStringTokenFromPosition($p + 1);
                if (!empty($name)) {
                    $typeToken[$namespace . $name] = Util::normalizePathSeparator(
                        $this->fileInfo->getRealPath()
                    );
                }
            }
        }

        return $typeToken;
    }

    /**
     * Finds first string token representing interface, class or enum name,
     * within the token list from the given position.
     *
     * @param int $pos The position to start the search from
     * @return mixed|string|null
     */
    protected function findStringTokenFromPosition(int $pos)
    {
        $length = count($this->allToken);
        for ($i = $pos; $i < $length; $i++) {
            if (in_array($this->allToken[$i][0], [T_INTERFACE, T_TRAIT, T_CLASS, T_ENUM])) {
                return null;
            }
            if (in_array($this->allToken[$i][0], [T_STRING, T_NAME_QUALIFIED])) {
                return $this->allToken[$i][1];
            }
        }

        return null;
    }

    /**
     * Returns namespace definition from the given position.
     *
     * @param int $pos The position to start the search from
     * @return string
     */
    protected function findNamespaceStringFromPosition(int $pos): string
    {
        $namespace = '';
        $length = count($this->allToken);
        for ($i = $pos; $i < $length; $i++) {
            if (in_array($this->allToken[$i][0], [T_INTERFACE, T_TRAIT, T_CLASS, T_ENUM, T_USE])) {
                break;
            }
            if (in_array($this->allToken[$i][0], [T_STRING, T_NAME_QUALIFIED, T_NS_SEPARATOR])) {
                $namespace .= $this->allToken[$i][1];
            }
        }

        return $namespace ? $namespace . '\\' : '';
    }

    /**
     * Extracts all tokens from file to parse.
     *
     * @return array
     */
    protected function getAllToken(): array
    {
        $contents = @php_strip_whitespace($this->fileInfo->getRealPath());
        if (empty($contents)) {
            $contents = file_get_contents($this->fileInfo->getRealPath());
        }

        $pattern = '/(<<<)([\'"]?)(\w+)([\'"]?)(.*?)\3(;)/s';
        $replace = '${1}${2}${3}${4}' . PHP_EOL . '*replaced heredoc/nowdoc*' . PHP_EOL . '${3}${6}';
        $contents = preg_replace($pattern, $replace, $contents);

        return token_get_all($contents);
    }

}
