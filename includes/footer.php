</main>
<button id="backToTop" class="back-to-top" aria-label="Back to top">&uarr;</button>
<footer class="site-footer">
    <p>&copy; <?= date('Y') ?> Wish Card. Share joy with every festival.</p>
</footer>
<?php if (app_bool_env('ADS_ENABLED', true)): ?>
<div class="sticky-ad">
    <script>
      atOptions = {
        'key' : 'c2d059dfd2490366ddcf49bd8b9132ab',
        'format' : 'iframe',
        'height' : 50,
        'width' : 320,
        'params' : {}
      };
    </script>
    <script src="https://www.highperformanceformat.com/c2d059dfd2490366ddcf49bd8b9132ab/invoke.js" defer></script>
</div>
<?php endif; ?>
<script src="<?= htmlspecialchars(app_url('/assets/js/script.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
<?php if (!empty($loadConfetti)): ?>
<script src="<?= htmlspecialchars(app_url('/assets/js/confetti.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
<?php endif; ?>
<?php if (!empty($extraScripts) && is_array($extraScripts)): ?>
    <?php foreach ($extraScripts as $scriptSrc): ?>
        <script src="<?= htmlspecialchars($scriptSrc, ENT_QUOTES, 'UTF-8') ?>" defer></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>
