<?php

require_once __DIR__ . '/init.php';
requireAdmin();

$message = '';
$messageType = 'success';
$uploadDirectory = __DIR__ . '/uploads';

if (!is_dir($uploadDirectory)) {
    mkdir($uploadDirectory, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $message = 'The form expired. Please refresh and try again.';
        $messageType = 'error';
        $action = '';
    } else {
        $action = $_POST['action'] ?? '';
    }

    if ($action === 'logo') {
        $file = $_FILES['logo'] ?? null;
        $allowedTypes = [
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
            'image/webp' => 'webp'
        ];

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $message = 'Please choose a PNG, JPG, or WebP logo.';
            $messageType = 'error';
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $message = 'The logo must be smaller than 2 MB.';
            $messageType = 'error';
        } else {
            $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
            if (!isset($allowedTypes[$mimeType]) || @getimagesize($file['tmp_name']) === false) {
                $message = 'Only valid PNG, JPG, or WebP images are allowed.';
                $messageType = 'error';
            } else {
                foreach (['png', 'jpg', 'jpeg', 'webp'] as $extension) {
                    $oldLogo = $uploadDirectory . '/logo.' . $extension;
                    if (is_file($oldLogo)) {
                        unlink($oldLogo);
                    }
                }

                $destination = $uploadDirectory . '/logo.' . $allowedTypes[$mimeType];
                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $message = 'Logo updated successfully.';
                } else {
                    $message = 'The logo could not be saved.';
                    $messageType = 'error';
                }
            }
        }
    } elseif ($action === 'password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $stmt = $pdo->prepare('SELECT password FROM regis_form WHERE id = ? AND role = ? LIMIT 1');
        $stmt->execute([$_SESSION['eastCoastUserId'], 'admin']);
        $admin = $stmt->fetch();

        if (!$admin || !password_verify($currentPassword, $admin['password'])) {
            $message = 'The current administrator password is incorrect.';
            $messageType = 'error';
        } elseif (strlen($newPassword) < 6) {
            $message = 'The new password must be at least 6 characters long.';
            $messageType = 'error';
        } elseif ($newPassword !== $confirmPassword) {
            $message = 'The new passwords do not match.';
            $messageType = 'error';
        } else {
            $update = $pdo->prepare('UPDATE regis_form SET password = ? WHERE id = ? AND role = ?');
            $update->execute([password_hash($newPassword, PASSWORD_DEFAULT), $_SESSION['eastCoastUserId'], 'admin']);
            $message = 'Administrator password updated successfully.';
        }
    }
}

$logoPath = 'LOGO.pdf';
$csrfToken = csrfToken();
foreach (['png', 'jpg', 'jpeg', 'webp'] as $extension) {
    if (is_file(__DIR__ . '/uploads/logo.' . $extension)) {
        $logoPath = 'uploads/logo.' . $extension;
        break;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <link rel="stylesheet" href="site.css">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Administrator settings | East Coast Secondary School</title>
  <link rel="stylesheet" href="styles.php">
    <style>
        .admin-settings-page { min-height: 100vh; padding: 32px 20px; background: #f7f9f6; }
        .admin-settings { width: min(760px, 100%); margin: 0 auto; }
        .admin-settings h1 { margin: 18px 0 8px; }
        .admin-settings > p { color: #718087; }
        .admin-settings .panel { margin-top: 20px; padding: 24px; }
        .admin-settings form { display: grid; gap: 16px; }
        .admin-settings label { display: grid; gap: 8px; }
        .admin-settings input[type="password"],
        .admin-settings input[type="file"] { width: 100%; padding: 12px; border: 1px solid #e5e9e8; border-radius: 8px; font: inherit; }
        .admin-logo-preview { display: block; width: 96px; height: 96px; object-fit: contain; margin: 12px 0 20px; }
        .admin-message { padding: 12px 14px; border-radius: 8px; background: #e7f4f1; color: #21685f !important; }
        .admin-message.error { background: #fff0ec; color: #ad402f !important; }
    </style>
</head>
<body class="admin-settings-page">
<div class="ec-bg-slideshow" aria-hidden="true">
  <img class="ec-bg-slide" src="background slide/FB_IMG_1788005050850.jpg" alt="">
  <img class="ec-bg-slide" src="background slide/FB_IMG_1788004990501.jpg" alt="">
  <img class="ec-bg-slide" src="background slide/FB_IMG_1788005059802.jpg" alt="">
  <img class="ec-bg-slide" src="background slide/FB_IMG_1788005013995.jpg" alt="">
  <img class="ec-bg-slide" src="background slide/FB_IMG_1788005001986.jpg" alt="">
  <img class="ec-bg-slide" src="background slide/FB_IMG_1788005017102.jpg" alt="">
  <img class="ec-bg-slide" src="background slide/FB_IMG_1788005011610.jpg" alt="">
  <img class="ec-bg-slide" src="background slide/FB_IMG_1788005021346.jpg" alt="">
  <img class="ec-bg-slide" src="background slide/FB_IMG_1788005036950.jpg" alt="">
</div>

  <main class="admin-settings">
    <a href="index.php" class="text-link">← Back to dashboard</a>
    <h1>Administrator settings</h1>
    <p>These settings are visible only to the authenticated administrator.</p>

    <?php if ($message !== ''): ?>
      <p class="admin-message <?php echo htmlspecialchars($messageType); ?>"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <section class="panel">
      <h2>School logo</h2>
      <img class="admin-logo-preview" src="<?php echo htmlspecialchars($logoPath); ?>" alt="Current school logo">
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="logo">
        <label>Upload a new logo<input type="file" name="logo" accept="image/png,image/jpeg,image/webp" required></label>
        <button class="primary-btn" type="submit">Save logo</button>
      </form>
    </section>

    <section class="panel">
      <h2>Administrator password</h2>
      <form method="post">
        <input type="hidden" name="action" value="password">
        <label>Current password<input type="password" name="current_password" required></label>
        <label>New password<input type="password" name="new_password" minlength="6" required></label>
        <label>Confirm new password<input type="password" name="confirm_password" minlength="6" required></label>
        <button class="primary-btn" type="submit">Update password</button>
      </form>
    </section>
  </main>
  <script src="slideshow.js"></script>
    <script>
        document.querySelectorAll('form[method="post"]').forEach(function (form) {
            var token = document.createElement('input');
            token.type = 'hidden';
            token.name = 'csrf_token';
            token.value = <?php echo json_encode($csrfToken); ?>;
            form.appendChild(token);
        });
    </script>
</body>
</html>