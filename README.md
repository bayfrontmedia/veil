## Veil

A fast, lightweight, framework agnostic PHP template engine.

Although there are some respectable, well-known PHP template engines available, most of them can be extremely complex and include a great deal of customized syntax to learn.
In addition, using proprietary libraries to perform simple functions and iterations can introduce unnecessary complexity and overhead, especially since PHP is a templating language in and of itself.

While it may not be suitable for every outlying case, Veil has been created to provide minimal and rapid template language functionality, including template inheritance, in a simple framework agnostic library.

- [License](#license)
- [Author](#author)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)

## License

This project is open source and available under the [MIT License](LICENSE).

## Author

John Robinson, [Bayfront Media](https://www.bayfrontmedia.com)

## Requirements

- PHP >= 7.1.0

## Installation

```
composer require bayfrontmedia/veil
```

## Usage

### Start using Veil

Default configuration:

```
use Bayfront\Veil\FileNotFoundException;
use Bayfront\Veil\Veil;

$options = [
    'base_path' => '', // Path to template files (no trailing slash)
    'file_extension' => '.veil.php' // Template file extensions (starting with ".")
];

$veil = new Veil($options);
```

### Working with content

**Template tags**

A variety of template tags can be used with Veil. 
This allows for functionality such as template inheritance (chaining files), injection of content, and usage of passed parameters.

The following template tags can be used in HTML and view files:

| Tag | Function
| --- | --- |
| `@use:path/from/base` | Adds contents of another file |
| `@markdown:path/from/base` | Adds markdown of another file as HTML (see [markdown](#markdown)) |
| `@inject:type` | Injects content (see [inject](#inject)) |
| `{{-- Comment —-}}` | Everything inside comment tags will be ignored and removed |
| `{{parameter.name}}` | Replaced with escaped value from the `$data` array in dot notation |
| `{{!parameter.name}}` | Replaced with unescaped (raw) value from the `$data` array in dot notation |
| <code>{{parameter.name&#124;&#124;string}}</code> | Replaced with escaped value from the `$data` array in dot notation or default string if not existing |
| <code>{{!parameter.name&#124;&#124;string}}</code> | Replaced with unescaped (raw) value from the `$data` array in dot notation or default string if not existing |


**Raw PHP**

View files can directly access the `$data` array in dot notation.
In fact, any other PHP code can be directly embedded in any view file.
However, this should be kept to simple tasks such as performing loop iterations.
Frequently embedding raw PHP from your view may be a sign you have too much logic within the template.

### Public methods

- [getBasePath](#getbasepath)
- [setBasePath](#setbasepath)
- [inject](#inject)
- [getHtml](#gethtml)
- [html](#html)
- [getView](#getview)
- [view](#view)
- [minify](#minify)
- [markdown](#markdown)

<hr />

### getBasePath

**Description:**

Returns base path.

**Parameters:**

- (None)

**Returns:**

- (string)

<hr />

### setBasePath

**Description:**

Sets base path.

**Parameters:**

- `$base_path` (string)

**Returns:**

- (void)

<hr />

### inject

**Description:**

Add injectable(s) by their respective types.

Default injectable types to use in your templates include:

- `css`: Wraps content into a CSS `<link>` element
- `js`: Wraps content into a `<script>` element
- `head`: Insert content into the `<head>` section
- `end_body`: Insert content just before the `</body>` tag

In addition to the default injectable types, custom types can be added and used.

**NOTE:** If `$content` is an array, all injectables will share the same priority.

**Parameters:**

- `$type` (string)
- `$content` (string|array)
- `$priority = 5` (int): Injectables of the same type will be injected in order of priority

**Returns:**

- (self)

**Example:**

```
$veil->inject('js', 'javascript-file.js');
```

Using the above example, whenever the `@inject:js` template tag appears, it will be replaced with:

```
<script src="javascript-file.js"></script>
```

<hr />

### getHtml

**Description:**

Get compiled HTML as a string.

**Parameters:**

- `$html` (string)
- `$data = []` (array): Data to pass to HTML in dot notation
- `$minify = false` (bool): Minify compiled HTML? See [minify](#minify) for more info.

**Returns:**

- (string)

**Throws:**

- `Bayfront\Veil\FileNotFoundException`

**Example:**

```
$html = 'Hello, {{name}}! Please visit: <a href="https://www.example.com">example.com</a>.';

try {

    $html = $veil->getHtml($html, ['name' => 'John']);

} catch (FileNotFoundException $e) {

    http_response_code(404);

    die($e->getMessage());

}
```

<hr />

### html

**Description:**

Echo compiled HTML.

**Parameters:**

- `$html` (string)
- `$data = []` (array): Data to pass to HTML in dot notation
- `$minify = false` (bool): Minify compiled HTML? See [minify](#minify) for more info.

**Returns:**

- (void)

**Throws:**

- `Bayfront\Veil\FileNotFoundException`

**Example:**

```
$html = 'Hello, {{name}}! Please visit: <a href="https://www.example.com">example.com</a>.';

try {

    $veil->html($html, ['name' => 'John']);

} catch (FileNotFoundException $e) {

    http_response_code(404);

    die($e->getMessage());

}
```

<hr />

### getView

**Description:**

Get compiled template file as a string.

**Parameters:**

- `$file` (string): Path to file from base path, excluding file extension
- `$data = []` (array): Data to pass to view in dot notation
- `$minify = false` (bool): Minify compiled HTML? See [minify](#minify) for more info.

**Returns:**

- (string)

**Throws:**

- `Bayfront\Veil\FileNotFoundException`

**Example:**

```
try {

    $html = $veil->getView('/path/to/file', ['name' => 'John']);

} catch (FileNotFoundException $e) {

    http_response_code(404);

    die($e->getMessage());

}
```

<hr />

### view

**Description:**

Echo compiled template file.

**Parameters:**

- `$file` (string): Path to file from base path, excluding file extension
- `$data = []` (array): Data to pass to view in dot notation
- `$minify = false` (bool): Minify compiled HTML? See [minify](#minify) for more info.

**Returns:**

- (void)

**Throws:**

- `Bayfront\Veil\FileNotFoundException`

**Example:**

```
try {

    $veil->view('/path/to/file', ['name' => 'John']);

} catch (FileNotFoundException $e) {

    http_response_code(404);

    die($e->getMessage());

}
```

<hr />

### minify

**Description:**

Minify HTML.

Currently, Veil uses [Tiny Html Minifier](https://github.com/pfaciana/tiny-html-minifier) for this method.

**NOTE:** In some cases, HTML may not minify correctly, so use with caution.
It is recommended you test this with your HTML and views before using in production.

**Parameters:**

- `$html` (string)

**Returns:**

- (string)

**Example:**

See [markdown](#markdown).

<hr />

### markdown

**Description:**

Convert markdown syntax to HTML.

Currently, Veil uses [Parsedown](https://github.com/erusev/parsedown) for this method.

**Parameters:**

- `$markdown` (string)

**Returns:**

- (string)

**Example:**

Convert a view file containing markdown to minified HTML:

```
try {

    $md = $veil->getView('path/to/markdown');

} catch (FileNotFoundException $e) {

    http_response_code(404);
    die('View not found!');

}

echo $veil->minify($veil->markdown($md));
```