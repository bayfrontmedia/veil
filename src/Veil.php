<?php

/**
 * @package veil
 * @link https://github.com/bayfrontmedia/veil
 * @author John Robinson <john@bayfrontmedia.com>
 * @copyright 2020 Bayfront Media
 */

namespace Bayfront\Veil;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\Sanitize\Sanitize;
use Minifier\TinyMinify;
use Parsedown;

class Veil
{

    private $options;

    public function __construct(array $options)

    {

        $this->options = array_merge([
            'base_path' => '', // No trailing slash
            'file_extension' => '.veil.php' // Include dot
        ], $options);

    }

    /**
     * Returns base path.
     *
     * @return string
     */

    public function getBasePath(): string
    {
        return $this->options['base_path'];
    }

    /**
     * Sets base path.
     *
     * @param string $base_path
     *
     * @return void
     */

    public function setBasePath(string $base_path): void
    {
        $this->options['base_path'] = rtrim($base_path, '/');
    }

    private $injectables = [ // Assets injected via inject()
        'css' => [],
        'js' => [],
        'head' => [],
        'end_body' => []
    ];

    /**
     * Add injectable(s) by their respective types
     *
     * In addition to the default injectable types, custom types can be added and used
     *
     * NOTE: If $content is an array, all injectables will share the same priority.
     *
     * @param string $type
     * @param string|array $content
     * @param int $priority (Injectables of the same type will be injected in order of priority)
     *
     * @return self
     */

    public function inject(string $type, $content, int $priority = 5): self
    {

        foreach ((array)$content as $inject) {

            $this->injectables[$type][] = [
                'content' => $inject,
                'priority' => $priority
            ];
        }

        return $this;

    }

    /**
     * Get compiled HTML as a string
     *
     * @param string $html
     * @param array $data (Data to pass to HTML in dot notation)
     * @param bool $minify (Minify compiled HTML?)
     *
     * @return string
     *
     * @throws FileNotFoundException
     */

    public function getHtml(string $html, array $data = [], bool $minify = false): string
    {

        if (true === $minify) {
            return $this->minify($this->_processTemplateTags($html, Arr::dot($data)));
        }

        return $this->_processTemplateTags($html, Arr::dot($data));

    }

    /**
     * Echo compiled HTML
     *
     * @param string $html
     * @param array $data (Data to pass to HTML in dot notation)
     * @param bool $minify (Minify compiled HTML?)
     *
     * @return void
     *
     * @throws FileNotFoundException
     */

    public function html(string $html, array $data = [], bool $minify = false): void
    {
        echo $this->getHtml($html, $data, $minify);
    }

    /**
     * Get compiled template file as a string
     *
     * @param string $file (Path to file from base path, excluding file extension)
     * @param array $data (Data to pass to view in dot notation)
     * @param bool $minify (Minify compiled HTML?)
     *
     * @return string
     *
     * @throws FileNotFoundException
     */

    public function getView(string $file, array $data = [], bool $minify = false): string
    {

        $filename = $this->options['base_path'] . '/' . ltrim($file, '/') . $this->options['file_extension'];

        $data = Arr::dot($data);

        $html = $this->_requireToVar($filename, $data);

        if (true === $minify) {
            return $this->minify($this->_processTemplateTags($html, $data));
        }

        return $this->_processTemplateTags($html, $data);

    }

    /**
     * Echo compiled template file
     *
     * @param string $file (Path to file from base path, excluding file extension)
     * @param array $data (Data to pass to view in dot notation)
     * @param bool $minify (Minify compiled HTML?)
     *
     * @return void
     *
     * @throws FileNotFoundException
     */

    public function view(string $file, array $data = [], bool $minify = false): void
    {
        echo $this->getView($file, $data, $minify);
    }

    /**
     * Minify HTML
     *
     * See: https://github.com/pfaciana/tiny-html-minifier
     *
     * @param string $html
     *
     * @return string
     */

    public function minify(string $html): string
    {
        return TinyMinify::html($html);
    }

    /**
     * Convert markdown syntax to HTML
     *
     * See: https://github.com/erusev/parsedown
     *
     * @param string $markdown
     *
     * @return string
     */

    public function markdown(string $markdown): string
    {
        $md = new Parsedown();

        return $md->text($markdown);
    }

    /**
     * Uses the output buffer to include a file to a variable
     *
     * NOTE: Do not include file extension, as this will search for *.veil.php and *.php files
     *
     * @param string $file
     * @param mixed $data (Parameters passed to the required file
     *
     * @return string
     *
     * @throws FileNotFoundException
     *
     */

    private function _requireToVar(string $file, /* @noinspection PhpUnusedParameterInspection */ $data = NULL): string
    {

        if (file_exists($file)) {

            ob_start();

            require($file);

            return ob_get_clean();

        }

        throw new FileNotFoundException('Unable to load view: ' . $file);

    }

    /**
     * Array of sections whose keys = name
     * and value = content.
     *
     * NOTE:
     * This array must be static to persist with the recursive calls
     * in the _processTemplateTags method.
     *
     * @var array
     */

    private static $sections = [];

