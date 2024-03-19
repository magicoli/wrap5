<?php

defined('WRAP_VERSION') or die('No direct script access.');

/**
 * Folder class
 * 
 * Provides methods to handle folders.
 * 
 * @package Wrap
 * @version 5.0.1
 * @since 5.0.1
 * 
 * @property string $path           Full local path to the folder
 * @property string $path_url       Path to the folder, relative to site url
 * @property array $childs          List of child folders
 * @property array $files           List of files in the folder
 * @property array $parents         List of parent folders
 * @property string $name           Folder name
 * @property string $page_url       Current page url
 * @property array $params          Folder parameters
 * @property bool $stop_navigation  Stop navigation flag (child folders considered as root)
 * @property string $minisite_root  Minisite root path
 */
class Wrap_Folder {
    private $path;
    private $path_url;
    private $childs = [];
    private $files = [];
    private $parents = [];
    private $name;
    private $page_url;
    private $params = [];
    private $stop_navigation = false;
    public $minisite_root;

    public function __construct($requested_url) {

        if(empty($requested_url)) {
            $requested_url = $_SERVER['REQUEST_URI'];
        }
        $this->page_url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $this->path_url = $requested_url;
        $this->path = WRAP_DATA . urldecode($requested_url);
        
        if(!file_exists($this->path)) {
            return false;
        }
        $this->parents = $this->scan_parents();
        $this->name = Wrap::filename2name(basename($this->path));
        $this->load_params();
        $this->scan_folder();
    }

    /**
     * load_params
     * 
     * Load folder parameters from .wrap.json file
     * 
     * @return void
     * 
     * @since 5.0.1
     */
    private function load_params() {
        $folder_params = $this->path . '/.wrap.json';
        if (file_exists($folder_params)) {
            $json = file_get_contents($folder_params);
            $this->params = json_decode($json, true);
            if(isset($this->params['name'])) {
                $this->name = $this->params['name'];
            }
            if(isset($this->params['stop_navigation'])) {
                $this->stop_navigation = $this->params['stop_navigation'];
            }

        }
    }

    /**
     * stop_navigation
     * 
     * Check if the folder has the stop_navigation parameter set to true.
     * stop_navigation will 
     *  - prevent the folder from being displayed in the navigation tree and breadcrumb.
     *  - prevent the folder from being used as a root for child folders.
     *  - prevent the folder from being used as a parent for child folders.
     *  - make child folders considered as root.
     * 
     * @return bool
     * 
     * @since 5.0.1
     */
    public function stop_navigation() {
        return $this->stop_navigation;
    }

    /**
     * get_params
     * 
     * Get folder parameters (for use from other classes).
     * 
     * @return array
     * 
     * @since 5.0.1
     */
    public function get_params() {
        return $this->params;
    }

    /**
     * scan_folder
     * 
     * Scan the folder for files and child folders
     * 
     * @return void
     * 
     * @since 5.0.1
     */
    public function scan_folder() {
        $content = [];

        $files = scandir($this->path);
        $ignore_files = array("playlist.php", "browser.prefs");
        if(is_array($files)) {
            $childs = [];
            foreach ($files as $file) {
                if ($file[0] == '.' || $file[0] == '_'  || $file[0] == '#' || substr($file, -1) == '~' ) {
                    continue;
                }
                if (in_array($file, $ignore_files)) {
                    continue;
                }
                if (is_dir($this->path . '/' . $file)) {
                    $childs[] = $file;
                } else {
                    $this->files[] = $file;
                }
            }
            if( !empty($childs) && ! empty ($this->params['folders']) && is_array($this->params['folders']) ) {
                foreach($this->params['folders'] as $folder => $name) {
                    if(in_array($folder, $childs)) {
                        $this->childs[] = $folder;
                    }
                }
                // Add non sorted folders at the end
                $this->childs = array_merge($this->childs, $childs);
            } else {
                $this->childs = $childs;
            }
        }
    }
    
