<?php

namespace Astravelata\Nanomachine;

class Utility {

    /**
    * Sanitizes a given request URI by filtering each of its components.
    *
    * This function splits the given URI into its components (scheme, host, port, user, pass, path, query, fragment),
    * sanitizes each component using appropriate filter, and then reassembles the sanitized components back into a URI.
    *
    * @param string $request_uri The URI to be sanitized.
    * @return string The sanitized URI.
    */
    public static function sanitize_request_uri($request_uri) {
        $parts = parse_url($request_uri);

        $scheme = isset($parts['scheme']) ? filter_var($parts['scheme'], FILTER_SANITIZE_URL) . '://' : '';
        $host = isset($parts['host']) ? filter_var($parts['host'], FILTER_SANITIZE_URL) : '';
        $port = isset($parts['port']) ? ':' . filter_var($parts['port'], FILTER_SANITIZE_NUMBER_INT) : '';
        $user = isset($parts['user']) ? filter_var($parts['user'], FILTER_SANITIZE_URL) . '@' : '';
        $pass = isset($parts['pass']) ? ':' . filter_var($parts['pass'], FILTER_SANITIZE_URL) : '';
        $path = isset($parts['path']) ? filter_var($parts['path'], FILTER_SANITIZE_URL) : '';
        $query = isset($parts['query']) ? '?' . filter_var($parts['query'], FILTER_SANITIZE_URL) : '';
        $fragment = isset($parts['fragment']) ? '#' . filter_var($parts['fragment'], FILTER_SANITIZE_URL) : '';

        $sanitized_uri = $scheme . $user . $pass . $host . $port . $path . $query . $fragment;

        return $sanitized_uri;
    }

 /**
  * Join multiple path segments into a single path string.
  * This function prevents directory traversal by removing '../' from the segments.
  * It also trims trailing directory separators from each segment.
  * If the first segment is a directory separator, it is preserved at the start of the resulting path.
  *
  * @param string ...$parts The path segments to join.
  * @return string The joined path.
  */
   public static function join_paths(...$parts): string {
    if (sizeof($parts) === 0) {
        return '';
    }
     
     $prefix = ($parts[0] === DIRECTORY_SEPARATOR) ? DIRECTORY_SEPARATOR : '';
     
     $processed = array_filter(
        array_map(function ($part) {
            $part = rtrim($part, DIRECTORY_SEPARATOR);
            $part = str_replace(['../', '..\\'], '', $part); // Prevent directory traversal
            return $part;
        }, $parts),
        function ($part) {
             return !empty($part);
        }
     );
     
     return $prefix . implode(DIRECTORY_SEPARATOR, $processed);
 }

    /**
     * This function takes a relative file path and converts it into an absolute file path.
     * It standardizes the directory separators, removes any "." or ".." references,
     * and then rebuilds the path.
     *
     * @param string $path The relative file path to be converted.
     * @return string The absolute file path.
     */
    public static function get_absolute_path($path) {
        // Replace all forward/backward slashes with the DIRECTORY_SEPARATOR constant
        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
        
        // Split the path into parts, removing empty parts
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        
        // Initialize an array to hold the parts of the absolute path
        $absolutes = array();
        
        // Iterate over each part of the path
        foreach ($parts as $part) {
            // If the part is a ".", ignore it
            if ('.' == $part) continue;
            
            // If the part is a "..", remove the last part from the absolute path
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                // Otherwise, add the part to the absolute path
                $absolutes[] = $part;
            }
        }
        
        // Join the parts of the absolute path with the DIRECTORY_SEPARATOR and return
        return '/' . implode(DIRECTORY_SEPARATOR, $absolutes);
    }

    /**
     * Sets the HTTP response status header according to the provided HTTP status code.
     *
     * @param int $code HTTP status code.
     *
     * @return void
     */
    public static function http_error(int $code) {
        $status = '400 Bad Request';
    
        switch ($code) {
            case 403:
                $status = '403 Forbidden';
                break;
            case 404:
                $status = '404 Not Found';
                break;
            case 500:
                $status = '500 Internal Server Error';
                break;
        }
    
        header("HTTP/1.1 $status");
    }

    
    /**
     * This function chooses a template based on the root category of a file path.
     * The root category is defined as the directory immediately following the 'data' directory in the path.
     * If the root category is 'media', 'code', or 'links', the corresponding template file is returned.
     * If the root category is anything else, the 'generic.php' template is returned.
     * If '00_data' is not found in the path, an empty string is returned.
     *
     * @param string $filePath The file path to analyze.
     *
     * @return string The name of the template file.
     */
    public static function choose_template(string $filePath): string {
        $paths = explode(DIRECTORY_SEPARATOR, $filePath);
        $root_category = "";
    
        if (($index = array_search('data', $paths)) !== false && isset($paths[$index + 1])) {
            $root_category = $paths[$index + 1];
        }
    
        $templates = [
            "media" => "media.php",
            "code" => "code.php",
            "links" => "links.php",
        ];

        return $templates[$root_category] ?? "generic.php";
    }

    
    /**
     * This function is used to templatize the front matter.
     * It maps the keys in the front matter array to the corresponding keys in the template data array.
     * If a key exists in the front matter array, its value is copied to the template data array.
     *
     * @param array|null $front_matter An associative array containing the front matter data.
     * @param array $tpl_data The array to which the front matter data is to be copied.
     * @return void
     */
    public static function templatize_front_matter(?array $front_matter, array &$tpl_data): void 
    {
        if (!is_array($front_matter)) {
            return;
        }

        $TEMPLATE_TAG_MAP = [
            'title',
            'description',
            'summary',
            'tags',
            'date'
        ];

        foreach ($TEMPLATE_TAG_MAP as $tag) {
            if (array_key_exists($tag, $front_matter)) {
                $tpl_data[$tag] = $front_matter[$tag];
            }
        }
    }
}
