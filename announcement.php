<?php
/**
 * Announcement.php - PHP Version (No HTML)
 * Converts all HTML to PHP output
 */

session_start();

require_once 'app.php';

// Check if user is logged in
if (empty($_SESSION['eastCoastLoggedIn'])) {
    header('Location: login.php');
    exit;
}

$userRole = getRole();
$canPublish = in_array($userRole, ['Teacher', 'Administrator']);
$userName = getUserDisplayName();

// Get announcements from session
$announcements = getList('eastCoastAnnouncements', []);

// Handle form submission
$formMessage = '';
$messageType = 'error';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action === 'publish' && $canPublish) {
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $audience = isset($_POST['audience']) ? trim($_POST['audience']) : 'Students';
        $author = isset($_POST['author']) ? trim($_POST['author']) : $userName;
        $message = isset($_POST['message']) ? trim($_POST['message']) : '';
        
        if (empty($title) || empty($message)) {
            $formMessage = 'Title and message are required.';
            $messageType = 'error';
        } else {
            $newAnnouncement = [
                'title' => $title,
                'message' => $message,
                'audience' => $audience,
                'author' => $author,
                'date' => date('Y-m-d H:i:s'),
                'timestamp' => time()
            ];
            
            $announcements[] = $newAnnouncement;
            saveList('eastCoastAnnouncements', $announcements);
            
            $formMessage = 'Announcement published successfully!';
            $messageType = 'success';
        }
    }
}

// Generate slideshow
function generateAnnouncementSlideshow() {
    global $SLIDE_IMAGES;
    
    $html = '<div class="bg-slideshow" id="bgSlideshow" aria-hidden="true">';
    foreach ($SLIDE_IMAGES as $image) {
        $html .= '<span style="background-image: url(\'' . htmlspecialchars($image) . '\')"></span>';
    }
    $html .= '</div>';
    
    return $html;
}

// Render announcements list
function renderAnnouncementsList($announcements) {
    if (count($announcements) === 0) {
        return '<div class="empty-state">No announcements yet. Post the first update for your school community.</div>';
    }
    
    $html = '';
    // Reverse to show newest first
    $reversed = array_reverse($announcements);
    
    foreach ($reversed as $item) {
        $date = isset($item['date']) ? $item['date'] : date('M d, g:i A', $item['timestamp'] ?? 0);
        
        $html .= '<article class="announcement-item">';
        $html .= '<strong>' . htmlspecialchars($item['title'] ?? '') . '</strong>';
        $html .= '<p>' . htmlspecialchars($item['message'] ?? '') . '</p>';
        $html .= '<small>' . htmlspecialchars($date) . ' · ' . htmlspecialchars($item['author'] ?? 'Unknown') . ' · ' . htmlspecialchars($item['audience'] ?? 'All') . '</small>';
        $html .= '</article>';
    }
    
    return $html;
}

