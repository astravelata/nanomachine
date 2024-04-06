<?php // echo the body if it needs it ?>
<?php if ($TPL_DATA['body']): ?>
<div id="content">
    <?php if ($TPL_DATA['cover_photo'] !== null): ?>
    <div id="cover-photo">
        <img src="<?php echo $TPL_DATA['cover_photo']; ?>" alt="<?php echo $TPL_DATA['cover_photo_alt']; ?>" />
    </div>
    <?php endif; ?>
    <?php echo $TPL_DATA['body']; ?>
</div>
<?php endif; ?>