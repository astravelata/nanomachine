<?php 

/**
 * Convert file size to human readable format.
 *
 * @param int $size The file size in bytes.
 * @return string The human readable file size.
 */
function humanize_filesize($size) {
  $units  = ['B', 'K', 'M', 'G', 'T'];
  for ($i = 0; $size >= 1024 && $i < 4; $i++) {
    $size /= 1024;
  }
  return number_format($size, 2) . ' ' . $units[$i];
}

/**
 * Get the relative path from the root.
 *
 * @param string $absolutePath The absolute path.
 * @param string $root The root path.
 * @return string The relative path.
 */
function get_relative_path($absolutePath, $root) {
  return ltrim(substr($absolutePath, strlen($root)), '/');
}

/**
 * Convert Unix timestamp to human readable time ago format.
 *
 * @param int $unix_timestamp The Unix timestamp.
 * @return string The human readable time ago.
 */
function human_time_ago($unix_timestamp) {
   $seconds_ago = time() - $unix_timestamp;

    if ($seconds_ago >= 2 * 60 * 60 * 24 * 365) {
        return "a long time ago";
    } elseif ($seconds_ago >= 60 * 60 * 24) {
        $days_ago = round($seconds_ago / (60 * 60 * 24));
        return "$days_ago " . ($days_ago == 1 ? "day" : "days") . " ago";
    } elseif ($seconds_ago >= 60 * 60) {
        $hours_ago = round($seconds_ago / (60 * 60));
        return "$hours_ago " . ($hours_ago == 1 ? "hour" : "hours") . " ago";
    } elseif ($seconds_ago >= 2 * 60) {
        return round($seconds_ago / 60) . " minutes ago";
    } else {
        return "just now";
    }
}

/**
 * Get the parent directory of a path.
 *
 * @param string $path The path.
 * @return string The parent directory.
 */
function get_parent_directory($path) {
  return substr($path, 0, strrpos($path, '/'));
}

/**
 * Get a directory tree of a path.
 *
 * @param string $path The path.
 * @param string $base The base directory.
 * @return array The directory tree.
 */
function get_directory_tree(string $path, string $base=''): array {
    $tree = [];
    $parent_dir = get_parent_directory($path);
    if ($path !== $base && $parent_dir !== $base) {
        $tree[] = [
            'name' => '..',
            'href' => get_relative_path(rtrim($parent_dir, '/'), $base),
            'type' => 'dir',
        ];
    }

    if (!is_dir($path) && !is_file($path)) {
        return [[
            'name' => '/',
            'href' => '',
            'type' => 'dir',
        ]];
    }

    foreach (glob($path . '/*') as $file) {
        $filemtime = filemtime($file);
        $filesize = filesize($file);
        $filectime = filectime($file);
        $tree[] = [
            'name' => basename($file),
            'href' => '/' . get_relative_path(rtrim($file, '/'), $base),
            'type' => is_dir($file) ? 'dir' : 'file',
            'pathinfo' => pathinfo($file),
            'mtime' => $filemtime,
            'ctime' => $filectime,
            'human_mtime' => human_time_ago($filemtime),
            'human_ctime' => human_time_ago($filectime),
            'filesize' => $filesize,
            'human_filesize' => humanize_filesize($filesize),
        ];
    }
    
    return $tree;
}

/**
 * Get an emoji for a file type based on the file extension.
 *
 * @param string $file_extension The file extension.
 * @return string The emoji.
 */
function get_emoji_for_file_type($file_extension) {
    $emoji = "";
    switch ($file_extension) {
        case 'jpeg':
        case 'png':
        case 'jpg':
        case 'tiff':
        case 'bmp':
        case 'gif':
        case 'apng':
        case 'webp':
            $emoji = "🖼️";
            break;
        case 'flac':
        case 'aea':
        case 'mp3':
        case 'ogg':
        case 'opus':
        case 'wav':
        case 'aiff':
        case 'm4a':
            $emoji = "🎵";
            break;
        case 'mp4':
        case 'webm':
        case 'mkv':
        case 'm4v':
        case 'mov':
            $emoji = "🎥";
            break;
        default:
            $emoji = "📄";
            break;
    }
    return $emoji;
}