    /**
     * Replaces template tags with their respective values
     *
     * @param string $html
     * @param array $data (Array of data to be passed as parameters)
     *
     * @return string
     *
     * @throws FileNotFoundException
     */

    private function _processTemplateTags(string $html, array $data): string
    {

        // -------------------- Tag: @use: --------------------

        preg_match_all("/@use:\S+/", $html, $tags); // Any non-whitespace

        if (isset($tags[0]) && !empty($tags[0])) { // If a tag was found

            foreach ($tags[0] as $tag) {

                $use = explode(':', $tag, 2);

                if (isset($use[1])) { // If valid @use syntax

                    $file = $this->_requireToVar($this->options['base_path'] . '/' . ltrim($use[1], '/') . $this->options['file_extension'], $data);

                    $tag = str_replace('/', '\/', $tag); // Escape forward slashes

                    $html = preg_replace("/" . $tag . "\b/i", $file, $html); // Replace tag

                }
            }

            /*
             * Keep looping until no more @use: tags exist (nested files)
             */

            $html = $this->_processTemplateTags($html, $data);

        }

        // -------------------- Tag: @section: --------------------

        preg_match_all("/@section:(.*?)@endsection/s", $html, $tags);

        if (isset($tags[0]) && !empty($tags[0])) { // If a tag was found

            foreach ($tags[0] as $tag) {

                $use = explode(':', $tag, 2);

                if (isset($use[1])) { // If valid @section syntax

                    /*
                     * TODO:
                     * Is PHP_EOL going to be problematic?
                     */

                    $section = explode(PHP_EOL, $use[1], 2);

                    if (isset($section[1])) {

                        // Define a section (key = name, value = content
                        self::$sections[$section[0]] = str_replace('@endsection', '', trim($section[1]));

                        // Remove original tag from HTML source
                        $html = str_replace($tag, '', $html);

                    }

                }

            }

        }

        // Place content

        foreach (self::$sections as $name => $content) {

            $html = str_replace([
                '?@place:' . $name,
                '@place:' . $name
            ], $content, $html);

        }

        // Remove unused optional ?@place tags

        $html = preg_replace('/\?@place:\S+/', '', $html);

        // -------------------- Tag: @markdown: --------------------

        preg_match_all("/@markdown:\S+/", $html, $tags); // Any non-whitespace

        if (isset($tags[0]) && !empty($tags[0])) { // If a tag was found

            foreach ($tags[0] as $tag) {

                $use = explode(':', $tag, 2);

                if (isset($use[1])) { // If valid @markdown syntax

                    $file = $this->_requireToVar($this->options['base_path'] . '/' . ltrim($use[1], '/') . $this->options['file_extension'], $data);

                    $tag = str_replace('/', '\/', $tag); // Escape forward slashes

                    $html = preg_replace("/" . $tag . "\b/i", $this->markdown($file), $html); // Replace tag

                }
            }

            /*
             * Keep looping until no more @use: tags exist (nested files)
             */

            $html = $this->_processTemplateTags($html, $data);

        }

        // -------------------- Tag: @inject: --------------------

        foreach ($this->injectables as $type => $inject) {

            $html = str_replace('@inject:' . $type, $this->_getInjectable($type), $html);

        }

        // -------------------- Remove comments {{-- COMMENT —-}} --------------------

        $html = preg_replace("/{{--(.*?)--}}/s", '', $html);

        // -------------------- Insert parameters {{in.dot.notation}} --------------------

        foreach ($data as $k => $v) {

            $html = str_replace([
                '{{!' . $k . '}}', // Unfiltered
                '{{' . $k . '}}'  // Escape variable to prevent XSS attacks
            ], [
                $v,
                Sanitize::escape($v)
            ],
                $html
            );

        }

        // -------------------- Replace with default --------------------

        $data = Arr::dot($data);

        $html = preg_replace_callback("/{{(.*?)\|\|(.*?)}}/", function ($match) use ($data, $html) {

            $replace = Arr::get($data, $match[1], $match[2]);

            return str_replace([
                $match[0], // Unfiltered
                '{{!' . $match[1] . '||' . $match[2] . '}}'  // Escape variable to prevent XSS attacks
            ], [
                $replace,
                Sanitize::escape($replace)
            ],
                $match[0]
            );

        }, $html);

        // -------------------- Trim --------------------

        return trim($html);

    }

    /**
     * Returns a string of content to be injected to a view in descending order according to priority
     *
     * @param string $type
     *
     * @return string
     */

    private function _getInjectable(string $type): string
    {

        if (!isset($this->injectables[$type]) || empty($this->injectables[$type])) { // Nonexistent
            return '';
        }

        $return = '';

        $injectables = Arr::multisort($this->injectables[$type], 'priority', true);

        foreach ($injectables as $injectable) {

            if ($type == 'css') {

                $return .= '<link rel="stylesheet" href="' . $injectable['content'] . '" type="text/css" media="all" />' . "\n";

            } else if ($type == 'js') {

                $return .= '<script src="' . $injectable['content'] . '"></script>' . "\n";

            } else {

                $return .= $injectable['content'] . "\n";

            }

        }

        return $return;

    }

}