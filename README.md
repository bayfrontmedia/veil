## Veil

A fast, lightweight, framework-agnostic PHP template engine.

Although there are some respectable, well-known PHP template engines available, most of them can be extremely complex and include a great deal of customized syntax to learn.
In addition, using proprietary libraries to perform simple functions and iterations can introduce unnecessary complexity and overhead, especially since PHP is a templating language in and of itself.

While it may not be suitable for every outlying case, Veil has been created to provide minimal and rapid template language functionality, including template inheritance, in a simple framework-agnostic library.

- [License](#license)
- [Author](#author)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)

## License

This project is open source and available under the [MIT License](LICENSE).

## Author

<img src="https://cdn1.onbayfront.com/bfm/brand/bayfront-media-logo.svg" alt="Bayfront Media" width="250" />

- [Bayfront Media homepage](https://www.bayfrontmedia.com?utm_source=github_repo&amp;utm_medium=direct)
- [Bayfront Media GitHub](https://github.com/bayfrontmedia)

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

#### Template tags

A variety of template tags can be used with Veil. 
This allows for functionality such as template inheritance (chaining files), injection of content, and usage of passed parameters.

The following template tags can be used in HTML and view files:

| Tag | Function
| --- | --- |
| `@use:path/from/base` | Adds contents of another file |
| `@markdown:path/from/base` | Adds markdown of another file as HTML (see [markdown](#markdown)) |
| `@inject:type` | Injects content (see [inject](#inject)) |
| `@section:name` | Defines a section to be placed in a view (see [sections](#sections)) |
| `@place:name` | Places a defined section into the view (see [sections](#sections)) |
| `?@place:name` | Places an optionally defined section into the view (see [sections](#sections)) |
| `{{-- Comment —-}}` | Everything inside comment tags will be ignored and removed |
| `{{parameter.name}}` | Replaced with escaped value from the `$data` array in dot notation |
| `{{!parameter.name}}` | Replaced with unescaped (raw) value from the `$data` array in dot notation |
| <code>{{parameter.name&#124;&#124;string}}</code> | Replaced with escaped value from the `$data` array in dot notation or default string if not existing |
| <code>{{!parameter.name&#124;&#124;string}}</code> | Replaced with unescaped (raw) value from the `$data` array in dot notation or default string if not existing |

##### Sections

Sections are used to define a block of HTML using the `@section` tag, 
which is placed in a view using the `@place` tag.

**Example `pages/index`:**

```html
@section:head

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">
<style>html {
        font-family: 'Open Sans', sans-serif;
    }</style>

@endsection

@section:content

<p>Welcome, {{name}}!</p>

<p>This is some content.</p>

@endsection

@use:layouts/default
```

**Example `layouts/default`:**

```html
<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{title}}</title>

    @place:head

</head>
<body style="background-color:#f0f0f0;color:#323232;">

<div style="max-width:800px;margin:auto;background-color:white;padding:1rem;">

    <h1>{{title}}</h1>

    @place:content

    @use:layouts/partials/sidebar

    @use:layouts/partials/footer

</div>

?@place:end_body

</body>
</html>
```

In the examples above, the sections `head` and `content` are defined in `pages/index`, 
then placed in `layouts/default`.

In addition, an optional `end_body` section is placed before the closing `body` tag.

#### Raw PHP

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