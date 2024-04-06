<?php
    use Kiwilan\Audio\Audio;
    
    include 'src/templates/functions/directory.php';
?>
<nav id="main-nav" aria-label="main">
    <h3>
        <?php if ($TPL_DATA['type'] === 'dir'): ?>
        index of
        <?php else: ?>
        viewing 
        <?php endif; ?>
        <?php echo $TPL_DATA['relpath']; ?>
    </h3>
    <?php 
        if (isset($TPL_DATA['pathinfo']) && $TPL_DATA['pathinfo'] !== null) {
            $dirs = get_directory_tree($TPL_DATA['path'], $TPL_DATA['root']);
            // sort by type
            usort($dirs, function ($item1, $item2) {
                // til this is called the "spaceship operator"
                return $item1['type'] <=> $item2['type'];
            });
            // filter out things we don't want to see
            $dirs = array_filter($dirs, function ($dir) {
                return ($dir['name'] != '.' &&
                        !(
                            $dir['name'][0] === '.' || 
                            $dir['name'][0] === '_' ||
                            strtolower($dir["name"]) === 'readme.txt' ||
                            strtolower($dir['name']) === 'readme.md' ||
                            strtolower($dir['name']) === 'index.html' ||
                            strtolower($dir['name']) === 'index.md' ||
                            strtolower($dir['name']) === 'robots.txt' ||
                            strtolower($dir['name']) === 'favicon.ico'
                        ));
            });
        }
    ?>

    <?php if ($TPL_DATA['relpath'] !== '/'): ?>
    <ul class="directory-navigation">
        <li><a href="<?php echo $TPL_DATA['relpath']; ?>/../">
        <?php if ($TPL_DATA['type'] === 'dir'): ?>
            ⮤ Up one level
        <?php else: ?>
            ⮤ View parent directory
        <?php endif; ?>
        </a></li>
    </ul>
    <?php endif; ?>
    

    <?php if (count($dirs) > 0): ?>
    <table aria-label="directory index">
        <thead>
            <td aria-label="type">&nbsp;</td>
            <td>Name</td>
            <td class="time">Last Modified</td>
    </thead>
    <tbody>
        <?php foreach($dirs as $dir): ?>
            <?php 
                // in /media directories, we hide markdown, html or text files
                // as they may be used to annotate media
                
                 if (strpos($dir['href'], '/media') === 0 && (
                        isset($dir['pathinfo']['extension']) &&
                        ($dir['pathinfo']['extension'] === 'txt' || 
                        $dir['pathinfo']['extension'] === 'html' || 
                        $dir['pathinfo']['extension'] ==='md'))) {
                     continue;
                 }
            ?>
            <tr data-href="<?php echo $dir['href']; ?>">
                <td class="type"><?php echo ($dir['type'] == 'file') ? get_emoji_for_file_type($dir['pathinfo']['extension']): '📁' ?></td>
                <td class="name"><a href="<?php echo $dir['href']; ?>"><?php echo $dir['name'] ?></a></td>
                <td class="time"><?php echo isset($dir['mtime']) ? human_time_ago($dir['mtime']) : ''; ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    </table>
    <?php elseif ($TPL_DATA['type'] === 'dir'): ?>
    <span class="blank-state empty-dir">This directory is currently empty.</span>
    <?php endif; ?>

    <?php if ($TPL_DATA['type'] !== 'dir'): ?>
        <?php
            $ext = $TPL_DATA['pathinfo']['extension'];
        ?>

        <?php if (in_array($ext, DOCUMENT_EXTENSIONS)): ?>
        <h4>Document Metadata</h4>
            <?php if (isset($TPL_DATA['summary'])): ?>
                <?php echo $TPL_DATA['summary']; ?>
            <?php else: ?>
                <span class="blank-state">No summary available.</span>
            <?php endif; ?>

        <ul class="metadata">
            <li>
                <?php
                    $ext = $TPL_DATA['pathinfo']['extension'];
                    $human_ext = 'Unknown';
                    switch ($ext) {
                        case 'txt':
                            $human_ext = 'Plain Text';
                            break;
                        case 'html':
                            $human_ext = 'HTML';
                            break;
                        case 'md':
                            $human_ext = 'Markdown';
                            break;
                    }
                ?>
                Type: <?php echo $human_ext; ?>
            </li>
            <li>
                Created: <?php echo human_time_ago($TPL_DATA['ctime']); ?>
            </li>
            <li>
                Last modified: <?php echo human_time_ago($TPL_DATA['mtime']); ?>
            </li>
            <li>
                Word count: <?php echo str_word_count(strip_tags($TPL_DATA['body'])); ?>
            </li>
            <?php if (isset($TPL_DATA['tags']) && is_array($TPL_DATA['tags'])): ?>
            <li>
                Tags: <?php echo implode(', ', $TPL_DATA['tags']); ?>
            </li>
            <?php endif; ?>
        </ul>
        <?php elseif (in_array($ext, IMAGE_EXTENSIONS)): ?>
        <h4>Image Metadata</h4>
        <ul class="metadata">
        <?php
            $displayed_exif = false;
            if (function_exists('read_exif_data')) {
                $exif = read_exif_data($TPL_DATA['path']);
                if (is_array($exif)) {
                    $displayed_exif = true;
                    foreach ($exif as $key => $value) {
                        echo '<li>'. htmlspecialchars($key) . ': '. htmlspecialchars($value) . '</li>';
                    }
                }
            }

            if ($displayed_exif === false) {
                $size = getimagesize($TPL_DATA['path']);
                $file_size = filesize($TPL_DATA['path']);
                if ($size !== false) {
                    echo '<li>Dimensions: '. $size[0]. ' x ' . $size[1] . '</li>';
                    echo '<li>Size: ' . humanize_filesize($file_size) .'</li>';
                }
            }
        ?>
        </ul>
        <?php elseif (in_array($ext, VIDEO_EXTENSIONS)): ?>
            <ul class="metadata">
            
            </ul>
        <?php elseif (in_array($ext, AUDIO_EXTENSIONS)): ?>
        <ul class="metadata">
        <h4>Audio Metadata</h4>
        <?php
                $audio = Audio::get($TPL_DATA['path']);

                // TODO pull relevant tags
                // https://github.com/kiwilan/php-audio
                $tags_to_display = [
                    ['getTitle', 'Title', 'title'],
                    ['getArtist', 'Artist', 'artist'],
                    ['getAlbum', 'Album', 'album'],
                    ['getEncoding', 'Encoding', 'encoding'],
                    ['getDuration', 'Duration', 'duration'],
                ];
                foreach ($tags_to_display as $tag_ntuple) {
                    $tag_method = $tag_ntuple[0];
                    $tag_label = $tag_ntuple[1];
                    $tag_key = $tag_ntuple[2];
                    $tag_value = $audio->$tag_method();
                    if (is_array($tag_value)) {
                        $tag_value = implode(', ', $tag_value);
                    }
                    if (trim($tag_value) !== '') {
                        if ($tag_key === 'duration') {
                            $tag_value = gmdate('H:i:s', $tag_value);
                        } else {
                            $tag_value = trim($tag_value);
                        }
                        echo '<li>' . $tag_label. ': '. $tag_value . '</li>';
                    }
                }
           ?>
        </ul>
        <?php endif; ?>

    </ul>
    <?php endif; ?>
</nav>
