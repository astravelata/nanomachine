<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link rel="stylesheet" type="text/css" href="/static/index.css">

    <?php if (SACRIFICE_SPEED_FOR_BEAUTY): ?>
    <link rel="stylesheet" type="text/css" href="/static/beauty.css">
    <link rel="stylesheet" type="text/css" href="/static/prism.css">
    <?php endif; ?>

    <?php /* various SEO meta tags */ ?>
    <?php if ($TPL_DATA['description']): ?>
    <meta name="description" content="<?php echo $TPL_DATA['description']; ?>">
    <meta property="og:description" content="<?php echo $TPL_DATA['description']; ?>">
    <?php endif; ?>
    <?php if ($TPL_DATA['author']): ?>
    <meta name="author" content="<?php echo $TPL_DATA['author']; ?>">
    <?php endif; ?>
    <?php if ($TPL_DATA['title'] !== ''): ?>
    <meta property="og:title" content=" <?php echo $TPL_DATA['title'] . "&mdash;" . $TPL_DATA['site_title']; ?>">   
    <?php else: ?>
    <meta property="og:title" content=" <?php echo $TPL_DATA['relpath'] . "&mdash;" . $TPL_DATA['site_title']; ?>">   
    <?php endif; ?>
    
    <?php if ($TPL_DATA['cover_photo']): ?>
    <meta property="og:image" content="<?php echo $TPL_DATA['cover_photo']; ?>">
    <?php endif; ?>

    <?php if ($TPL_DATA['keywords']): ?>
    <meta name="keywords" content="<?php echo $TPL_DATA['keywords']; ?>">
    <meta property="og:keywords" content="<?php echo $TPL_DATA['keywords']; ?>">
    <?php endif; ?>

    <meta name="robots" content="index, follow">    
    <meta property="og:url" content="<?php echo $TPL_DATA['url']; ?>">
    <link rel="canonical" href="<?php echo $TPL_DATA['url']; ?>">

    
    <title>
        <?php if ($TPL_DATA['title'] !== ''): ?>
        <?php echo $TPL_DATA['title']; ?>
        <?php else: ?>
        <?php echo $TPL_DATA['relpath'] ?>
        <?php endif; ?>
        &mdash; <?php echo $TPL_DATA['site_title']; ?>
    </title>
</head>
<body id="top" class="<?php echo (DEBUG ? "debug" : ""); ?>">
<div id="debug">
<?php if (php_sapi_name() === 'cli-server'): ?>
<pre><?php echo htmlspecialchars(print_r($TPL_DATA, true)); ?></pre>
<?php endif; ?>
</div>
<div id="upper-nav">
<div id="breadcrumb">
    <?php if ($TPL_DATA['site_title']): ?>
    <a href="/" class="home">~<?php echo str_replace(" ", "", $TPL_DATA['site_title']); ?></a>
    <?php else: ?>
    <a href="/" class="home">home</a>
    <?php endif; ?>
    <?php
        if ($TPL_DATA['relpath'] !== '/') {
            $path_array = explode("/", $TPL_DATA['relpath']);
            $dir_count = count($path_array) - 1;
            $kinda_long = $dir_count > 3;
            $additive_path = '';

            for ($cur = 1; $cur <= $dir_count; $cur++) {
                $additive_path .= '/' . $path_array[$cur];
                if ($kinda_long && $cur == 1) {
                    echo " / &hellip;";
                } elseif (!$kinda_long || ($dir_count - $cur) < 1) {
                    echo ' / <a href="' . $additive_path . '">' . $path_array[$cur] . '</a>';
                }
            }
        }
    ?>
</div>
<ul>
    <li><a class="evergreen" href="#main-nav">🠗&nbsp;Directory&nbsp;Index</a></li>
</ul>
</div>
