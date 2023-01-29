<?php
declare(strict_types=1);

/**
 * Contains class type token finder.
 *
 * @category   Development
 * @package    AutoloaderClassMap
 * @author     Murat Purç <murat@purc.de>
 * @copyright  Murat Purç (https://www.purc.de)
 * @license    https://www.gnu.org/licenses/gpl-2.0.html - GNU General Public License, version 2
 */

namespace Purc\AutoloaderClassMap;

use DirectoryIterator;
use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Class to find class type tokens
 *
 * @category  Development
 * @package   AutoloaderClassMap
 * @author    Murat Purç <murat@purc.de>
 */
class ClassTypeFinder
{
    /**
     * List of directories to ignore (note: is case-insensitive)
     * @var  string[]
     */
    protected $excludeDirs = ['.svn', '.cvs'];

    /**
     * List of files to ignore, regex pattern is also accepted (note: is case insensitive)
     * @var  string[]
     */
    protected $excludeFiles = ['/^~*.\.php$/', '/^~*.\.inc$/'];

    /**
     * List of file extensions to parse (note: is case-insensitive)
     * @var  string[]
     */
    protected $extensionsToParse = ['.php', '.inc'];

    /**
     * Initializes class with passed options
     *
     * @param   array  $options  Associative options array as follows:
     *                           - excludeDirs: (string[])  List of directories to exclude, optional.
     *                               Default values are '.svn' and '.cvs'.
     *                           - excludeFiles: (string[])  List of files to exclude, optional.
     *                               Default values are '/^~*.\.php$/' and '/^~*.\.inc$/'.
     *                           - extensionsToParse: (string[])  List of file extensions to parse, optional.
     *                               Default values are '.php' and '.inc'.
     *                           - enableDebug: (bool)  Flag to enable debugging, optional.
     *                               Default value is false.
     */
    public function __construct(array $options = [])
    {
        if (isset($options['excludeDirs']) && is_array($options['excludeDirs'])) {
            $this->setExcludeDirs($options['excludeDirs']);
        }
        if (isset($options['excludeFiles']) && is_array($options['excludeFiles'])) {
            $this->setExcludeFiles($options['excludeFiles']);
        }
        if (isset($options['extensionsToParse']) && is_array($options['extensionsToParse'])) {
            $this->setExtensionsToParse($options['extensionsToParse']);
        }
        if (isset($options['enableDebug']) && is_bool($options['enableDebug'])) {
            Util::setEnableDebug(true);
        }
    }

    /**
     * Sets directories to exclude
     *
     * @param   string[]  $excludeDirs
     * @return  void
     */
    public function setExcludeDirs(array $excludeDirs)
    {
        $this->excludeDirs = $excludeDirs;
    }

    /**
     * Returns list of directories to exclude
     *
     * @return  string[]
     */
    public function getExcludeDirs(): array
    {
        return $this->excludeDirs;
    }

    /**
     * Sets files to exclude
     *
     * @param   string[]  $excludeFiles  Feasible values are
     *                                - temp.php (single file name)
     *                                - ~*.php (with * wildcard)
     *                                  Will be replaced against regex '/^~.*\.php$/'
     */
    public function setExcludeFiles(array $excludeFiles)
    {
        foreach ($excludeFiles as $pos => $entry) {
            if (strpos($entry, '*') !== false) {
                $entry = '/^' . str_replace('*', '.*', preg_quote($entry)) . '$/';
                $excludeFiles[$pos] = $entry;
            }
        }
        $this->excludeFiles = $excludeFiles;
    }

    /**
     * Returns list of files to exclude
     *
     * @return  string[]
     */
    public function getExcludeFiles(): array
    {
        return $this->excludeFiles;
    }

    /**
     * Sets file extensions to parse
     *
     * @param   string[]  $extensionsToParse
     */
    public function setExtensionsToParse(array $extensionsToParse)
    {
        $this->extensionsToParse = $extensionsToParse;
    }

    /**
     * Returns list of file extension to parse
     *
     * @return  string[]
     */
    public function getExtensionsToParse(): array
    {
        return $this->extensionsToParse;
    }

