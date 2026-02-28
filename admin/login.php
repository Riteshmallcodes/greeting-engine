<?php

require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/csrf.php';

if (!empty($_SESSION['admin_id'])) {
    app_redirect('/admin/dashboard.php');
}

$error = '';
$maxAttempts = 5;
$windowSeconds = 15 * 60;
$attemptData = isset($_SESSION['admin_login_attempts']) && is_array($_SESSION['admin_login_attempts'])
    ? $_SESSION['admin_login_attempts']
    : ['count' => 0, 'first_attempt_at' => 0];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $now = time();
    $count = isset($attemptData['count']) ? (int)$attemptData['count'] : 0;
    $firstAttemptAt = isset($attemptData['first_attempt_at']) ? (int)$attemptData['first_attempt_at'] : 0;

    if ($firstAttemptAt > 0 && ($now - $firstAttemptAt) > $windowSeconds) {
        $count = 0;
        $firstAttemptAt = 0;
    }

    if ($count >= $maxAttempts) {
        $wait = max(1, $windowSeconds - ($now - $firstAttemptAt));
        $error = 'Too many attempts. Please retry in ' . ceil($wait / 60) . ' minute(s).';
    }

    $token = filter_input(INPUT_POST, 'csrf_token', FILTER_UNSAFE_RAW);
    $usernameInput = filter_input(INPUT_POST, 'username', FILTER_UNSAFE_RAW);
    $passwordInput = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);

    $username = trim((string)$usernameInput);
    $password = trim((string)$passwordInput);

    if ($error !== '') {
        // rate-limited
    } elseif (!csrf_validate($token)) {
        $error = 'Invalid request token.';
    } elseif ($username === '' || $password === '') {
        $error = 'Username and password are required.';
    } else {
        try {
            $stmt = $pdo->prepare('SELECT id, username, password FROM admin_users WHERE username = :username LIMIT 1');
            $stmt->execute([':username' => $username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                session_regenerate_id(true);
                $_SESSION['admin_id'] = (int)$admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_last_active'] = time();
                unset($_SESSION['admin_login_attempts']);
                app_redirect('/admin/dashboard.php');
            }

            $error = 'Invalid login credentials.';
        } catch (PDOException $e) {
            error_log('Admin login failed: ' . $e->getMessage());
            $error = 'Login unavailable right now.';
        }
    }

    if ($error !== '') {
        if ($firstAttemptAt === 0 || ($now - $firstAttemptAt) > $windowSeconds) {
            $firstAttemptAt = $now;
            $count = 1;
        } else {
            $count += 1;
        }
        $_SESSION['admin_login_attempts'] = [
            'count' => $count,
            'first_attempt_at' => $firstAttemptAt,
        ];
    }
}

$pageTitle = 'Admin Login | Wish Card';
$metaDescription = 'Secure admin login for Wish Card website.';
$bodyClass = 'admin-page';
require __DIR__ . '/../includes/header.php';
?>
<section class="admin-auth reveal">
    <form class="glass-card admin-form" method="post">
        <h1>Admin Login</h1>
        <?php if ($error !== ''): ?>
            <div class="form-errors"><p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p></div>
        <?php endif; ?>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

        <label for="username">Username</label>
        <input type="text" id="username" name="username" required maxlength="100">

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <button type="submit" class="btn-primary">Login</button>
    </form>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>

