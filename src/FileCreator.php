<?php
declare(strict_types=1);

/**
 * Contains class to create a class map file.
 *
 * @category   Development
 * @package    AutoloaderClassMap
 * @author     Murat Purç <murat@purc.de>
 * @copyright  Murat Purç (https://www.purc.de)
 * @license    https://www.gnu.org/licenses/gpl-2.0.html - GNU General Public License, version 2
 */

namespace Purc\AutoloaderClassMap;

use stdClass;

/**
 * Class to create a PHP file which contains an associative PHP array.
 *
 * Generated file will contain a PHP array as following:
 * <code>
 * return [
 *     '{classname}' => '{path_to_class_file}',
 *     '{classname2}' => '{path_to_class_file2}',
 * ];
 * </code>
 *
 * @category  Development
 * @package   AutoloaderClassMap
 * @author    Murat Purç <murat@purc.de>
 */
class FileCreator {

    /**
     * Class map file template
     *
     * @var string
     */
    protected $template = '';

    /**
     * Template replacements
     *
     * @var stdClass
     */
    protected $data = '';

    /**
     * Sets template and template replacements
     */
    public function __construct()
    {
        $this->template = trim('
<?php
/**
 {DESCRIPTION}
 *
 * @package    {PACKAGE}
 * @subpackage {SUBPACKAGE}
 * @version    {VERSION}
 * @author     {AUTHOR}
 * @copyright  {COPYRIGHT}
 * @license    {LICENSE}
 */

{CONTENT}
');
        $this->data = new stdClass();
        $this->data->content = '';
        $this->data->description = trim('
 * Autoloader classmap file. Contains all available classes/interfaces/traits and
 * related class files.
 *
 * NOTES:
 * - Don\'t edit this file manually!
 * - It was generated by ' . __CLASS__ . '
 * - Use ' . __CLASS__ . ' again, if you want to regenerate this file
 *');

        $this->data->package = __CLASS__;
        $this->data->subpackage = 'Classmap';
        $this->data->version = '0.2';
        $this->data->author = 'System';
        $this->data->copyright = 'Murat Purç (https://www.purc.de)';
        $this->data->license = 'https://www.gnu.org/licenses/gpl-2.0.html - GNU General Public License, version 2';
    }

    /**
     * Creates classmap file with passed data list
     *
     * @param string[] $data Associative list which contains class type tokens
     *         and the related path to the class file.
     * @param string $file Destination class map file
     * @return bool
     */
    public function create(array $data, string $file): bool
    {
        $this->createClassMap($data);

        return (bool) file_put_contents($file, $this->renderTemplate());
    }

    /**
     * Fills template replacement variable with generated associative PHP array
     *
     * @param string[] $data Associative list with class type tokens and files
     */
    protected function createClassMap(array $data)
    {
        $eol = PHP_EOL;
        $classMapTpl = "{$eol}return [$eol%s$eol];$eol";
        $classMapContent = '';
        foreach ($data as $classToken => $path) {
            $classMapContent .= sprintf("    '%s' => '%s',%s", addslashes($classToken), addslashes($path), $eol);
        }
        $classMapContent = substr($classMapContent, 0, -3);

        $this->data->content .= sprintf($classMapTpl, $classMapContent);
    }

    /**
     * Replaces all wildcards in template with related template variables.
     *
     * @return string Replaced template
     */
    protected function renderTemplate(): string
    {
        $template = $this->template;
        foreach ($this->data as $name => $value) {
            $template = str_replace('{' . strtoupper($name) . '}', $value, $template);
        }

        return $template;
    }

}