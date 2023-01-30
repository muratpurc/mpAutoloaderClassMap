# AutoloaderClassMap

A PHP based class map generator and autoloader for usage with [composer](https://getcomposer.org/) or 
directly in PHP projects.

Is able to parse several defined directories/files for existing `class`/`interface`/`trait` definitions and 
to generate a class map configuration file which is usable for an autoloader implementation.

---

## Installation

### Install using composer

```sh
composer require purc/autoloader-class-map
```

**NOTE:** You may need to update your `minimum-stability` definition to `dev` in your `composer.json` 
in order to install this package.

```json
{
    "minimum-stability": "dev"
}
```

### Install via git clone

Open your command line tool, clone the repository, change to the directory, and run composer.

```sh
# Clone from GitHub
$ git clone https://github.com/muratpurc/mpAutoloaderClassMap

# Change to the package folder
$ cd mpAutoloaderClassMap

# Run composer
$ composer install
```

### Download from GitHub with bash

Open bash, download the latest version (see the latest tag) from GitHub, extract the archive, change to the directory, and run composer.

```sh
# Download release
$ curl -s -L https://github.com/muratpurc/mpAutoloaderClassMap/archive/refs/tags/0.2.1.tar.gz -o mpAutoloaderClassMap-0.2.1.tar.gz

# Extract archive
$ tar -xzf mpAutoloaderClassMap-0.2.1.tar.gz

# Change to the extracted folder
$ cd mpAutoloaderClassMap-0.2.1

# Run composer
$ composer install
```

### Manually download from GitHub

Download the zip package from [GitHub](https://github.com/muratpurc/mpAutoloaderClassMap) and extract it to a folder.

Open your command line tool, change to the extracted folder and run composer.

```sh
$ composer install
```

---

## Description

Loading required class/interface/trait files, in PHP can be done via different ways.

A project should ideally use the autoloading standard as defined in the [PSR-4](https://www.php-fig.org/psr/psr-4/), 
but this is not possible in some cases, e.g. when you have to deal with legacy code, which was implemented way 
before the existence of the PHP Standard Recommendation ([PSR](https://www.php-fig.org/psr/)).

If the project is not PSR-4 compatible and/or there is no way to map automatically required 
`class`/`interface`/`trait` names to file system location, and you want to get rid of all `require`/`include` 
statements in your PHP files, then using a class map configuration is probably the convenient solution.

The AutoloaderClassMap parses specific folders for `class`, `interface`, and `trait` definitions, and generates
a class map file from the parse results. The class map file can be used with a custom autoloader implementation,
which deals with loading the required source files, the first time you use one of the class, interface or trait
names in your PHP scripts.

----

## Class Mao Generator Options

There a some options to control class map creation described as follows:

### `excludeDirs`

(`string[]`) List of directories to ignore (note: is case-insensitive)

Default value is `['.svn', '.cvs']`.

### `excludeFiles`

(`string[]`) List of files to ignore, regexp pattern is also accepted (note: is case insensitive)

Default value is `['/^~*.\.php$/', '/^~*.\.inc$/']`

### `extensionsToParse`

(`string[]`) List of file extensions to parse (note: is case-insensitive)

Default value is `['.php', '.inc']`

### `enableDebug`

(`bool`) Flag to enable debugging, collects some helpful state information's

Default value is `false`

---

## Setting an Environment Variable or PHP $GLOBAL

The AutoloaderClassMap comes with a build-in `Autoloader` implementation, see [Autoloader.php](./src/Autoloader.php).

You need to define the path to your class map file in order to use the build-in `Autoloader`. 
This can be done by defining an environment variable or by setting the PHP superglobal `$GLOBAL` variable.

### Environment Variable

There are different ways to set an environment variable, use the solution which fits the best for your needs.

1. Setting the environment variable in a `.env` file, in case your project supports dotenv.

    ```
    PURC_AUTOLOADER_CLASS_MAP_FILE=/path/to/your/class_map_file.php
    ```

2. Setting the environment variable in a `.htaccess` file, in case your project is served via Apache.

    ```
    SetEnv PURC_AUTOLOADER_CLASS_MAP_FILE /path/to/your/class_map_file.php
    ```

3. Setting the environment variable in a PHP script, e.g. at the beginning of the application bootstrap process. 
    This must be done before using an entry defined in the class map file!

    ```php
    <?php
    putenv('PURC_AUTOLOADER_CLASS_MAP_FILE="/path/to/your/class_map_file.php"');
    ```

### PHP superglobal $GLOBAL Variable

The preferred way should be the setting of an environment variable, but it is also possible to set it via 
the PHP superglobal `$GLOBAL`.

Define the path to the class map file by setting the PHP superglobal `$GLOBAL` in a PHP script, e.g. at 
the beginning of the application bootstrap process. This must be done before using an entry defined in
the class map file!

```php
<?php
$GLOBALS['PURC_AUTOLOADER_CLASS_MAP_FILE'] = '/path/to/your/class_map_file.php';
```

---

## Usage

The AutoloaderClassMap `examples` will be within the `vendor` folder (`vendor/purc/autoloader-class-map/examples`)
in case you have installed the package via composer.

You should copy the `examples` folder to you project root, by typing following command in you command line tool:

```sh
cp -r ./vendor/purc/autoloader-class-map/examples ./examples
```

This step ist not mandatory, but most likely you need to do some adaptions in the example files, 
e.g. configuring the class map creation to your needs, and it is not recommended to modify files
within the `vendor` folder. You can also use the logic in [class_map_generation.php](./examples/class_map_generation.php) 
as a blueprint and implement your own class map creation script.

The description below will assume, that the `examples` folder is in your project root, and you are using 
the example files being delivered with the AutoloaderClassMap package.

### Create a class map configuration

See [class_map_generation.php](./examples/class_map_generation.php) in [examples](./examples) folder.

Configure the example to your requirements and run the script from the command line as follows:

```sh
$ php ./examples/class_map_generation.php
```

It should generate the class map configuration file `classmap.configuration.php` in same/configured directory.

### Use the created class map configuration with an autoloader

You can use the build-in `Autoloader` implementation (see [Autoloader.php](./src/Autoloader.php)) or set up your own.

See the section `Setting an Environment Variable or PHP $GLOBAL` above for using the build-in `Autoloader`.

### In composer.json

By defining an `autoload` setting in your `composer.json`. The values in `files` will be loaded by composer's
autoloading mechanism.

```json
{
    "autoload": {
        "files": ["vendor/purc/autoloader-class-map/src/Autoloader.php"]
    }
}
```

### In a PHP Script

Require the autoloader script in PHPt, e.g. at the beginning of the application bootstrap process. 
This must be done before using an entry defined in the class map file!

```php
require_once 'vendor/purc/autoloader-class-map/src/Autoloader.php';
```
