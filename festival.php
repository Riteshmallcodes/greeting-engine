<?php

require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/csrf.php';

$festivalMap = [
    'diwali' => 'Diwali',
    'holi' => 'Holi',
    'eid' => 'Eid',
    'new-year' => 'New Year',
    'raksha-bandhan' => 'Raksha Bandhan',
];

$festivalSlugRaw = filter_input(INPUT_GET, 'name', FILTER_UNSAFE_RAW);
$festivalSlug = strtolower(trim((string)$festivalSlugRaw));
$festivalSlug = preg_replace('/[^a-z\-]/', '', $festivalSlug);
if (!$festivalSlug || !array_key_exists($festivalSlug, $festivalMap)) {
    http_response_code(404);
    exit('Festival not found.');
}

$festivalName = $festivalMap[$festivalSlug];
$errors = [];
$userName = '';
$customMessage = '';

function rate_limit_submission(int $windowSeconds = 5): bool
{
    $now = time();
    $lastSubmitAt = isset($_SESSION['wish_last_submit_at']) ? (int)$_SESSION['wish_last_submit_at'] : 0;
    if ($lastSubmitAt > 0 && ($now - $lastSubmitAt) < $windowSeconds) {
        return false;
    }
    $_SESSION['wish_last_submit_at'] = $now;
    return true;
}

function sanitize_user_name(string $name): string
{
    $clean = trim($name);
    $clean = preg_replace('/\s+/', ' ', $clean);
    $clean = preg_replace('/[^a-zA-Z0-9\.\'\-\s]/', '', (string)$clean);
    return trim((string)$clean);
}

