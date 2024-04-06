<?php include 'src/templates/partial/nav.php'; ?>
<footer>
    <div id="ps">
        <p id="copyright">
            Copyright &copy; 2024 <?php echo $TPL_DATA['author']; ?>. All rights reserved (where applicable.)
        </p>
        <p id="nerd-stats">
        <?php if ($TPL_DATA['caching'] === true): ?>
        last gen 
        <?php else: ?>
        gen 
        <?php endif; ?>
        
        <?php if ($TPL_DATA['render_time'] >= 16): ?>
        <span class="slow"><?php echo round($TPL_DATA['render_time'], 3); ?></span> ms;
        <?php else: ?>
        <span class="fast"><?php echo round($TPL_DATA['render_time'], 3); ?></span> ms;
        <?php endif; ?>
        <?php echo $TPL_DATA['vendor_string']; ?> <span class="blink">|</span>
        </p>
    </div>

    <div>
        <a class="evergreen" href="#top">🠕 Back to Top</a>
    </div>
</footer>
<?php if (SACRIFICE_SPEED_FOR_BEAUTY): ?>
<script type="text/javascript" src="/static/beauty.js"></script>
<script type="text/javascript" src="/static/prism.js"></script>
<?php endif; ?>
</body>
</html>