// Output HTML
header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<html lang="en">
<head>
  <link rel="stylesheet" href="site.css">
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>East Coast Secondary School | Announcements</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --ink: #17252b;
      --muted: #718087;
      --line: #e5e9e8;
      --paper: #f7f9f6;
      --white: #fff;
      --coral: #e9674f;
      --teal: #2f8e83;
      --navy: #203d48;
      --amber: #d69a3c;
    }

    * { box-sizing: border-box; }

    body {
      position: relative;
      margin: 0;
      font-family: 'DM Sans', Arial, sans-serif;
      background: linear-gradient(135deg, #edf4f2 0%, #f9f3eb 100%);
      color: var(--ink);
      min-height: 100vh;
    }

    body::before {
      content: "";
      position: fixed;
      inset: 0;
      background: rgba(11, 20, 28, 0.42);
      z-index: 0;
    }

    .bg-slideshow {
      position: fixed;
      inset: 0;
      z-index: -2;
      overflow: hidden;
      background: #0d1b2a;
    }

    .bg-slideshow span {
      position: absolute;
      inset: 0;
      background-size: cover;
      background-position: center;
      opacity: 0;
      transform: scale(1.08);
      animation: backgroundSlide 60s infinite;
    }

    @keyframes backgroundSlide {
      0%, 100% { opacity: 0; transform: scale(1.08); }
      4%, 18% { opacity: 1; }
      22%, 100% { opacity: 0; transform: scale(1.15); }
    }

    .page {
      position: relative;
      z-index: 1;
      max-width: 1100px;
      margin: 40px auto;
      padding: 0 20px 50px;
    }

    .topbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 16px;
      margin-bottom: 28px;
    }

    .topbar h1 {
      margin: 0;
      font-family: 'Space Grotesk', Arial, sans-serif;
      font-size: clamp(2rem, 5vw, 3rem);
    }

    .back-btn,
    .primary-btn,
    .outline-btn {
      border: none;
      border-radius: 12px;
      padding: 12px 18px;
      font-weight: 700;
      cursor: pointer;
    }

    .back-btn {
      background: var(--white);
      color: var(--ink);
      border: 1px solid var(--line);
    }

    .primary-btn {
      background: linear-gradient(135deg, var(--coral) 0%, #f39a74 100%);
      color: var(--white);
      box-shadow: 0 18px 32px rgba(233, 103, 79, 0.22);
    }

    .outline-btn {
      background: transparent;
      color: var(--teal);
      border: 1px solid rgba(47, 142, 131, 0.25);
    }

    .layout {
      display: grid;
      grid-template-columns: 1.05fr 0.95fr;
      gap: 26px;
    }

    .panel {
      background: rgba(255,255,255,0.86);
      border: 1px solid rgba(23,37,43,0.08);
      border-radius: 24px;
      padding: 22px 20px;
      box-shadow: 0 18px 45px rgba(23,37,43,0.08);
    }

    .panel h2 {
      margin: 0 0 18px;
      font-size: 1.35rem;
      font-family: 'Space Grotesk', Arial, sans-serif;
    }

    form {
      display: grid;
      gap: 18px;
    }

    label {
      display: grid;
      gap: 8px;
      font-weight: 600;
    }

    input,
    select,
    textarea {
      width: 100%;
      background: #fbfcfb;
      border: 1px solid var(--line);
      border-radius: 12px;
      padding: 12px 14px;
      font: inherit;
      color: var(--ink);
    }

    textarea {
      min-height: 150px;
      resize: vertical;
    }

    .row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
    }

    .message {
      min-height: 20px;
      margin: 0;
      color: var(--coral);
      font-weight: 600;
    }

    .message.success {
      color: var(--teal);
    }

    .feed-head {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 10px;
      margin-bottom: 16px;
    }

    .feed-head span {
      font-size: 0.8rem;
      font-weight: 700;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: var(--muted);
    }

    .announcement-item {
      border-left: 3px solid var(--coral);
      background: rgba(233, 103, 79, 0.04);
      padding: 14px 14px 12px 16px;
      border-radius: 12px;
      margin-bottom: 14px;
    }

    .announcement-item strong {
      display: block;
      font-size: 1rem;
      margin-bottom: 6px;
    }

    .announcement-item p {
      margin: 0 0 8px;
      color: var(--ink);
      line-height: 1.6;
    }

    .announcement-item small {
      color: var(--muted);
    }

    .empty-state {
      background: rgba(47, 142, 131, 0.04);
      border: 1px dashed rgba(47, 142, 131, 0.35);
      border-radius: 12px;
      padding: 16px;
      color: var(--muted);
    }

    .read-only-notice {
      background: rgba(214, 154, 60, 0.08);
      border: 1px solid rgba(214, 154, 60, 0.2);
      border-radius: 12px;
      padding: 16px;
      color: var(--ink);
    }

    @media (max-width: 760px) {
      .layout,
      .row {
        grid-template-columns: 1fr;
      }

      .topbar {
        flex-direction: column;
        align-items: flex-start;
      }
    }
  </style>
</head>
<body>
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

  <?php echo generateAnnouncementSlideshow(); ?>
  <div class="page">
    <div class="topbar">
      <h1>Announcements</h1>
      <button class="back-btn" onclick="window.location.href='index.php'" type="button">← Back to dashboard</button>
    </div>

    <div class="layout">
      <section class="panel">
        <?php if ($canPublish): ?>
        <h2>Post a school update</h2>
        <form method="POST">
          <input type="hidden" name="action" value="publish">
          
          <label>
            Title
            <input type="text" name="title" placeholder="e.g. Leadership forum this Friday" required />
          </label>

          <label>
            Audience
            <select name="audience">
              <option value="Students">Students</option>
              <option value="Teachers">Teachers</option>
              <option value="All staff and students">All staff and students</option>
            </select>
          </label>

          <label>
            Posted by
            <input type="text" name="author" placeholder="Teacher on duty / Administrator" value="<?php echo htmlspecialchars($userName); ?>" required />
          </label>

          <label>
            Message
            <textarea name="message" placeholder="Share important news, reminders, or updates for students..." required></textarea>
          </label>

          <div class="row">
            <button class="primary-btn" type="submit">Publish announcement</button>
            <button class="outline-btn" type="button" onclick="document.querySelector('form').reset()">Clear form</button>
          </div>

          <?php if (!empty($formMessage)): ?>
          <p class="message <?php echo $messageType === 'success' ? 'success' : ''; ?>">
            <?php echo htmlspecialchars($formMessage); ?>
          </p>
          <?php endif; ?>
        </form>
        <?php else: ?>
        <div class="read-only-notice">
          <strong>Read-only access</strong>
          <p>Only teachers and administrators can post announcements. You can view announcements in the feed on the right.</p>
        </div>
        <?php endif; ?>
      </section>

      <aside class="panel">
        <div class="feed-head">
          <h2>Recent posts</h2>
          <span id="countLabel"><?php echo count($announcements); ?></span>
        </div>
        <div id="announcementList">
          <?php echo renderAnnouncementsList($announcements); ?>
        </div>
      </aside>
    </div>
  </div>
  <script src="slideshow.js"></script>
</body>
</html>
