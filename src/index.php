<?php

/*
    An ugly and functional router / view builder.
*/

namespace Astravelata\Nanomachine;

$start = hrtime(true);

require_once __DIR__ . '/../vendor/autoload.php';

use Astravelata\Nanomachine\Cache\PageCache;
use Astravelata\Nanomachine\Formatters\MarkdownFormatter;
use Astravelata\Nanomachine\Utility;

global $CONFIG;
$CONFIG = parse_ini_file(
    Utility::get_absolute_path(
        Utility::join_paths(
            dirname(__FILE__), '..', 'config/config.ini')));

// some constants
define("INDEX_FILES", ["index.html", "index.md", "README.md", "readme.md", "README.txt", "README"]);
define("DEBUG", ((isset($CONFIG['debug']) ? $CONFIG['debug'] == 1 : false) || getenv("DEBUG") === "1" || php_sapi_name() == 'cli-server'));
define("ENABLE_FILE_CACHE", $CONFIG['use_file_cache'] == 1);
define("SACRIFICE_SPEED_FOR_BEAUTY", $CONFIG['sacrifice_speed_for_beauty']);
define("NANOMACHINE_VERSION", 0.1);

define("DOCUMENT_EXTENSIONS", ["md", "txt", "htm", "html"]);
define("IMAGE_EXTENSIONS", ['jpg', 'jpeg', 'png', 'gif', 'webp', 'tiff', 'bmp']);
define("VIDEO_EXTENSIONS", ['m4v', 'mkv', 'mp4', 'webm', 'avi', 'mov']);
define("AUDIO_EXTENSIONS", ['mp3', 'flac', 'm4a', 'aac', 'ogg', 'wav', 'aea', 'opus']);

// Define the path
$path = strtok($_SERVER['REQUEST_URI'], '?#');

$cache = new Cache\PageCache();
if (!ENABLE_FILE_CACHE) {
    error_log("Disabling file caching based upon config.");
    $cache->disable();
}

// making dev a bit easier for now
if (php_sapi_name() == 'cli-server' || isset($_GET['raw'])) {
    // serve static assets first
    if (isset($_GET['raw']) || strpos($path, "/static") === 0 || strpos($path, "/_") === 0) {

        if (strpos($path, "/static") === 0) {
            $ROOT_PATH = realpath(Utility::join_paths(dirname(__FILE__), 'templates'));
        } else {
            $ROOT_PATH = realpath(Utility::join_paths(dirname(__FILE__), '..', 'data'));
        }
        $filePath = realpath(Utility::join_paths($ROOT_PATH, $path));
        if (!file_exists($filePath)) {
            Utility::http_error(404);
            exit;
        }
        // set mime type in header
        $suffix = pathinfo($path)['extension'];
        $content_type = "text/plain";
        switch($suffix) {
            case 'css':
                $content_type = "text/css";
                break;
            case 'js':
                $content_type = "text/javascript";
                break;
            default:
                $content_type = mime_content_type($filePath);
                break;
        } 
        header('Content-Type: '. $content_type);
        $handle = fopen($filePath, "rb");
        while (!feof($handle)) {
            echo fread($handle, 8192);
            ob_flush();
            flush();
        }
        fclose($handle);
        return;
    }
}

// Define the file path
$ROOT_PATH = Utility::get_absolute_path(Utility::join_paths(dirname(__FILE__), '..', '..', 'data'));
$filePath = Utility::get_absolute_path(Utility::join_paths($ROOT_PATH, $path));
if ($filePath == "") {
    $filePath = $ROOT_PATH;
}

// start output buffering
ob_start();

$data = $cache->get($filePath);
if ($data !== null) {
    echo $data;
    ob_flush();
    exit;
}

// set core template data
global $TPL_DATA;
$TPL_DATA = [
    'root' => $ROOT_PATH,
    'path' => $filePath,
    'relpath' => str_replace($ROOT_PATH, "", $filePath),
    'pathinfo' => pathinfo($filePath),
    'file' => basename($filePath),
    'type' => is_dir($filePath) ? 'dir' : 'file',
    'template' => Utility::choose_template($filePath),
    'caching' => $cache->enabled(),
    'is_media' => false,
    'media_type' => null,
    'media_file_path' => null,
    'body' => '',
    'title' => '',
    'cover_photo' => null,
    'cover_photo_alt' => '',
    'cover_photo_caption' => '',
    'site_title' => $CONFIG['title'],
    'description' => $CONFIG['description'],
    'author' => $CONFIG['author'],
    'author_email' => $CONFIG['author_email'],
    'url' => $CONFIG['url'],
    'keywords' => ''
];

