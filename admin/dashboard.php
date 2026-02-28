<?php

require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/csrf.php';

if (empty($_SESSION['admin_id'])) {
    app_redirect('/admin/login.php');
}

$sessionTimeout = 30 * 60;
$lastActive = isset($_SESSION['admin_last_active']) ? (int)$_SESSION['admin_last_active'] : 0;
if ($lastActive > 0 && (time() - $lastActive) > $sessionTimeout) {
    $_SESSION = [];
    session_destroy();
    app_redirect('/admin/login.php');
}
$_SESSION['admin_last_active'] = time();

$deleteError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = filter_input(INPUT_POST, 'csrf_token', FILTER_UNSAFE_RAW);
    $deleteId = filter_input(INPUT_POST, 'delete_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    if (!csrf_validate($token)) {
        $deleteError = 'Invalid CSRF token.';
    } elseif ($deleteId) {
        try {
            try {
                $imgStmt = $pdo->prepare('SELECT image_path, ai_image_path FROM wishes WHERE id = :id LIMIT 1');
                $imgStmt->execute([':id' => $deleteId]);
                $wishRow = $imgStmt->fetch();
            } catch (PDOException $e) {
                if (stripos($e->getMessage(), 'ai_image_path') !== false) {
                    $imgStmt = $pdo->prepare('SELECT image_path FROM wishes WHERE id = :id LIMIT 1');
                    $imgStmt->execute([':id' => $deleteId]);
                    $wishRow = $imgStmt->fetch();
                    if ($wishRow) {
                        $wishRow['ai_image_path'] = null;
                    }
                } else {
                    throw $e;
                }
            }

            $stmt = $pdo->prepare('DELETE FROM wishes WHERE id = :id');
            $stmt->execute([':id' => $deleteId]);

            if ($wishRow && !empty($wishRow['image_path']) && preg_match('#^/uploads/wishes/[a-zA-Z0-9_\\-\\.]+$#', (string)$wishRow['image_path'])) {
                $filePath = dirname(__DIR__) . (string)$wishRow['image_path'];
                if (is_file($filePath)) {
                    @unlink($filePath);
                }
            }
            if ($wishRow && !empty($wishRow['ai_image_path']) && preg_match('#^/uploads/generated/[a-zA-Z0-9_\\-\\.]+$#', (string)$wishRow['ai_image_path'])) {
                $aiFilePath = dirname(__DIR__) . (string)$wishRow['ai_image_path'];
                if (is_file($aiFilePath)) {
                    @unlink($aiFilePath);
                }
            }

            app_redirect('/admin/dashboard.php');
        } catch (PDOException $e) {
            error_log('Wish delete failed: ' . $e->getMessage());
            $deleteError = 'Failed to delete wish.';
        }
    }
}

$query = trim((string)(filter_input(INPUT_GET, 'q', FILTER_UNSAFE_RAW) ?? ''));
if (function_exists('mb_substr')) {
    $query = mb_substr($query, 0, 80);
} else {
    $query = substr($query, 0, 80);
}
$query = preg_replace('/[^\pL\pN\-\s]/u', '', $query);
$query = trim((string)$query);

$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$currentPage = $page ?: 1;
$perPage = 25;
$offset = ($currentPage - 1) * $perPage;

$totalWishes = (int)$pdo->query('SELECT COUNT(*) FROM wishes')->fetchColumn();
$todayWishes = (int)$pdo->query('SELECT COUNT(*) FROM wishes WHERE DATE(created_at) = CURDATE()')->fetchColumn();
$festivalCountsStmt = $pdo->query('SELECT festival_name, COUNT(*) AS total FROM wishes GROUP BY festival_name ORDER BY total DESC');
$festivalCounts = $festivalCountsStmt->fetchAll();

