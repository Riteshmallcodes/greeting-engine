<?php

require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Wish Card | Create Festival Wishes';
$metaDescription = 'Create personalized festival wish cards for Diwali, Holi, Eid, New Year and Raksha Bandhan.';
$bodyClass = 'home-page';
require __DIR__ . '/includes/header.php';

$festivals = [
    'diwali' => 'Diwali',
    'holi' => 'Holi',
    'eid' => 'Eid',
    'new-year' => 'New Year',
    'raksha-bandhan' => 'Raksha Bandhan',
];
?>
<section class="hero reveal">
    <div class="hero-content">
        <h1>Create Viral Festival Wishes in Seconds</h1>
        <p>Design beautiful greeting cards and share instantly with friends and family.</p>
        <a class="btn-primary" href="#festival-grid">Start Creating</a>
    </div>
</section>

<?php if (app_bool_env('ADS_ENABLED', true)): ?>
<div class="ads-top ad-box reveal">
    <h3>Sponsored</h3>
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

<section id="festival-grid" class="festival-grid">
    <?php foreach ($festivals as $slug => $label): ?>
        <article class="glass-card reveal">
            <h2><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></h2>
            <p>Generate a premium <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?> greeting card.</p>
            <a href="<?= htmlspecialchars(app_url('/festival.php?name=' . urlencode($slug)), ENT_QUOTES, 'UTF-8') ?>" class="btn-secondary">Create Wish</a>
        </article>
    <?php endforeach; ?>
</section>

<?php if (app_bool_env('ADS_ENABLED', true)): ?>
<section class="ad-mid ad-box reveal">
    <h3>Advertisement</h3>
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
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>

