<?php

require __DIR__ . '/includes/db.php';

$wishId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$wishId) {
    http_response_code(400);
    exit('Invalid wish ID.');
}

try {
    $stmt = $pdo->prepare('SELECT id, festival_name, user_name, custom_message, image_path, created_at FROM wishes WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $wishId]);
    $wish = $stmt->fetch();
} catch (PDOException $e) {
    error_log('Wish fetch failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Unable to fetch wish right now.');
}

if (!$wish) {
    http_response_code(404);
    exit('Wish not found.');
}

$currentUrl = app_url('/wish.php?id=' . (int)$wish['id']);

$imagePath = isset($wish['image_path']) ? (string)$wish['image_path'] : '';
$imageDiskPath = __DIR__ . $imagePath;
$hasImage = $imagePath !== '' && preg_match('#^/uploads/wishes/[a-zA-Z0-9_\-\.]+$#', $imagePath) && is_file($imageDiskPath);
$imagePublicUrl = $hasImage ? app_url($imagePath) : '';
$festivalSlug = strtolower(str_replace(' ', '-', (string)$wish['festival_name']));
$posterClass = 'poster-' . $festivalSlug . '-' . (((int)$wish['id'] % 4) + 1);
$festivalThemeClass = 'festival-theme-' . $festivalSlug;
$downloadName = preg_replace('/[^a-z0-9\-]+/i', '-', strtolower((string)$wish['festival_name'] . '-' . (string)$wish['user_name']));
$downloadName = trim((string)$downloadName, '-');
if ($downloadName === '') {
    $downloadName = 'wish-card';
}
$downloadName .= '-' . (int)$wish['id'] . '.png';

$pageTitle = $wish['user_name'] . ' sent a ' . $wish['festival_name'] . ' wish | Wish Card';
$metaDescription = 'Open this festive greeting created on Wish Card and share the joy.';
$ogTitle = $wish['user_name'] . ' sent you a ' . $wish['festival_name'] . ' wish!';
$ogDescription = $wish['custom_message'] ? $wish['custom_message'] : 'Click to view your special wish card.';
$ogUrl = $currentUrl;
$ogType = 'article';
$bodyClass = 'wish-page';
$loadConfetti = true;
$extraScripts = ['https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js'];
require __DIR__ . '/includes/header.php';

$shareText = $wish['user_name'] . ' has sent you a special ' . $wish['festival_name'] . ' wish. Click here: ' . $currentUrl;
$encodedShareText = rawurlencode($shareText);
$encodedUrl = rawurlencode($currentUrl);
?>
<section class="wish-display reveal">
    <article id="wishCard" class="wish-card glass-card wish-poster canva-poster <?= htmlspecialchars($posterClass, ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($festivalThemeClass, ENT_QUOTES, 'UTF-8') ?>">
        <div class="poster-overlay"></div>
        <div class="poster-content">
            <div class="poster-topline">Wish Card Exclusive</div>
            <?php if ($hasImage): ?>
                <div class="poster-avatar-wrap">
                    <img class="poster-avatar" src="<?= htmlspecialchars($imagePublicUrl, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($wish['user_name'], ENT_QUOTES, 'UTF-8') ?>">
                </div>
            <?php endif; ?>
            <h1 class="poster-title">Happy <?= htmlspecialchars($wish['festival_name'], ENT_QUOTES, 'UTF-8') ?></h1>
            <h2 class="poster-name"><?= htmlspecialchars($wish['user_name'], ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="poster-message"><?= nl2br(htmlspecialchars($wish['custom_message'] ? $wish['custom_message'] : 'Wishing you joy, peace, and prosperity!', ENT_QUOTES, 'UTF-8')) ?></p>
            <small class="poster-meta">Wish ID #<?= (int)$wish['id'] ?> | <?= htmlspecialchars((string)$wish['created_at'], ENT_QUOTES, 'UTF-8') ?></small>
        </div>
    </article>

    <div class="share-actions reveal">
        <a class="btn-primary" target="_blank" rel="noopener" href="https://wa.me/?text=<?= $encodedShareText ?>">Share on WhatsApp</a>
        <a class="btn-secondary" target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u=<?= $encodedUrl ?>">Share on Facebook</a>
        <button id="copyLinkBtn" class="btn-secondary" type="button" data-url="<?= htmlspecialchars($currentUrl, ENT_QUOTES, 'UTF-8') ?>">Copy Link</button>
        <button id="downloadCardBtn" class="btn-secondary" type="button" data-download-name="<?= htmlspecialchars($downloadName, ENT_QUOTES, 'UTF-8') ?>">Download Poster</button>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