try {
    $hasAiImageColumn = true;
    if ($query !== '') {
        $baseCountSql = 'SELECT COUNT(*) FROM wishes WHERE user_name LIKE :q OR festival_name LIKE :q OR custom_message LIKE :q';
        $countStmt = $pdo->prepare($baseCountSql);
        $countStmt->execute([':q' => '%' . $query . '%']);
        $filteredTotal = (int)$countStmt->fetchColumn();

        $sql = 'SELECT id, festival_name, user_name, custom_message, image_path, ai_image_path, created_at
                FROM wishes
                WHERE user_name LIKE :q OR festival_name LIKE :q OR custom_message LIKE :q
                ORDER BY id DESC
                LIMIT :limit OFFSET :offset';
        $allWishesStmt = $pdo->prepare($sql);
        $allWishesStmt->bindValue(':q', '%' . $query . '%', PDO::PARAM_STR);
        $allWishesStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $allWishesStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $allWishesStmt->execute();
    } else {
        $filteredTotal = $totalWishes;
        $sql = 'SELECT id, festival_name, user_name, custom_message, image_path, ai_image_path, created_at
                FROM wishes
                ORDER BY id DESC
                LIMIT :limit OFFSET :offset';
        $allWishesStmt = $pdo->prepare($sql);
        $allWishesStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $allWishesStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $allWishesStmt->execute();
    }

    $allWishes = $allWishesStmt->fetchAll();
} catch (PDOException $e) {
    if (stripos($e->getMessage(), 'ai_image_path') !== false) {
        $hasAiImageColumn = false;
        if ($query !== '') {
            $baseCountSql = 'SELECT COUNT(*) FROM wishes WHERE user_name LIKE :q OR festival_name LIKE :q OR custom_message LIKE :q';
            $countStmt = $pdo->prepare($baseCountSql);
            $countStmt->execute([':q' => '%' . $query . '%']);
            $filteredTotal = (int)$countStmt->fetchColumn();

            $fallbackSql = 'SELECT id, festival_name, user_name, custom_message, image_path, created_at
                            FROM wishes
                            WHERE user_name LIKE :q OR festival_name LIKE :q OR custom_message LIKE :q
                            ORDER BY id DESC
                            LIMIT :limit OFFSET :offset';
            $allWishesStmt = $pdo->prepare($fallbackSql);
            $allWishesStmt->bindValue(':q', '%' . $query . '%', PDO::PARAM_STR);
            $allWishesStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $allWishesStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $allWishesStmt->execute();
        } else {
            $filteredTotal = $totalWishes;
            $fallbackSql = 'SELECT id, festival_name, user_name, custom_message, image_path, created_at
                            FROM wishes
                            ORDER BY id DESC
                            LIMIT :limit OFFSET :offset';
            $allWishesStmt = $pdo->prepare($fallbackSql);
            $allWishesStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $allWishesStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $allWishesStmt->execute();
        }

        $allWishes = $allWishesStmt->fetchAll();
        foreach ($allWishes as $idx => $row) {
            $allWishes[$idx]['ai_image_path'] = null;
        }
    } else {
        throw $e;
    }
}

$totalPages = max(1, (int)ceil(($filteredTotal ?? 0) / $perPage));
if ($currentPage > $totalPages) {
    $target = '/admin/dashboard.php?page=' . $totalPages;
    if ($query !== '') {
        $target .= '&q=' . rawurlencode($query);
    }
    app_redirect($target);
}
$startRecord = $filteredTotal > 0 ? (($currentPage - 1) * $perPage) + 1 : 0;
$endRecord = min($filteredTotal, $currentPage * $perPage);