    /**
     * Detects all available class type tokens in found files inside passed directory.
     *
     * @param SplFileInfo  $fileInfo
     * @param bool $recursive Flag to parse directory recursive
     * @return string[]|null Either an associative array where the key is the class
     *                    type token and the value is the path or null.
     */
    public function findInDir(SplFileInfo $fileInfo, bool $recursive = true)
    {
        if (!$fileInfo->isDir() || !$fileInfo->isReadable()) {
            Util::addDebugMessage('findInDir: Invalid/Not readable directory ' . $fileInfo->getPathname());
            return null;
        }
        Util::addDebugMessage('findInDir: Processing dir ' . $fileInfo->getPathname() . ' (realpath: ' . $fileInfo->getRealPath() . ')');

        $classTypeTokens = [];

        $iterator = $this->getDirIterator($fileInfo, $recursive);

        foreach ($iterator as $file) {
            if ($this->isFileToProcess($file)) {
                if ($foundTokens = $this->findInFile($file)) {
                     $classTypeTokens = array_merge($classTypeTokens, $foundTokens);
                }
            }
        }

        return count($classTypeTokens) > 0 ? $classTypeTokens : null;
    }

    /**
     * Detects all available class type tokens in passed file
     *
     * @param SplFileInfo $fileInfo
     * @return string[]|null Either an associative array where the key is the class
     *                    type token and the value is the path or null.
     */
    public function findInFile(SplFileInfo $fileInfo)
    {
        try {
            $tokenExtractor = new TokenExtractor($fileInfo);
            return $tokenExtractor->extractToken();
        } catch (Exception $exception) {
            Util::addDebugMessage($exception->getMessage());
            return null;
        }
    }

    /**
     * {@see Util::getDebugMessages()}
     */
    public function getDebugMessages(): array
    {
        return Util::getDebugMessages();
    }

    /**
     * {@see Util::getFormattedDebugMessages()}
     * @throws Exception if the given wrap does not contain %s
     */
    public function getFormattedDebugMessages(string $delimiter = "\n", string $wrap = '%s'): string
    {
        return Util::getFormattedDebugMessages($delimiter, $wrap);
    }

    /**
     * Returns directory iterator depending on $recursive parameter value
     *
     * @param   SplFileInfo  $fileInfo
     * @param   bool         $recursive
     * @return  RecursiveIteratorIterator|DirectoryIterator
     */
    protected function getDirIterator(SplFileInfo $fileInfo, bool $recursive)
    {
        if ($recursive) {
            return new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($fileInfo->getRealPath()),
                RecursiveIteratorIterator::SELF_FIRST
            );
        } else {
            return new DirectoryIterator($fileInfo->getRealPath());
        }
    }

    /**
     * Checks if file is to process
     *
     * @param   SplFileInfo  $file
     * @return  bool
     */
    protected function isFileToProcess(SplFileInfo $file): bool
    {
        if ($this->isDirToExclude($file)) {
            Util::addDebugMessage('isFileToProcess: Dir to exclude ' . $file->getPathname() . ' (realpath: ' . $file->getRealPath() . ')');
            return false;
        }
        if ($this->isFileToExclude($file)) {
            Util::addDebugMessage('isFileToProcess: File to exclude ' . $file->getPathname() . ' (realpath: ' . $file->getRealPath() . ')');
            return false;
        }
        if ($this->isFileToParse($file)) {
            Util::addDebugMessage('isFileToProcess: File to parse ' . $file->getPathname() . ' (realpath: ' . $file->getRealPath() . ')');
            return true;
        }
        return false;
    }

    /**
     * Checks if directory is to exclude
     *
     * @param   SplFileInfo  $file
     * @return  bool
     */
    protected function isDirToExclude(SplFileInfo $file): bool
    {
        $path = strtolower(Util::normalizePathSeparator($file->getRealPath()));

        foreach ($this->excludeDirs as $item) {
            if (strpos($path, $item) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks if file is to exclude
     *
     * @param   SplFileInfo  $file
     * @return  bool
     */
    protected function isFileToExclude(SplFileInfo $file): bool
    {
        $path = strtolower(Util::normalizePathSeparator($file->getRealPath()));

        foreach ($this->excludeFiles as $item) {
            if (strlen($item) > 2 && substr($item, 0, 2) == '/^') {
                if (preg_match($item, $path)) {
                    return true;
                }
            } else if (strpos($path, $item) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks if file is to parse (if file extension matches)
     *
     * @param   SplFileInfo  $file
     * @return  bool
     */
    protected function isFileToParse(SplFileInfo $file): bool
    {
        $path = strtolower(Util::normalizePathSeparator($file->getRealPath()));

        foreach ($this->extensionsToParse as $item) {
            if (substr($path, -strlen($item)) == $item) {
                return true;
            }
        }
        return false;
    }

}
