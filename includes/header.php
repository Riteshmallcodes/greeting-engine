<?php

require_once __DIR__ . '/bootstrap.php';
app_send_security_headers();

$siteName = 'Wish Card';
$pageTitle = $pageTitle ?? 'Wish Card | Festival Wishes';
$metaDescription = $metaDescription ?? 'Create and share beautiful festival greeting cards instantly with Wish Card.';
$ogTitle = $ogTitle ?? $pageTitle;
$ogDescription = $ogDescription ?? $metaDescription;
$ogUrl = $ogUrl ?? app_current_url();
$ogType = $ogType ?? 'website';
$bodyClass = $bodyClass ?? '';
$isAdminPage = strpos($bodyClass, 'admin-page') !== false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:title" content="<?= htmlspecialchars($ogTitle, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($ogDescription, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:url" content="<?= htmlspecialchars($ogUrl, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:type" content="<?= htmlspecialchars($ogType, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="canonical" href="<?= htmlspecialchars(app_current_url(), ENT_QUOTES, 'UTF-8') ?>">
    <?php if ($isAdminPage): ?>
        <meta name="robots" content="noindex,nofollow">
    <?php endif; ?>
    <meta name="theme-color" content="#0f172a">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(app_url('/assets/css/style.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="<?= htmlspecialchars($bodyClass, ENT_QUOTES, 'UTF-8') ?>">
<div id="loader" class="loader-wrap">
    <div class="spinner"></div>
</div>
<header class="site-header">
    <a href="<?= htmlspecialchars(app_url('/index.php'), ENT_QUOTES, 'UTF-8') ?>" class="brand">Wish Card</a>
    <nav>
        <a href="<?= htmlspecialchars(app_url('/index.php'), ENT_QUOTES, 'UTF-8') ?>">Home</a>
        <a href="<?= htmlspecialchars(app_url('/admin/login.php'), ENT_QUOTES, 'UTF-8') ?>">Admin</a>
    </nav>
</header>
<main>