$pageTitle = 'Admin Dashboard | Wish Card';
$metaDescription = 'Manage festival wishes and track analytics from admin dashboard.';
$bodyClass = 'admin-page';
require __DIR__ . '/../includes/header.php';
?>
<section class="dashboard reveal">
    <div class="dash-head">
        <h1>Dashboard</h1>
        <a href="<?= htmlspecialchars(app_url('/admin/logout.php'), ENT_QUOTES, 'UTF-8') ?>" class="btn-secondary">Logout</a>
    </div>

    <?php if ($deleteError !== ''): ?>
        <div class="form-errors"><p><?= htmlspecialchars($deleteError, ENT_QUOTES, 'UTF-8') ?></p></div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="glass-card stat-card">
            <h3>Total Wishes</h3>
            <p><?= $totalWishes ?></p>
        </div>
        <div class="glass-card stat-card">
            <h3>Today's Wishes</h3>
            <p><?= $todayWishes ?></p>
        </div>
        <div class="glass-card stat-card">
            <h3>Festival Wise Count</h3>
            <?php if ($festivalCounts): ?>
                <?php foreach ($festivalCounts as $row): ?>
                    <small><?= htmlspecialchars($row['festival_name'], ENT_QUOTES, 'UTF-8') ?>: <?= (int)$row['total'] ?></small><br>
                <?php endforeach; ?>
            <?php else: ?>
                <small>No wishes yet.</small>
            <?php endif; ?>
        </div>
    </div>

    <div class="dash-tools glass-card">
        <form method="get" class="dash-search">
            <label for="q">Search Wishes</label>
            <input id="q" type="text" name="q" maxlength="80" value="<?= htmlspecialchars($query, ENT_QUOTES, 'UTF-8') ?>" placeholder="User, festival, message">
            <button type="submit" class="btn-secondary">Search</button>
            <?php if ($query !== ''): ?>
                <a href="<?= htmlspecialchars(app_url('/admin/dashboard.php'), ENT_QUOTES, 'UTF-8') ?>" class="btn-secondary">Clear</a>
            <?php endif; ?>
        </form>
        <p class="dash-meta">
            Showing <?= (int)$startRecord ?>-<?= (int)$endRecord ?> of <?= (int)$filteredTotal ?> result(s)
        </p>
    </div>

    <div class="table-wrap glass-card">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Festival</th>
                    <th>User</th>
                    <th>Photo</th>
                    <th>AI Poster</th>
                    <th>Message</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$allWishes): ?>
                    <tr><td colspan="8">No data found.</td></tr>
                <?php else: ?>
                    <?php foreach ($allWishes as $row): ?>
                        <tr>
                            <td><?= (int)$row['id'] ?></td>
                            <td><?= htmlspecialchars($row['festival_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($row['user_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <?php if (!empty($row['image_path'])): ?>
                                    <a href="<?= htmlspecialchars(app_url((string)$row['image_path']), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">View</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($row['ai_image_path'])): ?>
                                    <a href="<?= htmlspecialchars(app_url((string)$row['ai_image_path']), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">View</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <?php
                            $messagePreview = (string)($row['custom_message'] ?? '');
                            if (function_exists('mb_strimwidth')) {
                                $messagePreview = mb_strimwidth($messagePreview, 0, 80, '...');
                            } else {
                                $messagePreview = strlen($messagePreview) > 80 ? substr($messagePreview, 0, 77) . '...' : $messagePreview;
                            }
                            ?>
                            <td><?= htmlspecialchars($messagePreview, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)$row['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <form method="post" onsubmit="return confirm('Delete this wish?');">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="delete_id" value="<?= (int)$row['id'] ?>">
                                    <button type="submit" class="btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="pagination-wrap">
            <?php if ($currentPage > 1): ?>
                <a class="btn-secondary" href="<?= htmlspecialchars(app_url('/admin/dashboard.php?page=' . ($currentPage - 1) . ($query !== '' ? '&q=' . rawurlencode($query) : '')), ENT_QUOTES, 'UTF-8') ?>">Previous</a>
            <?php endif; ?>
            <span class="pagination-meta">Page <?= (int)$currentPage ?> / <?= (int)$totalPages ?></span>
            <?php if ($currentPage < $totalPages): ?>
                <a class="btn-secondary" href="<?= htmlspecialchars(app_url('/admin/dashboard.php?page=' . ($currentPage + 1) . ($query !== '' ? '&q=' . rawurlencode($query) : '')), ENT_QUOTES, 'UTF-8') ?>">Next</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>

