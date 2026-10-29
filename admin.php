<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/app.php';

requireAdmin();

  $userMessage = '';
  $userMessageType = 'success';
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_action'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
      $userMessage = 'The form expired. Please refresh and try again.';
      $userMessageType = 'error';
    } else {
    $targetId = (int) ($_POST['user_id'] ?? 0);
    $userAction = $_POST['user_action'];
    if ($targetId === (int) ($_SESSION['eastCoastUserId'] ?? 0)) {
      $userMessage = 'The current administrator account cannot be changed here.';
      $userMessageType = 'error';
    } elseif ($userAction === 'approve' || $userAction === 'reject') {
      $status = $userAction === 'approve' ? 'approved' : 'rejected';
      $stmt = $conn->prepare('UPDATE regis_form SET status = ? WHERE id = ? AND role <> \'admin\'');
      $stmt->bind_param('si', $status, $targetId);
      $stmt->execute();
      $stmt->close();
      $userMessage = $status === 'approved' ? 'User approved successfully.' : 'User rejected.';
    } elseif ($userAction === 'role') {
      $role = $_POST['role'] ?? 'student_leader';
      $allowedRoles = ['teacher', 'class_monitor', 'student_leader'];
      if (in_array($role, $allowedRoles, true)) {
        $stmt = $conn->prepare('UPDATE regis_form SET role = ? WHERE id = ? AND role <> \'admin\'');
        $stmt->bind_param('si', $role, $targetId);
        $stmt->execute();
        $stmt->close();
        $userMessage = 'User role updated successfully.';
      }
    } elseif ($userAction === 'class') {
      $className = trim($_POST['class_name'] ?? '');
      $stmt = $conn->prepare('UPDATE regis_form SET class_name = ? WHERE id = ? AND role = \'class_monitor\'');
      $stmt->bind_param('si', $className, $targetId);
      $stmt->execute();
      $stmt->close();
      $userMessage = 'Class monitor assignment updated successfully.';
    } elseif ($userAction === 'attendance_access') {
      $attendanceAccess = (int) ($_POST['attendance_access'] ?? 0) === 1 ? 1 : 0;
      $stmt = $conn->prepare('UPDATE regis_form SET attendance_access = ? WHERE id = ? AND role = \'teacher\' AND status = \'approved\'');
      $stmt->bind_param('ii', $attendanceAccess, $targetId);
      $stmt->execute();
      $stmt->close();
      $userMessage = $attendanceAccess ? 'Teacher assigned as on duty.' : 'Teacher duty access removed.';
    }
    }
  }

  $users = [];
  $userResult = $conn->query("SELECT id, fname, lname, email, role, status, class_name, attendance_access, created_at FROM regis_form ORDER BY FIELD(status, 'pending', 'approved', 'rejected'), created_at DESC");
  if ($userResult) {
    while ($row = $userResult->fetch_assoc()) {
      $users[] = $row;
    }
  }

