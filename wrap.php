<?php
/**
 * W.R.A.P. by Magiiic
 * 
 * @version 5.0.1
 * @author Magiiic
 * @link https://wrap.rocks/
 * @license AGPL-3.0
 * 
 * Wrap is a simple file browser and media player for your website.
 */

require 'vendor/autoload.php';

/**
 * Main class
 * 
 * Initialize the application, update cache, collect data and build the page.
 * 
 * @package Wrap
 * @version 5.0.1
 * @since 5.0.1
 *
 * @property string $wrap_data
 * @property string $nav
 * @property string $branding
 * @property array $scripts
 * @property array $styles
 * @property string $breadcrumb
 * @property string $content
 * 
 */
class Wrap {
    private $wrap_data;
    private $nav;
    private $branding;
    private $site_title;
    private $logo;
    private $wrap_title = "W.R.A.P. by Magiiic";
    private $wrap_logo = "/images/magiiic-logoby-v4-wrap-80.png";
    private static $scripts = [];
    private static $styles = [];
    private $breadcrumb = '';
    private $content = '';
    private $title = '';
    private $minisite_root;

    public function __construct() {
        define('WRAP_VERSION', '5.0.1');
        
        $this->init();
        // $this->update_cache();
        $this->build_page();
    }

    /**
     * init
     * 
     * Initialize the application.
     * 
     * Set data root as 
     *  - a 'data' folder in the same parent as document root if present
     *  - fallback to document root if no data folder is found
     * 
     * @return void
     * 
     * @since 5.0.1
     */
    public function init() {
        // TODO: Add a global configuration file in app dir 
        // TODO: Add an option in app dir or in .wrap.json to set the data folder

        $document_root = $_SERVER['DOCUMENT_ROOT'];
        $try = [
            $document_root . '/data',
            dirname($document_root) . '/data',
            dirname($document_root)
        ];
        foreach ($try as $path) {
            if (file_exists($path)) {
                $wrap_data = $path;
                break;
            }
        }
        if(empty($wrap_data)) {
            die("No data folder found");
        }
        define('WRAP_DATA', $wrap_data);
        define('WRAP_DIR', dirname(__FILE__)); // Define WRAP_DIR as the actual script directory
        define('WRAP_URL', $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']);

        require_once(WRAP_DIR . '/includes/class-folder.php');
        require_once(WRAP_DIR . '/includes/class-file.php');
    }

    /**
     * update_cache
     * 
     * Update the cache for a file. Used for app files that needs to be available on the website.
     * 
     * A copy of the file is created in the cache directory if it doesn't exist or if the source file is newer than the cache file.
     * 
     * @param string $file
     * @return string
     * 
     * @since 5.0.1
     */
    public function update_cache( $file ) {
        if (empty($file)) return;

        // do not cache external urls
        if (filter_var($file, FILTER_VALIDATE_URL)) {
            return $file;
        }


        // Cache WRAP_DIR/css/style.css into WRAP_DATA/.css/style.css
        $source_file = WRAP_DIR . $file;
        if (!file_exists($source_file)) {
            return false;
        }
        $cache_file = WRAP_DATA . '/.cache/' . ltrim($file, '/');
        $cache_url = WRAP_URL . '/.cache/' . ltrim($file, '/');

        if (!file_exists($cache_file) || filemtime($source_file) != filemtime($cache_file)) {
            // Create the cache directory if it doesn't exist
            if (!is_dir(dirname($cache_file))) {
                mkdir(dirname($cache_file), 0777, true);
            }

            // Copy the CSS file to the cache directory
            copy($source_file, $cache_file);
        }
        $cache_url .= '?v=' . WRAP_VERSION;
        
        return $cache_url;
    }

    /**
     * build_page
     * 
     * Build the page content
     * 
     * @return void
     * 
     * @since 5.0.1
     */
    public function build_page() {
        $requested_url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $requested_path = WRAP_DATA . urldecode($requested_url);
        if (is_dir($requested_path)) {
            $wrap_folder = new Wrap_Folder($requested_url);
            $this->minisite_root = $wrap_folder->minisite_root;
            $this->content = $wrap_folder->build_content();
            $this->nav = $wrap_folder->get_nav();
            $this->breadcrumb = $wrap_folder->build_breadcrumb();
            $this->title = $wrap_folder->get_name();

            $this->output_html();

        } elseif (file_exists($requested_path)) {
            $wrap_file = new Wrap_File($requested_url);
            $wrap_file->output();
        } else {
            $this->error_404();
        }
    }
    
    /**
     * error_404
     * 
     * Output a 404 error page
     * 
     * @return void
     * 
     * @since 5.0.1
     */
    public function error_404() {
        error_log('Error 404: File not found - ' . $_SERVER['REQUEST_URI']);

        $this->content = "<h1>Not Found</h1>
        <p>The requested URL was not found on this server.</p>
        <hr>{$_SERVER['SERVER_SIGNATURE']}"; // Display Apache server signature in the error message
        $this->output_html('', '404 Not Found', 'The requested URL was not found on this server.', '404, Not Found', 404);
        
        die();
    }

    /**
     * build_url
     * 
     * Build a URL from a path
     * 
     * @param string $path
     * @return string
     * 
     * @since 5.0.1
     */
    public static function build_url($path) {
        $path = '/' . ltrim($path, '/');
        return WRAP_URL . $path;
    }

    /**
     * icons
     * 
     * Return an array of Font Awesome icons for file extensions
     * 
     * @return array
     * 
     * @since 5.0.1
     */
    public static function icons() {
        static $icons = null;
        if ($icons !== null) {
            return $icons;
        }
        $fa = [
            'csv' => 'fa-file-csv',
            'doc' => 'fa-file-word',
            'docx' => 'fa-file-word',
            'gif' => 'fa-file-image',
            'gz' => 'fa-file-archive',
            'jpeg' => 'fa-file-image',
            'jpg' => 'fa-file-image',
            'mov' => 'fa-file-video',
            'mp3' => 'fa-file-audio',
            'mp4' => 'fa-file-video',
            'mov' => 'fa-file-video',
            'pdf' => 'fa-file-pdf',
            'png' => 'fa-file-image',
            'ppt' => 'fa-file-powerpoint',
            'pptx' => 'fa-file-powerpoint',
            'rar' => 'fa-file-archive',
            'tar' => 'fa-file-archive',
            'wav' => 'fa-file-audio',
            'webm' => 'fa-file-video',
            'xls' => 'fa-file-excel',
            'xlsx' => 'fa-file-excel',
            'zip' => 'fa-file-archive',
        ];
        // <i class="fas ' . $icon . '"></i>
        $icons = array_map(function($icon) {
            return '<i class="icon fas ' . $icon . '"></i>';
        }, $fa);
        
        return $icons;
    }

    /**
     * queue_script
     * 
     * Queue a script to be included in the page
     * 
     * @param string $name
     * @param string $src
     * @return void
     * 
     * @since 5.0.1
     */
    public static function queue_script($name, $src) {
        self::$scripts[$name] = $src;
    }

    /**
     * queue_style
     * 
     * Queue a style to be included in the page
     * 
     * @param string $name
     * @param string $src
     * @return void
     * 
     * @since 5.0.1
     */
    public static function queue_style($name, $src) {
        self::$styles[$name] = $src;
    }

    /**
     * output_html
     * 
     * Output the HTML page
     * 
     * @param string $content
     * @param string $title
     * @param string $description
     * @param string $keywords
     * @param int $http_code
     * @return void
     * 
     * @since 5.0.1
     */
    public function output_html($content = '', $title = '', $description = '', $keywords = '', $http_code = 200) {
        http_response_code($http_code);

        $content = empty($content) ? $this->content : $content;
        $title = empty($title) ? $this->title : $title;

        $description = empty($description) ? $this->description : $description;
        $keywords = empty($keywords) ? $this->keywords : $keywords;

        // escape $content $title $description $keywords
        $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $description = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');
        $keywords = htmlspecialchars($keywords, ENT_QUOTES, 'UTF-8');

        self::queue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css');
        self::queue_style('style-main', '/dist/main.css');
        $this->update_cache('/dist/mitm.html');
        $this->update_cache('/dist/sw.js');
        $this->update_cache('/dist/ping');

        $this->logo = ($this->logo) ? $this->logo : $this->wrap_logo;
        $site_title = ($this->site_title) ? $this->site_title : $this->wrap_title;
        $logo = $this->update_cache($this->logo);
        $branding = ( $logo ) ? '<img class=logo src="' . $logo . '" alt="' . $this->site_title . '">' : $this->site_title;
        $this->branding = (empty($branding)) ? '' : '<a href="' . WRAP_URL . '" class="branding">' . $branding . '</a>';
        
        if($this->wrap_logo) {
            $footer_logo = $this->update_cache($this->wrap_logo);
            $footer_title = $this->wrap_title;
            $footer_url = 'https://wrap.rocks/';
            $footer_branding = ( $footer_logo ) ? '<img class="logo" src="' . $footer_logo . '" alt="' . ($footer_title) . '">' : $footer_title;
        } else {
            $footer_url = WRAP_URL;
            $footer_branding = $this->branding;
        }
        $footer_branding = (empty($footer_branding)) ? '' : '<div class=branding><a href="' . $footer_url . '" class="branding">' . $footer_branding . '</a></div>';
        $footer = '<div class=version>' . WRAP_VERSION . '</div>' . $footer_branding;

        $queued_meta = '';
        foreach (self::$scripts as $key => $src) {
            $script_url = $this->update_cache($src);
            $queued_meta .= '<script id="js-' . $key . '" src="' . $script_url . '"></script>' . PHP_EOL;
        }
        foreach (self::$styles as $key => $src) {
            $css_url =  $this->update_cache($src);
            $queued_meta .= '<link id="css-' . $key . '" href="' . $css_url . '" rel="stylesheet">' . PHP_EOL;
        }

        $output = Wrap::process_template('templates/page.html', array(
            '{title}' => $this->title,
            '{description}' => $description,
            '{keywords}' => $keywords,
            '{content}' => $this->content,
            '{footer}' => $footer,
            '{branding}' => $this->branding,
            '{breadcrumb}' => $this->breadcrumb,
            '{nav}' => $this->nav,
            '{queued_meta}' => $queued_meta,
        ));
        echo $output;
    }

    /**
     * process_template
     * 
     * Process ah HTML template with data
     * 
     * @param string $template      Path to the template file
     * @param array $data           Array of tag/values to replace in the template
     * 
     * @return string               The processed template
     * 
     * @since 5.0.1
     */
    public static function process_template($template, $data = []) {
        $template = file_get_contents(WRAP_DIR . '/' . $template);
        return strtr($template, $data);
    }

    /**
     * getVersion
     * 
     * Return the version of Wrap
     * 
     * @return string
     * 
     * @since 5.0.1
     */
    public static function getVersion() {
        return WRAP_VERSION;
    }

    /**
     * debug
     * 
     * Output a message to the browser and to the error log
     * 
     * @param string $message
     * @return void
     * 
     * @since 5.0.1
     */
    static function debug($message) {
        echo "$message<br>";
        error_log($message);
    }

    /**
     * filename2name
     * 
     * Convert a filename to a human readable name
     * 
     * @param string $string
     * @return string
     * 
     * @since 5.0.1
     */
    public static function filename2name($string) {
        $string = pathinfo($string, PATHINFO_FILENAME);
        $string = str_replace('_', ' ', $string);
        $string = ucfirst($string);
        $string = preg_replace('/(?<=[a-zA-Z])\d+/', ' $0', $string); // Ajoute un espace avant le nombre si précédé par une lettre
        $string = preg_replace('/\d+(?=[a-zA-Z])/', '$0 ', $string); // Ajoute un espace après le nombre si suivi par une lettre
        $string = trim($string);
        return $string;
    }

    /**
     * santitize_slug
     * 
     * Sanitize a string to be used as a slug (valid for classes, ids, urls, etc.)
     * 
     * @param string $string
     * @return string
     * 
     * @since 5.0.1
     */
    public static function santitize_slug($string) {
        $string = strtolower($string);
        $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
        $string = preg_replace('/[^a-z0-9]+/', '-', $string);
        $string = trim($string, '-');
        return $string;
    }
}

// Let's get it started in here!
$wrap = new Wrap();
