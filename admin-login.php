<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/app.php';

if (isAdminAccount()) {
    header('Location: admin.php');
    exit;
}

$loginMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $loginMessage = 'Enter the administrator email and password.';
    } else {
        $stmt = $conn->prepare("SELECT id, fname, lname, email, password, role, status, class_name FROM regis_form WHERE email = ? AND role = ? AND status = 'approved' LIMIT 1");
        $role = 'admin';
        if ($stmt) {
            $stmt->bind_param('ss', $email, $role);
            $stmt->execute();
            $admin = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        }

        if (!empty($admin) && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['eastCoastLoggedIn'] = true;
            $_SESSION['eastCoastUserId'] = (int) $admin['id'];
            $_SESSION['eastCoastUserEmail'] = $admin['email'];
            $_SESSION['eastCoastUserName'] = trim($admin['fname'] . ' ' . $admin['lname']);
            $_SESSION['eastCoastRole'] = 'Administrator';
            $_SESSION['eastCoastClass'] = $admin['class_name'] ?? '';
            $_SESSION['fname'] = $admin['fname'];
            $_SESSION['lname'] = $admin['lname'];
            $_SESSION['user'] = [
                'id' => (int) $admin['id'],
                'fname' => $admin['fname'],
                'lname' => $admin['lname'],
                'email' => $admin['email'],
                'role' => 'admin'
            ];
            session_write_close();
            header('Location: admin.php');
            exit;
        }

        $loginMessage = 'Administrator email or password is incorrect.';
    }
}

header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Administrator Login | East Coast Secondary School</title>
  <link rel="stylesheet" href="site.css">
  <link rel="stylesheet" href="styles.php">
  <style>
    :root { --admin-ink: #192d42; --admin-blue: #174a74; --admin-gold: #d59a32; }
    body { min-height: 100vh; margin: 0; display: grid; place-items: center; padding: 24px; background: #102a43; font-family: 'DM Sans', Arial, sans-serif; }
    .admin-login { width: min(440px, 100%); padding: 38px; border: 1px solid rgba(255,255,255,.45); border-radius: 18px; background: rgba(255,255,255,.96); box-shadow: 0 24px 60px rgba(0,0,0,.25); }
    .admin-badge { width: 64px; height: 64px; display: grid; place-items: center; margin-bottom: 22px; border-radius: 14px; background: var(--admin-blue); color: #fff; border: 4px solid var(--admin-gold); font-size: 1.7rem; }
    h1 { margin: 0 0 8px; color: var(--admin-ink); }
    .intro { margin: 0 0 24px; color: #647486; }
    .message { margin-bottom: 18px; padding: 12px; border-radius: 8px; background: #fff0ec; color: #a63d2d; }
    label { display: grid; gap: 8px; margin-top: 16px; color: var(--admin-ink); font-weight: 600; }
    input { width: 100%; padding: 13px; box-sizing: border-box; border: 1px solid #ccd6df; border-radius: 8px; font: inherit; }
    input:focus { outline: 3px solid rgba(213,154,50,.25); border-color: var(--admin-blue); }
    button { width: 100%; margin-top: 24px; padding: 13px; border: 0; border-radius: 8px; background: var(--admin-blue); color: #fff; font: inherit; font-weight: 700; cursor: pointer; }
    button:hover { background: #0f3858; }
    .links { display: flex; justify-content: space-between; gap: 16px; margin-top: 20px; font-size: .9rem; }
    .links a { color: var(--admin-blue); }
  </style>
</head>
<body>
  <div class="ec-bg-slideshow" aria-hidden="true">
    <?php foreach ($SLIDE_IMAGES as $image): ?>
      <img class="ec-bg-slide" src="<?php echo htmlspecialchars($image, ENT_QUOTES, 'UTF-8'); ?>" alt="">
    <?php endforeach; ?>
  </div>
  <main class="admin-login">
    <div class="admin-badge" aria-hidden="true">★</div>
    <h1>Administrator login</h1>
    <p class="intro">Use the secure administrator portal to manage the school workspace.</p>
    <?php if ($loginMessage !== ''): ?><p class="message" role="alert"><?php echo htmlspecialchars($loginMessage); ?></p><?php endif; ?>
    <form method="post">
      <label for="email">Administrator email<input id="email" name="email" type="email" autocomplete="username" required></label>
      <label for="password">Password<input id="password" name="password" type="password" autocomplete="current-password" required></label>
      <button type="submit">Sign in as administrator</button>
    </form>
    <div class="links"><a href="login.php">Standard user login</a><a href="res/foget.php">Forgot password?</a></div>
  </main>
  <script src="slideshow.js"></script>
</body>
</html>