$userName = getUserDisplayName();
$csrfToken = csrfToken();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Dashboard | East Coast Secondary School</title>
  <link rel="stylesheet" href="site.css">
  <style>
    .admin-page{min-height:100vh;padding:40px 20px}.admin-wrap{max-width:1000px;margin:auto}.admin-card{background:rgba(255,255,255,.94);border-radius:22px;padding:30px;box-shadow:0 18px 50px rgba(0,0,0,.12)}
    .admin-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:18px;margin-top:24px}.admin-action{display:block;text-decoration:none;padding:22px;border:1px solid #e5e9e8;border-radius:16px;background:#fff;color:#173b36}.admin-action:hover{transform:translateY(-2px);box-shadow:0 8px 25px rgba(0,0,0,.08)}.admin-action strong{display:block;font-size:18px;margin-bottom:7px}.admin-action span{color:#687b78;font-size:14px}
    .admin-logo{width:74px;height:74px;object-fit:contain}.admin-head{display:flex;align-items:center;gap:18px}.back{display:inline-block;margin-top:22px}
    .admin-message{margin:20px 0 0;padding:12px 14px;border-radius:8px;background:#e7f4f1;color:#21685f}.admin-message.error{background:#fff0ec;color:#ad402f}
    .user-list{display:grid;gap:12px;margin-top:18px}.user-row{display:grid;grid-template-columns:1.3fr 1fr auto;gap:14px;align-items:center;padding:15px;border:1px solid #e5e9e8;border-radius:12px;background:#fff}.user-row strong,.user-row small{display:block}.user-row small{color:#687b78;margin-top:4px}.user-actions{display:flex;flex-wrap:wrap;justify-content:flex-end;gap:8px}.user-actions form{display:flex;gap:6px;align-items:center}.user-actions select{padding:7px;border:1px solid #d5dfda;border-radius:7px}.user-actions button{padding:7px 10px;border:0;border-radius:7px;background:#174a74;color:#fff;cursor:pointer}.user-actions .reject{background:#a63d2d}.status-badge{display:inline-block;width:max-content;padding:4px 8px;border-radius:999px;font-size:12px;font-weight:700;text-transform:capitalize;background:#fff5e2;color:#94651d}.status-badge.approved{background:#e7f4f1;color:#21685f}.status-badge.rejected{background:#fff0ec;color:#ad402f}
    @media(max-width:720px){.user-row{grid-template-columns:1fr}.user-actions{justify-content:flex-start}}
  </style>
</head>
<body class="admin-page">
<div class="ec-bg-slideshow" aria-hidden="true">
<?php foreach ($SLIDE_IMAGES as $image): ?><img class="ec-bg-slide" src="<?php echo htmlspecialchars($image); ?>" alt=""><?php endforeach; ?>
</div>
<main class="admin-wrap"><section class="admin-card">
  <div class="admin-head"><a href="admin.php" title="Administrator"><img class="admin-logo" src="logo_picture.png" alt="East Coast Secondary School logo"></a><div><h1>Administrator Dashboard</h1><p>Welcome, <?php echo htmlspecialchars($userName); ?>.</p></div></div>
  <div class="admin-grid">
    <a class="admin-action" href="admin-settings.php"><strong>⚙ Admin Settings</strong><span>Manage school logo and administrator settings.</span></a>
    <a class="admin-action" href="index.php"><strong>⌂ Main Dashboard</strong><span>Return to the East Coast workspace.</span></a>
    <a class="admin-action" href="register.php"><strong>＋ Register User</strong><span>Create a new school account.</span></a>
    <a class="admin-action" href="logout.php"><strong>↪ Log out</strong><span>Sign out of the administrator account.</span></a>
  </div>
  <?php if ($userMessage !== ''): ?><p class="admin-message <?php echo htmlspecialchars($userMessageType); ?>" role="status"><?php echo htmlspecialchars($userMessage); ?></p><?php endif; ?>
    <h2>User approvals and roles</h2>
    <p>Approve registrations before users can access the school workspace.</p>
    <div class="user-list">
      <?php foreach ($users as $user): ?>
        <div class="user-row">
          <div><strong><?php echo htmlspecialchars(trim($user['fname'] . ' ' . $user['lname'])); ?></strong><small><?php echo htmlspecialchars($user['email']); ?></small></div>
          <div><span class="status-badge <?php echo htmlspecialchars($user['status']); ?>"><?php echo htmlspecialchars($user['status']); ?></span><small><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $user['role']))); ?></small></div>
          <?php if ($user['role'] === 'admin'): ?>
            <small>Protected administrator</small>
          <?php else: ?>
            <div class="user-actions">
              <?php if ($user['status'] !== 'approved'): ?><form method="post"><input type="hidden" name="user_id" value="<?php echo (int) $user['id']; ?>"><button type="submit" name="user_action" value="approve">Approve</button></form><?php endif; ?>
              <?php if ($user['status'] !== 'rejected'): ?><form method="post"><input type="hidden" name="user_id" value="<?php echo (int) $user['id']; ?>"><button class="reject" type="submit" name="user_action" value="reject">Reject</button></form><?php endif; ?>
              <form method="post"><input type="hidden" name="user_id" value="<?php echo (int) $user['id']; ?>"><input type="hidden" name="user_action" value="role"><select name="role" aria-label="Role for <?php echo htmlspecialchars($user['email']); ?>"><option value="student_leader" <?php echo $user['role'] === 'student_leader' ? 'selected' : ''; ?>>Student leader</option><option value="class_monitor" <?php echo $user['role'] === 'class_monitor' ? 'selected' : ''; ?>>Class monitor</option><option value="teacher" <?php echo $user['role'] === 'teacher' ? 'selected' : ''; ?>>Teacher</option></select><button type="submit">Save role</button></form>
              <?php if ($user['role'] === 'class_monitor'): ?><form method="post"><input type="hidden" name="user_id" value="<?php echo (int) $user['id']; ?>"><input type="hidden" name="user_action" value="class"><select name="class_name" aria-label="Class for <?php echo htmlspecialchars($user['email']); ?>"><option value="">Unassigned</option><?php foreach (['Form 1 - Science', 'Form 1 - Arts', 'Form 1 - Commercial', 'Form 2 - Science', 'Form 2 - Arts', 'Form 2 - Commercial', 'Form 3 - Science', 'Form 3 - Arts', 'Form 3 - Commercial', 'Form 4 - Science', 'Form 4 - Arts', 'Form 4 - Commercial'] as $classOption): ?><option <?php echo ($user['class_name'] ?? '') === $classOption ? 'selected' : ''; ?>><?php echo htmlspecialchars($classOption); ?></option><?php endforeach; ?></select><button type="submit">Save class</button></form><?php endif; ?>
              <?php if ($user['role'] === 'teacher' && $user['status'] === 'approved'): ?><form method="post"><input type="hidden" name="user_id" value="<?php echo (int) $user['id']; ?>"><input type="hidden" name="user_action" value="attendance_access"><input type="hidden" name="attendance_access" value="<?php echo !empty($user['attendance_access']) ? '0' : '1'; ?>"><button type="submit"><?php echo !empty($user['attendance_access']) ? 'Remove duty access' : 'Make teacher on duty'; ?></button></form><?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
  <a class="text-link back" href="index.php">← Back to dashboard</a>
</section></main>
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
</body></html>
