<?php
    use Astravelata\Nanomachine\Formatters\MarkdownFormatter;
    use Astravelata\Nanomachine\Utility;
?>

<div id="content">
<?php 
if ($TPL_DATA['is_media']) {
    switch ($TPL_DATA['media_type']) {
        case 'video':
            include 'src/templates/partial/media_video.php';
            break;

        case 'image':
            include 'src/templates/partial/media_image.php';
            break;

        case 'audio':
            include 'src/templates/partial/media_audio.php';
            break;
            
        default:
            error_log("Unknown media type: {$TPL_DATA['media_type']}");
            break;
    }
}

// do we have a file alongside this that marks it up?
$path_with_extension_removed = pathinfo($TPL_DATA['path'], PATHINFO_DIRNAME) . '/' . pathinfo($TPL_DATA['relpath'], PATHINFO_FILENAME);
foreach (DOCUMENT_EXTENSIONS as $ext) {
    $maybe_annotation_file = $path_with_extension_removed . '.' . $ext;
    if (file_exists($maybe_annotation_file)) {
        // TODO refactor with file switcher in index.php
        switch ($ext) {
            case 'md':
                $markdown_content = MarkdownFormatter::parse_markdown($maybe_annotation_file);
                $TPL_DATA['body'] = $markdown_content['content'];
                Utility::templatize_front_matter($markdown_content['front_matter'], $TPL_DATA);
                break;
    
            default:
                $TPL_DATA['body'] = file_get_contents($maybe_annotation_file);
                break;
        }
    }
}
?>

<?php if ($TPL_DATA['body'] != ''): ?>
<div id="content-annotation">
<?php echo $TPL_DATA['body']; ?>
</div>
<?php endif; ?>

</div>