if ($TPL_DATA['relpath'] === '') {
    $TPL_DATA['relpath'] = '/';
}

if (strpos($filePath, $ROOT_PATH) !== 0) {
    Utility::http_error(404);
    exit;
}

if (is_dir($filePath)) {
    // Directories can have index files.
    foreach(INDEX_FILES as $index_file) {
        $index_file_path = Utility::get_absolute_path(Utility::join_paths($filePath, $index_file));
        $index_file_extension = explode(".", $index_file)[1];
        $found = false;
        if (file_exists($index_file_path)) {
            switch ($index_file_extension) {
                case 'md':
                    $found = true;
                    $markdown_content = Formatters\MarkdownFormatter::parse_markdown($index_file_path);
                    $TPL_DATA['body'] = $markdown_content['content'];
                    Utility::templatize_front_matter($markdown_content['front_matter'], $TPL_DATA);
                    break;
                case 'htm':
                case 'html':
                default:
                    $found = true;
                    $TPL_DATA['body'] = file_get_contents($index_file_path);
                    break;
            }
        }
        if ($found) {
            break;
        }
    }

    include 'src/templates/partial/header.php'; 

    // home is a special template
    if ($ROOT_PATH === $filePath) {
        require_once "src/templates/home.php";
    }
    else {
        require_once "src/templates/directory.php";
    }
}
else if (file_exists($filePath)) {
    // don't allow direct navigation to index files
    if (in_array(basename($filePath), INDEX_FILES)) {
        Utility::http_error(403);
        exit;
    }

    $ext = pathinfo($filePath)['extension'];
    if (in_array($ext, DOCUMENT_EXTENSIONS)) {
        switch($ext) {
            case 'md':
                $markdown_content = Formatters\MarkdownFormatter::parse_markdown($filePath);
                $TPL_DATA['body'] = $markdown_content['content']; 
                Utility::templatize_front_matter($markdown_content['front_matter'], $TPL_DATA);
                break;
    
            case 'html':
                $TPL_DATA['body'] = file_get_contents($filePath);
                break;

            default:
                error_log("Unhandled document extension: " . $ext);
                Utility::http_error(400);
                break;
        }
    }
    elseif (in_array($ext, IMAGE_EXTENSIONS)) {
        $TPL_DATA['is_media'] = true;
        $TPL_DATA['media_type'] = 'image';
        $TPL_DATA['media_file_path'] = $filePath;
    }
    elseif (in_array($ext, AUDIO_EXTENSIONS)) {
        $TPL_DATA['is_media'] = true;
        $TPL_DATA['media_type'] = 'audio';
        $TPL_DATA['media_file_path'] = $filePath;
    }
    elseif (in_array($ext, VIDEO_EXTENSIONS)) {
        $TPL_DATA['is_media'] = true;
        $TPL_DATA['media_type'] = 'video';
        $TPL_DATA['media_file_path'] = $filePath;
    } else {
        error_log("Don't know how to handle file " . $filePath );
        Utility::http_error(400);
    }

    $TPL_DATA['mtime'] = filemtime($filePath);
    $TPL_DATA['ctime'] = filectime($filePath);

    $tpl = Utility::choose_template($filePath);
    include 'src/templates/partial/header.php'; 
    include 'src/templates/' . $tpl;
}
else {
    print("404");
    Utility::http_error(404);
}

$end = hrtime(true);
$TPL_DATA['render_time'] = ($end - $start) / 1000 / 1000;
if ($cache->enabled()) {
    $TPL_DATA['vendor_string'] =  "php ". PHP_VERSION . " / caching / ". "nanomachine ". NANOMACHINE_VERSION;
} else {
    $TPL_DATA['vendor_string'] = "php ". PHP_VERSION . " / ". "nanomachine ". NANOMACHINE_VERSION;
}


require_once 'src/templates/partial/footer.php';

$cache->set($filePath, ob_get_contents());
ob_flush();

