<?php

require __DIR__ . '/includes/db.php';

header('Content-Type: application/xml; charset=UTF-8');

$base = app_base_url();
$today = date('Y-m-d');
$festivals = ['diwali', 'holi', 'eid', 'new-year', 'raksha-bandhan'];
$urls = [
    ['loc' => $base . '/index.php', 'changefreq' => 'daily', 'priority' => '1.0', 'lastmod' => $today],
];

foreach ($festivals as $slug) {
    $urls[] = [
        'loc' => $base . '/festival.php?name=' . rawurlencode($slug),
        'changefreq' => 'weekly',
        'priority' => '0.8',
        'lastmod' => $today,
    ];
}

try {
    $latestStmt = $pdo->query('SELECT id, DATE(created_at) AS lastmod FROM wishes ORDER BY id DESC LIMIT 100');
    $latestRows = $latestStmt->fetchAll();
    foreach ($latestRows as $row) {
        $urls[] = [
            'loc' => $base . '/wish.php?id=' . (int)$row['id'],
            'changefreq' => 'weekly',
            'priority' => '0.6',
            'lastmod' => !empty($row['lastmod']) ? (string)$row['lastmod'] : $today,
        ];
    }
} catch (PDOException $e) {
    error_log('Sitemap wish fetch failed: ' . $e->getMessage());
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $item): ?>
    <url>
        <loc><?= htmlspecialchars($item['loc'], ENT_QUOTES, 'UTF-8') ?></loc>
        <lastmod><?= htmlspecialchars($item['lastmod'], ENT_QUOTES, 'UTF-8') ?></lastmod>
        <changefreq><?= htmlspecialchars($item['changefreq'], ENT_QUOTES, 'UTF-8') ?></changefreq>
        <priority><?= htmlspecialchars($item['priority'], ENT_QUOTES, 'UTF-8') ?></priority>
    </url>
<?php endforeach; ?>
</urlset>
