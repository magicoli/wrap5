<?php

defined('WRAP_VERSION') or die('No direct script access.');

/**
 * File class
 * 
 * Provides methods to handle files.
 * 
 * @package Wrap
 * @version 5.0.1
 * @since 5.0.1
 * 
 * @property string $path           Full local path to the file
 * @property string $path_url       Path to the file, relative to site url
 * @property string $name           File name
 * @property string $extension      File extension
 * @property string $mime_type      File mime type
 * @property array $tags            File tags
 * 
 * In the provided code, the Wrap_File properties that are accessed directly are
 *  $name, $extension, $mime_type, and a method get_thumb().


 * 
 */
class Wrap_File {
    public $name;
    public $extension;
    public $mime_type;
    private $path;
    private $path_url;
    private $tags = [];
    
    public function __construct($requested_url) {
        $this->path = WRAP_DATA . urldecode($requested_url);
        $this->path_url = $requested_url;
        $this->name = Wrap::filename2name($this->path);
        $this->extension = pathinfo($this->path, PATHINFO_EXTENSION);
        $this->mime_type = $this->getMimeType($this->extension);
    }

    /**
     * getMimeType
     * 
     * Use Mimey library to get the mime type of a file extension.
     * 
     * @param string $extension
     * @return string
     * 
     * @since 5.0.1
     */
    private function getMimeType($extension) {
        static $mimes = null;
        if ($mimes === null) {
            $mimes = new \Mimey\MimeTypes;
        }
        return $mimes->getMimeType($extension);
    }

    /**
     * get_thumb
     * 
     * If file is a video, generate a thumbnail if none exist or if the video file is newer than the thumbnail
     * 
     * @param bool $output_html
     * @return string
     * 
     * @since 5.0.1
     */
    public function get_thumb( $output_html = true ) {
        $thumbnail = '/.thumbs/' . $this->path_url . '.jpg';
        $thumbfile = WRAP_DATA . urldecode($thumbnail);
        $thumburl = Wrap::build_url($thumbnail);

        if (strpos($this->mime_type, 'video') !== false) {
            if (!file_exists($thumbfile) || filemtime($this->path) > filemtime($thumbfile)) {
                // Create the cache directory if it doesn't exist
                if (!is_dir(dirname($thumbfile))) {
                    mkdir(dirname($thumbfile), 0777, true);
                }
                // legacy thumbnail is {file directory}/.browsercache/{$filename without extension}-large.jpg
                // or .browsercache/{$filename without extension}-thumb.jpg, prefer large.
                // if found, copy to $thumbfile
                $legacy_thumb_base = dirname($this->path) . '/.browsercache/' . pathinfo($this->path, PATHINFO_FILENAME);
                $legacy_thumb_large = $legacy_thumb_base . '-large.jpg';
                $legacy_thumb_thumb = $legacy_thumb_base . '-thumb.jpg';
                if (file_exists($legacy_thumb_large)) {
                    copy($legacy_thumb_large, $thumbfile);
                } elseif (file_exists($legacy_thumb_thumb)) {
                    copy($legacy_thumb_thumb, $thumbfile);
                } else {
                    // Generate a thumbnail from the video file
                    $ffmpeg = FFMpeg\FFMpeg::create();
                    $video = $ffmpeg->open($this->path);
                    $frame = $video->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(0));
                    $frame->save($thumbfile);
                }
            }
        }

        if (file_exists($thumbfile) && filesize($thumbfile) > 0) {
            if($output_html) {
                return '<img class="thumbnail" src="' . $thumburl . '" alt="' . $this->name . '">';
            } else {
                return $thumburl;
            }
        }
    }

    /**
     * Raw file output
     * 
     * Outputs the file to the browser
     * 
     * @return void
     * 
     * @since 5.0.1
     */
    public function output() {
        $file = $this->path;
        $size = filesize($file);
        $start = 0;
        $end = $size - 1;

        header('Content-Type: ' . $this->mime_type);
        header("Accept-Ranges: bytes");
        if (isset($_SERVER['HTTP_RANGE'])) {
            $range = $_SERVER['HTTP_RANGE'];
            list($param, $range) = explode('=', $range);
            if (strtolower(trim($param)) != 'bytes') {
                header('HTTP/1.1 400 Invalid Request');
                exit;
            }
            list($from, $to) = explode('-', $range);
            if ($from) {
                $start = intval($from);
            }
            if ($to) {
                $end = intval($to);
            }
            $length = $end - $start + 1;
            header('HTTP/1.1 206 Partial Content');
            header("Content-Length: $length");
            header("Content-Range: bytes $start-$end/$size");
        } else {
            header("Content-Length: $size");
        }

        $fp = fopen($file, 'rb');
        fseek($fp, $start);
        while ($start <= $end) {
            set_time_limit(0);
            print fread($fp, min(1024, $end - $start + 1));
            $start += 1024;
        }
        fclose($fp);
        die();
    }
}