    /**
     * build_content
     * 
     * Build the page content html
     * 
     * @return string       Formatted html
     * 
     * @since 5.0.1
     */
    public function build_content() {
        $icons = Wrap::icons();
        // $content = '<ul class="files">';
        $playlist = [];
        $id=0;
        $p=0;

        
        // The files actually in the folder
        $files = $this->files;
        $files_map = [];
        
        $params = self::get_params();
        if(isset($params['files'])) {
            // First, we create a simple array of the files in the params array
            foreach ($params['files'] as $file) {
                $files_map[$file['filename']] = $file;
            }
            
            // Then we remove from $files_map the files not in the folder
            foreach ($files_map as $file => $data) {
                if (!in_array($file, $files)) {
                    unset($files_map[$file]);
                }
            }
        }

        // Finally we add the remaining files, present in the folder but not in the params array
        foreach ($files as $file) {
            if (!isset($files_map[$file])) {
                $files_map[$file] = [
                    'filename' => $file,
                    'name' => Wrap::filename2name($file),
                    'tags' => [],
                ];
            }
        }
        $items = '';
        foreach ($files_map as $filename => $data) {
            $classes = [];

            $id++;
            $idx = '';
            // error_log("Looking for file " . $this->path_url . '/' . $filename);
            $file = new Wrap_File($this->path_url . '/' . urlencode($filename));
            $file->name = isset($data['name']) ? $data['name'] : $file->name;

            $extension = $file->extension;
            $thumb = $file->get_thumb();
            $icon = isset($icons[$extension]) ? $icons[$extension] : null;
            if(empty($thumb)) {
                $thumb = $icon;
            }
            $classes[] = "file";
            $classes[] = "list-item";

            $tags = [];
            if(isset($data['tags']) && is_array($data['tags'])) {
                foreach ($data['tags'] as $tag) {
                    $tag_slug = Wrap::santitize_slug($tag);
                    $tags[$tag_slug] = $tag;
                    $this->tags[$tag_slug] = $tag;
                    $classes[] = 'tag-' . Wrap::santitize_slug($tag);
                }
            }
            if(isset($this->tags) && is_array($this->tags) ) {
                $this->tags = array_merge($this->tags, $tags);
            } else {
                $this->tags = $tags;
            }
            // empty($file->tags) ? $classes[] = 'no-tags' : $classes[] = 'has-tags';

            if (in_array($extension, ['mp3', 'mov', 'wav', 'ogg', 'mp4', 'webm'])) {
                $playlist[] = array(
                    'sources' => array(
                        'src' => Wrap::build_url ($this->path_url . '/' . $filename),
                        'type' => $file->mime_type,
                    ),
                    'name' => $file->name,
                    'poster' => $file->get_thumb(false),
                );
                $classes[] = 'playable';
                // $classes[] = str_replace('/', '-', $file->mime_type);
                $idx = $p;
                $p++;
            }

            $items .= Wrap::process_template('templates/page-list-item.html', array(
                '{id}' => $id,
                '{idx}' => $idx,
                '{thumb}' => $thumb,
                '{name}' => $file->name,
                '{tags}' => empty($tags) ? '' : '<span class=tag>' . join('</span> <span class=tag>', $tags) . '</span>',
                '{classes}' => join(' ', $classes),
            ));
        }
        
        $content = Wrap::process_template('templates/page-list.html', array(
            '{items}' => $items,
        )); 

        if(!empty($playlist)) {
            Wrap::queue_script('player', '/dist/player.js');
            Wrap::queue_style('player', '/dist/player.css');

            // $playlist_script = "<script>setupPlayer(" . json_encode($playlist) . ");</script>";
            // $player = '<dialog id="player-modal"><div id=player><video id="player" class="video-js vjs-default-skin" controls preload="auto" data-setup="{}"></video></div></dialog>';
            // $content .= $player . $playlist_script;

            $content .= Wrap::process_template('templates/page-player.html', array(
                '{playlist_json}' => json_encode($playlist),
            ));
        }

        return $content;
    }