function pick_auto_wish($festivalName, $userName)
{
    $wishes = [
        'Diwali' => [
            'May your home shine with diyas, happiness, and endless blessings.',
            'Wishing you joy, prosperity, and a year full of bright new beginnings.',
            'May every diya light your path with peace, love, and success.',
            'Celebrate this Diwali with smiles, sparkles, and sweet memories.',
            'Let this festival of lights fill your life with positivity and grace.',
        ],
        'Holi' => [
            'May your life be painted with joy, laughter, and vibrant colors.',
            'Wishing you a Holi full of happiness, friendship, and sweet moments.',
            'Let every color of Holi bring success, love, and good health.',
            'Celebrate Holi with heartwarming smiles and colorful memories.',
            'May this Holi splash your life with positivity and celebration.',
        ],
        'Eid' => [
            'May this Eid bring peace, barakah, and endless joy to your family.',
            'Wishing you and your loved ones a blessed and beautiful Eid.',
            'May your prayers be answered and your heart be filled with peace.',
            'Celebrate Eid with gratitude, kindness, and warm togetherness.',
            'May Allah bless you with happiness, health, and prosperity this Eid.',
        ],
        'New Year' => [
            'May this new year open doors to success, happiness, and growth.',
            'Wishing you 12 months of joy, health, and unforgettable moments.',
            'May the new year bring fresh hopes and beautiful beginnings.',
            'Cheers to new goals, big dreams, and a brighter tomorrow.',
            'Step into the new year with confidence, gratitude, and joy.',
        ],
        'Raksha Bandhan' => [
            'May this Raksha Bandhan strengthen the bond of love and trust forever.',
            'Wishing you a joyful Rakhi filled with smiles and sweet memories.',
            'May this special bond always protect and inspire both of you.',
            'Celebrate the beautiful sibling bond with love, laughter, and blessings.',
            'Happy Raksha Bandhan to a bond that grows stronger with time.',
        ],
    ];

    $pool = isset($wishes[$festivalName]) ? $wishes[$festivalName] : ['Wishing you happiness and prosperity.'];
    try {
        $index = random_int(0, count($pool) - 1);
    } catch (Exception $e) {
        $index = 0;
    }

    return 'Dear ' . $userName . ', ' . $pool[$index];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = filter_input(INPUT_POST, 'csrf_token', FILTER_UNSAFE_RAW);
    $postedFestivalRaw = filter_input(INPUT_POST, 'festival_name', FILTER_UNSAFE_RAW);
    $userNameRaw = filter_input(INPUT_POST, 'user_name', FILTER_UNSAFE_RAW);

    if (!csrf_validate($token)) {
        $errors[] = 'Invalid request token. Please refresh and try again.';
    }

    $postedFestival = strtolower(trim((string)$postedFestivalRaw));
    $postedFestival = preg_replace('/[^a-z\-]/', '', $postedFestival);

    if ($postedFestival === '' || !array_key_exists($postedFestival, $festivalMap)) {
        $errors[] = 'Invalid festival selected.';
    }

    $userName = sanitize_user_name((string)$userNameRaw);
    $customMessage = '';

    if (!rate_limit_submission(4)) {
        $errors[] = 'Please wait a few seconds before submitting again.';
    }

    if ($userName === '') {
        $errors[] = 'User name is required.';
    }

    $userNameLen = function_exists('mb_strlen') ? mb_strlen($userName) : strlen($userName);
    if ($userNameLen > 150) {
        $errors[] = 'User name is too long.';
    }

    $imagePath = null;
    $uploaded = isset($_FILES['user_image']) ? $_FILES['user_image'] : null;

    if (!$uploaded || !isset($uploaded['error']) || (int)$uploaded['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Please upload your image to generate a poster.';
    } elseif ((int)$uploaded['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Image upload failed. Please try another image.';
    } else {
        $tmpName = isset($uploaded['tmp_name']) ? (string)$uploaded['tmp_name'] : '';
        $fileSize = isset($uploaded['size']) ? (int)$uploaded['size'] : 0;

        if (!is_uploaded_file($tmpName)) {
            $errors[] = 'Invalid uploaded file.';
        }

        if ($fileSize <= 0 || $fileSize > 2 * 1024 * 1024) {
            $errors[] = 'Image size must be less than 2MB.';
        }

        $imageInfo = @getimagesize($tmpName);
        $mime = $imageInfo && isset($imageInfo['mime']) ? (string)$imageInfo['mime'] : '';
        $imgWidth = $imageInfo && isset($imageInfo[0]) ? (int)$imageInfo[0] : 0;
        $imgHeight = $imageInfo && isset($imageInfo[1]) ? (int)$imageInfo[1] : 0;
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        if (!$mime || !array_key_exists($mime, $allowed)) {
            $errors[] = 'Only JPG, PNG, and WEBP images are allowed.';
        }

        if ($imgWidth < 60 || $imgHeight < 60 || $imgWidth > 6000 || $imgHeight > 6000) {
            $errors[] = 'Image dimensions are not supported.';
        }

        if (empty($errors)) {
            try {
                $rand = bin2hex(random_bytes(8));
            } catch (Exception $e) {
                $rand = uniqid('wish_', true);
            }

            $extension = $allowed[$mime];
            $fileName = 'wish_' . time() . '_' . $rand . '.' . $extension;
            $uploadDir = __DIR__ . '/uploads/wishes';
            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
                $errors[] = 'Upload directory is not writable.';
            } else {
                $destination = $uploadDir . '/' . $fileName;
                if (!move_uploaded_file($tmpName, $destination)) {
                    $errors[] = 'Failed to save uploaded image.';
                } else {
                    $imagePath = '/uploads/wishes/' . $fileName;
                }
            }
        }
    }

    if (empty($errors)) {
        $customMessage = pick_auto_wish($festivalMap[$postedFestival], $userName);
        try {
            $stmt = $pdo->prepare('INSERT INTO wishes (festival_name, user_name, custom_message, image_path) VALUES (:festival_name, :user_name, :custom_message, :image_path)');
            $stmt->execute([
                ':festival_name' => $festivalMap[$postedFestival],
                ':user_name' => $userName,
                ':custom_message' => $customMessage,
                ':image_path' => $imagePath,
            ]);

            $wishId = (int)$pdo->lastInsertId();
            app_redirect('/wish.php?id=' . $wishId);
            exit;
        } catch (PDOException $e) {
            if (!empty($imagePath)) {
                $savedPath = __DIR__ . $imagePath;
                if (is_file($savedPath)) {
                    @unlink($savedPath);
                }
            }
            error_log('Wish insert failed: ' . $e->getMessage());
            $errors[] = 'Could not create wish right now. Please try again.';
        }
    }
}

$pageTitle = "Create {$festivalName} Wish | Wish Card";
$metaDescription = "Create and send a personalized {$festivalName} wish poster in seconds.";
$bodyClass = 'festival-page theme-' . $festivalSlug;
require __DIR__ . '/includes/header.php';
?>
<section class="festival-wrap reveal" data-festival="<?= htmlspecialchars($festivalSlug, ENT_QUOTES, 'UTF-8') ?>">
    <div class="festival-head">
        <h1><?= htmlspecialchars($festivalName, ENT_QUOTES, 'UTF-8') ?> Wishes</h1>
        <p>Upload your photo and generate a unique festival poster instantly.</p>
    </div>

    <?php if ($festivalSlug === 'diwali'): ?>
        <div class="diya-glow" aria-hidden="true"></div>
    <?php elseif ($festivalSlug === 'holi'): ?>
        <div class="holi-splash" aria-hidden="true"></div>
    <?php elseif ($festivalSlug === 'new-year'): ?>
        <canvas id="fireworksCanvas" aria-hidden="true"></canvas>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="form-errors">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form id="posterWishForm" method="post" enctype="multipart/form-data" class="wish-form glass-card reveal">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="festival_name" value="<?= htmlspecialchars($festivalSlug, ENT_QUOTES, 'UTF-8') ?>">

        <label for="user_name">User Name <span>*</span></label>
        <input type="text" id="user_name" name="user_name" maxlength="150" required value="<?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?>">

        <label for="custom_message">Auto Wish Message</label>
        <textarea id="custom_message" maxlength="1000" rows="4" placeholder="Unique festive message will be generated automatically." disabled>Unique festive wish will be auto-generated for each poster.</textarea>

        <label for="user_image">Upload Your Photo <span>*</span></label>
        <input type="file" id="user_image" name="user_image" accept="image/jpeg,image/png,image/webp" required>
        <small class="input-hint">Allowed: JPG, PNG, WEBP | Max size: 2MB</small>
        <div id="uploadPreview" class="upload-preview" aria-live="polite"></div>

        <small class="input-hint">Enter name + photo only. Each poster gets a fresh auto-generated wish.</small>

        <button id="posterSubmitBtn" type="submit" class="btn-primary">Generate Poster Wish</button>
    </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
