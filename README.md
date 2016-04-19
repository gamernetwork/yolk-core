# Yolk Core

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/gamernetwork/yolk-core/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/gamernetwork/yolk-core/?branch=develop)

The lightweight core of Gamer Network's PHP framework. Provides error and exception handling and commonly used helper
functions.

## Requirements

This library requires only PHP 5.4 or later and the Yolk Contracts package (`gamernetwork/yolk-contracts`).

## Installation

It is installable and autoloadable via Composer as `gamernetwork/yolk-core`.

Alternatively, download a release or clone this repository, and add the `\yolk` namespace to an autoloader.

## License

Yolk Core is open-sourced software licensed under the MIT license.

## Quick Start

Yolk Core provides a basic exception and error handling wrapper for blocks of code, be they command-line scripts,
complete web apps or simple functions.

Running code with Yolk is a simple as calling the static `run()` method with a `callable` parameter.

```php
use yolk\Yolk;

// Using a closure
Yolk::run(function() {
  echo "Hello World";
});

// Using a function name
function hello() {
  echo "Hello World";
}
Yolk::run('hello');

// Using an object...
class Foo {
  public static function hello() {
    echo "Hello World";
  }
  public function helloWorld() {
    echo "Hello World";
  }
  public function __invoke() {
    $this->helloWorld();
  }
}

// ...static callback
Yolk::run(['Foo', 'hello']);

// ...instance callback
$o = new Foo();
Yolk::run([$o, 'hello']);

// ...invokable object
Yolk::run($o);
```

## Error and Exception Handling

Yolk Core provides default error and exception handlers:
* Errors are converted to ErrorExceptions() and passed to the exception handler.
* Exceptions result in an error page being displayed (for web scripts) or the exception being dumped to stdout (for CLI scripts) 

You can override the default handlers by passing a callable to the appropriate method:
```php
use yolk\Yolk;

Yolk::setErrorHandler($callback);

Yolk::setExceptionHandler($callback);
```

## Debug Flag

Yolk provides a debug flag accessible via:

```php
use yolk\Yolk;

// enabled/disable debug flag
Yolk::setDebug(true);

// Return current setting of debug flag
Yolk::isDebug();
```

Usage of the debug flag is left almost entirely to clients. The only uses within the framework are:
* to determine whether to display a detailed error page (if debug flag is set) or a simple static error page
* calls to the d() and dd() dump functions are ignored if the debug flag is not set

The default simple static error page can be overriden: by passing the path and file name to the ```
```php
use yolk\Yolk;

Yolk::setErrorPage($path_to_file);
```

## Variable Dumping

Yolk provides an enhanced var_dump() implementation that can output detailed variable information in plain text, html or terminal (CLI) formats.

```php
use yolk\Yolk;

/**
 * $var - any variable or constant
 * $format - one of null, Yolk::DUMP_TEXT, Yolk::DUMP_HTML, Yolk::DUMP_TERMINAL
 */
Yolk::dump($var, $format = null);
```

If no format or null is specified then Yolk will auto-detect the appropriate output - `DUMP_TERMINAL` will be used within CLI environments and `DUMP_HTML` will be used for web environments.

## Helpers

Yolk provides a variety of helper functions and more can be easily added. Helper functions are implemented as static class methods and registered either by passing the class name to the `Yolk::registerHelper()` method (registers all public static methods) or by passing a class and method name to the `Yolk::addHelperMethod()` (registers a single method).
Once registered helper functions can be called via static method call to `Yolk`.

```php
use yolk\Yolk;

class MyHelper {
  public static function foo() {
    echo 'foo';
  }
  public static function bar() {
    echo 'bar';
  }
}

Yolk::registerHelper('\\MyHelper');

Yolk::foo();
Yolk::bar();
```

### General Helpers

* `isCLI()` - determines if the script is running in a CLI environment

### Array Helpers

* `uniqueIntegers()` - return an array of unique integers
* `isTraversable()` - determines if a variable can be interated over using `foreach`
* `isAssoc()` - determine if an array is associative or not
* `filterObjects()` - filter an array to instances of a specific class
* `get()` - return an item from an array or object or a default value if the item doesn't exist
* `getNullItems()` - filter an array to items that are null
* `pluck()` - extract a single field from an array of arrays of objects
* `sum()` - calculate the sum of the specified item from an array of arrays or objects
* `min()` - calculate the min of the specified item from an array of arrays or objects
* `max()` - calculate the max of the specified item from an array of arrays or objects
* `implodeAssoc()` - implode an associative array into an array of key/value pairs
* `makeComparer()` - create a comparison function for sorting multi-dimensional arrays

### Date/Time Helpers

* `makeTimestamp` - convert a value into a timestamp
* `seconds()` - convert a string representation containing one or more of hours, minutes and seconds into a total number of seconds

### String Helpers

* `parseURL()` - parse a url into an array of it's components
* `randomHex()` - generate a random hex string of a specific length
* `randomString()` - generate a random string of a specific length
* `uncamelise()` - convert a camel-cased string to lower case with underscores
* `slugify()` - convert a string into a form suitable for urls
* `removeAccents()` - convert accented characters to their regular counterparts
* `latin1()` - convert a UTF-8 string into Latin1 (ISO-8859-1)
* `utf8()` - convert a Latin1 (ISO-8859-1) into UTF-8
* `ordinal()` - return the ordinal suffix (st, nd, rd, th) of a number
* `sizeFormat()` - convert a number of bytes to a human-friendly string using the largest suitable unit

* `xssClean()` - remove XSS vulnerabilities from a string
* `stripControlChars()` - remove control characters from a string

### Inflection Helpers

* `pluralise()` - Determine the plural form of a word (English only)
* `singularise()` - Determine the single form of a word (English only)