    /**
     * scan_parents
     * 
     * Get the list of parent folders
     * 
     * @return array
     * 
     * @since 5.0.1
     */
    public function scan_parents() {
        static $parents = null;
        
        if ($parents !== null) {
            return $parents;
        }
        
        $currentPath = $this->path;
        $parentPath = dirname($currentPath);
        $parents = [];

        while ($parentPath != WRAP_DATA && strpos($parentPath, WRAP_DATA) !== false && $parentPath != "/") {
            $parent_url = str_replace(WRAP_DATA, '', $parentPath);
            $folder = new Wrap_Folder($parent_url);
            if($folder->stop_navigation() ) {
                break;
            }
            $parents[] = $parent_url;
            $currentPath = $parentPath;
            $parentPath = dirname($parentPath);
        }
        $this->minisite_root = $currentPath;
        $parents = array_reverse($parents);
        $this->parents = $parents;
        return $parents;
    }
    
    /**
     * get_childs
     * 
     * Get the list of child folders (for use from other classes).
     * 
     * @return array
     * 
     * @since 5.0.1
     */
    public function get_childs() {
        return $this->childs;
    }
    
    /**
     * build_breadcrumb
     * 
     * Build the breadcrumb html
     * 
     * @param bool $include_current
     * @return string
     * 
     * @since 5.0.1
     */
    public function build_breadcrumb( $include_current = false ) {
        $parents = $this->scan_parents();
        
        $breadcrumb = '<ul>';
        foreach ($this->parents as $parent) {
            $folder = new Wrap_Folder($parent);
            $breadcrumb .= '<li><a href="' . Wrap::build_url($parent) . '">' . $folder->get_name() . '</a></li>';
        }
        if($include_current === true) {
            $breadcrumb .= '<li>' . $this->get_name() . '</li>';
        }
        $breadcrumb .= '</ul>';
        return $breadcrumb;
    }

    /**
     * get_name
     * 
     * Get the folder name (for use from other classes).
     * 
     * @param string $folder
     * @return string
     * 
     * @since 5.0.1
     */
    public function get_name( $folder = null) {
        $folder = $folder ?? $this->name;
        return basename($folder);
    }

    /**
     * scan_nav_tree
     * 
     * Scan the folder and build the navigation tree, including parent and each up level siblings
     * Instantiates Wrap_folder for each parent to get their childs
     * 
     * @return string
     * 
     * @since 5.0.1
     */
    public function scan_nav_tree() {
        $parents = $this->scan_parents();
        $parents[] = $this->path_url;
        $tree = array();
        $is_root= true;
        foreach ($parents as $path) {
            $parent = new Wrap_Folder($path);
            if($parent->stop_navigation() ) {
                continue;
            }
            $path_parts = explode('/', trim($path, '/'));
            $current = &$tree;
            foreach ($path_parts as $part) {
                if (!isset($current[$part])) {
                    $current[$part] = array();
                }
                $current = &$current[$part];
            }
            $parent_childs = $parent->get_childs();
            $current = array_fill_keys($parent_childs, null);
        }

        // Call the function with the $tree variable
        $nestedList = $this->build_nav_tree($tree);
        return $nestedList;
    }
    
    /**
     * build_nav_tree
     * 
     * Build the navigation tree html
     * 
     * @param array $tree
     * @param string $parent
     * @return string
     * 
     * @since 5.0.1
     */
    public function build_nav_tree($tree, $parent=null) {
        $html = '';
        foreach ($tree as $key => $value) {
            $path = $parent . '/' . $key;
            $folder = new Wrap_Folder($path);
            $parentFolder = new Wrap_Folder($parent);
            $classes = ($path === $this->page_url) ? 'active' : null;
            $child_html = '';
            if (!empty($value)) {
                $child_html = $this->build_nav_tree($value, $path);
            }
            if($parentFolder->stop_navigation() || $folder->stop_navigation() ) {
                $html .= $child_html;
            } else {
                $html .= sprintf(
                    '<li class="%s"><a href="%s">%s</a>%s</li>',
                    $classes,
                    Wrap::build_url($path),
                    $folder->get_name(),
                    $child_html,
                );
            }
        }
        if (!empty($html && ! preg_match('/^<ul[ >]/', $html)) ) {
            $html = '<ul class=nav>' . $html . '</ul>';
        }
        return $html;
    }

    /**
     * get_nav
     * 
     * Get the navigation tree (for use from other classes).
     * 
     * @return string
     * 
     * @since 5.0.1
     */
    public function get_nav() {
        $content = $this->scan_nav_tree();
        return $content;
    }
}
