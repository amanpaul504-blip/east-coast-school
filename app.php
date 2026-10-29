<?php
/**
 * Application Utilities - PHP Version
 * Converted from JavaScript app.js
 */

// Storage keys constants
define('STORAGE_KEYS', json_encode([
    'cases' => 'eastCoastCases',
    'reports' => 'eastCoastReports',
    'tasks' => 'eastCoastTasks',
    'leadership' => 'eastCoastLeadership'
]));

// Slide images array
$SLIDE_IMAGES = [
    'background slide/FB_IMG_1788004990501.jpg',
    'background slide/FB_IMG_1788004997802.jpg',
    'background slide/FB_IMG_1788005001986.jpg',
    'background slide/FB_IMG_1788005004385.jpg',
    'background slide/FB_IMG_1788005006912.jpg',
    'background slide/FB_IMG_1788005009324.jpg',
    'background slide/FB_IMG_1788005011610.jpg',
    'background slide/FB_IMG_1788005013995.jpg',
    'background slide/FB_IMG_1788005017102.jpg',
    'background slide/FB_IMG_1788005021346.jpg',
    'background slide/FB_IMG_1788005036950.jpg',
    'background slide/FB_IMG_1788005039159.jpg',
    'background slide/FB_IMG_1788005041269.jpg',
    'background slide/FB_IMG_1788005044491.jpg',
    'background slide/FB_IMG_1788005050850.jpg',
    'background slide/FB_IMG_1788005059802.jpg',
    'background slide/FB_IMG_1788005062855.jpg',
    'background slide/FB_IMG_1788005069084.jpg',
    'background slide/FB_IMG_1788005087230.jpg'
];

// Role access control matrix
$ROLE_ACCESS = [
    'Student leader' => ['overview', 'cases', 'tasks', 'announcements'],
    'Class monitor' => ['overview', 'attendance', 'cases', 'tasks', 'announcements'],
    'Teacher' => ['overview', 'cases', 'reports', 'tasks', 'attendance', 'announcements', 'statistics'],
    'Administrator' => ['overview', 'cases', 'reports', 'tasks', 'attendance', 'announcements', 'team', 'statistics']
];

/**
 * Get a list from session storage
 * 
 * @param string $key Storage key
 * @param array $fallback Default value if not found
 * @return array The stored list or fallback
 */
function getList($key, $fallback = []) {
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = $fallback;
        return $fallback;
    }
    
    $saved = $_SESSION[$key];
    if (!is_array($saved)) {
        $_SESSION[$key] = $fallback;
        return $fallback;
    }
    
    return $saved;
}

/**
 * Save a list to session storage
 * 
 * @param string $key Storage key
 * @param array $data Data to save
 */
function saveList($key, $data) {
    $_SESSION[$key] = $data;
}

/**
 * Get user's current role
 * 
 * @return string User role
 */
function getRole() {
    return isset($_SESSION['eastCoastRole']) ? $_SESSION['eastCoastRole'] : 'Student leader';
}

/**
 * Get the configured school logo path.
 *
 * @return string Relative logo path
 */
function getBrandLogoPath() {
    $uploadDirectory = __DIR__ . '/uploads';
    $allowedExtensions = ['png', 'jpg', 'jpeg', 'webp'];

    foreach ($allowedExtensions as $extension) {
        if (is_file($uploadDirectory . '/logo.' . $extension)) {
            return 'uploads/logo.' . $extension;
        }
    }

    if (is_file(__DIR__ . '/logo_picture.png')) {
        return 'logo_picture.png';
    }

    return 'LOGO.pdf';
}

/**
 * Check if user can access a specific view
 * 
 * @param string $view View name to check
 * @return bool True if user has access
 */
function canAccessView($view) {
    global $ROLE_ACCESS;
    
    $userRole = getRole();
    $allowed = isset($ROLE_ACCESS[$userRole]) ? $ROLE_ACCESS[$userRole] : $ROLE_ACCESS['Student leader'];
    
    return in_array($view, $allowed);
}

/**
 * Get user display name
 * 
 * @return string User's display name
 */
function getUserDisplayName() {
    $savedName = isset($_SESSION['eastCoastUserName']) ? $_SESSION['eastCoastUserName'] : '';
    
    if (!empty($savedName) && trim($savedName)) {
        return trim($savedName);
    }
    
    $email = isset($_SESSION['eastCoastUserEmail']) ? $_SESSION['eastCoastUserEmail'] : '';
    
    if (!empty($email)) {
        $parts = explode('@', $email);
        $localPart = isset($parts[0]) ? $parts[0] : 'User';
        
        $name = preg_replace('/[._-]+/', ' ', $localPart);
        $name = ucwords(strtolower($name));
        
        return trim($name) ?: 'User';
    }
    
    return 'Alex Morgan';
}

/**
 * Get user initials from name
 * 
 * @param string $name User's full name
 * @return string Two-letter initials
 */
function getUserInitials($name = '') {
    $parts = preg_split('/\s+/', trim($name ?: 'User'), -1, PREG_SPLIT_NO_EMPTY);
    
    if (count($parts) === 1) {
        return strtoupper(substr($parts[0], 0, 2));
    }
    
    $first = isset($parts[0]) ? $parts[0][0] : '';
    $last = isset($parts[count($parts) - 1]) ? $parts[count($parts) - 1][0] : '';
    
    return strtoupper($first . $last);
}

/**
 * Get greeting based on time of day
 * 
 * @return string Greeting message
 */
function getGreeting() {
    $hour = (int)date('H');
    
    if ($hour >= 5 && $hour < 12) {
        return 'Good morning';
    } elseif ($hour >= 12 && $hour < 15) {
        return 'Good noon';
    } elseif ($hour >= 15 && $hour < 18) {
        return 'Good afternoon';
    } elseif ($hour >= 18 && $hour < 22) {
        return 'Good evening';
    }
    
    return 'Good midnight';
}

/**
 * Check if user is administrator
 * 
 * @return bool True if user is admin
 */
function isAdmin() {
    if (function_exists('isAdminAccount')) {
        return isAdminAccount();
    }

    return getRole() === 'Administrator';
}

/**
 * Check if user is teacher or administrator
 * 
 * @return bool True if user is teacher or admin
 */
function isTeacher() {
    $role = getRole();
    return $role === 'Teacher' || $role === 'Administrator';
}

/**
 * Format status for display
 * 
 * @param string $status Status code
 * @return string Formatted status
 */
function formatStatus($status) {
    $map = [
        'pending' => 'Pending',
        'progress' => 'In progress',
        'resolved' => 'Resolved',
        'closed' => 'Closed',
        'urgent' => 'Urgent',
        'normal' => 'Normal'
    ];
    
    return isset($map[$status]) ? $map[$status] : ($status ?: 'Pending');
}

/**
 * Generate empty state HTML
 * 
 * @param string $title Empty state title
 * @param string $text Empty state description
 * @return string HTML for empty state
 */
function emptyState($title, $text) {
    return sprintf(
        '<div class="empty-state-box"><strong>%s</strong><p>%s</p></div>',
        htmlspecialchars($title),
        htmlspecialchars($text)
    );
}

/**
 * Render overview dashboard
 * 
 * @return string HTML for overview
 */
function renderOverview() {
    $cases = getList('eastCoastCases', []);
    $reports = getList('eastCoastReports', []);
    $tasks = getList('eastCoastTasks', []);
    $attendance = getList('eastCoastAttendance', []);
    
    $present = 0;
    foreach ($attendance as $row) {
        if (isset($row['status']) && $row['status'] === 'Present') {
            $present++;
        }
    }
    
    $percent = count($attendance) > 0 ? round(($present / count($attendance)) * 100) : 0;
    
    $incompleteTasks = array_filter($tasks, function($task) {
        return !isset($task['complete']) || !$task['complete'];
    });
    
    $html = '<div class="stat-grid">';
    $html .= '<div class="stat"><span class="stat-label">Open cases</span><strong>' . count($cases) . '</strong><span class="stat-change up">live</span></div>';
    $html .= '<div class="stat"><span class="stat-label">Tasks due</span><strong>' . count($incompleteTasks) . '</strong><span class="stat-change neutral">this week</span></div>';
    $html .= '<div class="stat"><span class="stat-label">Attendance</span><strong>' . $percent . '%</strong><span class="stat-change up">' . $present . '/' . count($attendance) . '</span></div>';
    $html .= '<div class="stat"><span class="stat-label">Reports filed</span><strong>' . count($reports) . '</strong><span class="stat-change up">today</span></div>';
    $html .= '</div>';
    
    $html .= '<div class="dashboard-grid">';
    
    // Recent cases panel
    $html .= '<div class="panel"><div class="panel-head"><div><h2>Recent cases</h2><div class="panel-kicker">Keep an eye on your active reports</div></div><button class="text-link" data-view="cases">View all →</button></div><div class="case-list">';
    
    if (count($cases) > 0) {
        $recentCases = array_slice($cases, 0, 3);
        foreach ($recentCases as $item) {
            $html .= '<div class="case-row">';
            $html .= '<div class="case-type">♧</div>';
            $html .= '<div class="case-info"><strong>' . htmlspecialchars($item['title'] ?? '') . '</strong>';
            $html .= '<small>' . htmlspecialchars($item['location'] ?? 'General campus') . ' · ' . formatStatus($item['status'] ?? '') . '</small></div>';
            $statusClass = (isset($item['urgency']) && $item['urgency'] === 'urgent') ? 'pending' : 'progress';
            $statusText = (isset($item['urgency']) && $item['urgency'] === 'urgent') ? 'Urgent' : 'In progress';
            $html .= '<span class="status ' . $statusClass . '">' . $statusText . '</span>';
            $html .= '</div>';
        }
    } else {
        $html .= emptyState('No cases yet', 'Use the new case form to add the first incident.');
    }
    
    $html .= '</div></div>';
    
    // Attendance card
    $html .= '<div class="attendance-card"><span class="eyebrow">TODAY\'S CHECK-IN</span><h2>Attendance snapshot</h2>';
    $html .= '<p>Class attendance for today.</p><div class="attendance-number">' . $percent . '%</div>';
    $html .= '<div class="attendance-foot">' . $present . ' present of ' . count($attendance) . ' students</div></div>';
    
    // Daily reports panel
    $html .= '<div class="panel"><div class="panel-head"><div><h2>Daily reports</h2><div class="panel-kicker">Reports filed this week</div></div><button class="text-link" data-view="reports">See reports →</button></div><div class="case-list">';
    
    if (count($reports) > 0) {
        $recentReports = array_slice($reports, 0, 2);
        foreach ($recentReports as $item) {
            $html .= '<div class="case-row">';
            $html .= '<div class="case-type teal">✓</div>';
            $html .= '<div class="case-info"><strong>' . htmlspecialchars($item['title'] ?? '') . '</strong>';
            $html .= '<small>' . htmlspecialchars($item['area'] ?? 'Campus overview') . ' · ' . formatStatus($item['status'] ?? '') . '</small></div>';
            $statusClass = (isset($item['status']) && $item['status'] === 'resolved') ? 'resolved' : 'pending';
            $html .= '<span class="status ' . $statusClass . '">' . formatStatus($item['status'] ?? '') . '</span>';
            $html .= '</div>';
        }
    } else {
        $html .= emptyState('No reports yet', 'Add a daily report to keep the school updated.');
    }
    
    $html .= '</div></div></div>';
    
    return $html;
}

/**
 * Render cases management view
 * 
 * @return string HTML for cases view
 */
function renderCases() {
    $items = getList('eastCoastCases', []);
    
    $html = '<div class="panel"><div class="panel-head"><div><h2>Case management</h2>';
    $html .= '<div class="panel-kicker">Track, verify and resolve student concerns</div></div>';
    $html .= '<button class="primary-btn" id="addCaseBtn">＋ New case</button></div><div class="case-list">';
    
    if (count($items) > 0) {
        foreach ($items as $index => $item) {
            $html .= '<div class="case-row">';
            $html .= '<div class="case-type">♧</div>';
            $html .= '<div class="case-info"><strong>' . htmlspecialchars($item['title'] ?? '') . '</strong>';
            $html .= '<small>' . htmlspecialchars($item['location'] ?? 'General') . ' · ' . ($item['urgency'] ?? 'normal') . ' urgency</small></div>';
            $html .= '<div style="display:flex; align-items:center; gap:8px;">';
            $statusClass = (isset($item['status']) && $item['status'] === 'resolved') ? 'resolved' : 'progress';
            $html .= '<span class="status ' . $statusClass . '">' . formatStatus($item['status'] ?? '') . '</span>';
            $html .= '<button class="text-link" data-remove-case="' . $index . '" type="button">Remove</button>';
            $html .= '</div></div>';
        }
    } else {
        $html .= emptyState('No cases yet', 'Use the form to add the first case.');
    }
    
    $html .= '</div></div>';
    
    return $html;
}

/**
 * Render reports management view
 * 
 * @return string HTML for reports view
 */
function renderReports() {
    $items = getList('eastCoastReports', []);
    
    $html = '<div class="panel"><div class="panel-head"><div><h2>Daily reports</h2>';
    $html .= '<div class="panel-kicker">Share the daily school updates for the team</div></div>';
    $html .= '<button class="primary-btn" id="addReportBtn">＋ Add report</button></div><div class="case-list">';
    
    if (count($items) > 0) {
        foreach ($items as $index => $item) {
            $html .= '<div class="case-row">';
            $html .= '<div class="case-type teal">✓</div>';
            $html .= '<div class="case-info"><strong>' . htmlspecialchars($item['title'] ?? '') . '</strong>';
            $html .= '<small>' . htmlspecialchars($item['area'] ?? 'Campus') . ' · ' . formatStatus($item['status'] ?? '') . '</small></div>';
            $html .= '<div style="display:flex; align-items:center; gap:8px;">';
            $statusClass = (isset($item['status']) && $item['status'] === 'resolved') ? 'resolved' : 'pending';
            $html .= '<span class="status ' . $statusClass . '">' . formatStatus($item['status'] ?? '') . '</span>';
            $html .= '<button class="text-link" data-remove-report="' . $index . '" type="button">Remove</button>';
            $html .= '</div></div>';
        }
    } else {
        $html .= emptyState('No reports yet', 'Add the first report to keep everyone informed.');
    }
    
    $html .= '</div></div>';
    
    return $html;
}

/**
 * Render tasks management view
 * 
 * @return string HTML for tasks view
 */
function renderTasks() {
    $items = getList('eastCoastTasks', []);
    
    $html = '<div class="panel"><div class="panel-head"><div><h2>Assigned tasks</h2>';
    $html .= '<div class="panel-kicker">Small actions that keep the school moving</div></div>';
    $html .= '<button class="primary-btn" id="addTaskBtn">＋ New task</button></div><div class="task-list">';
    
    if (count($items) > 0) {
        foreach ($items as $index => $item) {
            $html .= '<div class="task-row">';
            $done = isset($item['complete']) && $item['complete'] ? 'done' : '';
            $checkmark = isset($item['complete']) && $item['complete'] ? '✓' : '';
            $html .= '<span class="task-check ' . $done . '">' . $checkmark . '</span>';
            $html .= '<div class="case-info"><strong>' . htmlspecialchars($item['title'] ?? '') . '</strong>';
            $dueText = isset($item['due']) && !empty($item['due']) ? 'Due ' . $item['due'] : 'No due date';
            $html .= '<small>' . $dueText . ' · ' . ($item['priority'] ?? 'Normal') . '</small></div>';
            $html .= '<div style="display:flex; align-items:center; gap:8px;">';
            $toggleText = isset($item['complete']) && $item['complete'] ? 'Undo' : 'Done';
            $html .= '<button class="text-link" data-task-toggle="' . $index . '" type="button">' . $toggleText . '</button>';
            $html .= '<button class="text-link" data-remove-task="' . $index . '" type="button">Remove</button>';
            $html .= '</div></div>';
        }
    } else {
        $html .= emptyState('No tasks yet', 'Create the first task for your team.');
    }
    
    $html .= '</div></div>';
    
    return $html;
}

/**
 * Render team management view
 * 
 * @return string HTML for team view
 */
function renderTeam() {
    $items = getList('eastCoastLeadership', []);
    
    $html = '<div class="panel"><div class="panel-head"><div><h2>School leadership team</h2>';
    $html .= '<div class="panel-kicker">Your school leadership community</div></div>';
    
    if (isAdmin()) {
        $html .= '<button class="primary-btn" id="addTeamBtn">＋ Add member</button>';
    } else {
        $html .= '<span class="tag">Admin only</span>';
    }
    
    $html .= '</div><div class="case-list">';
    
    if (count($items) > 0) {
        foreach ($items as $index => $member) {
            $html .= '<div class="case-row">';
            $initials = getUserInitials($member['name'] ?? 'A');
            $html .= '<span class="avatar small">' . htmlspecialchars($initials) . '</span>';
            $html .= '<div class="case-info"><strong>' . htmlspecialchars($member['name'] ?? '') . '</strong>';
            $html .= '<small>' . htmlspecialchars($member['role'] ?? '') . '</small></div>';
            $html .= '<div style="display:flex; align-items:center; gap:8px;">';
            $html .= '<span class="status resolved">Active</span>';
            
            if (isAdmin()) {
                $html .= '<button class="text-link" data-remove-member="' . $index . '" type="button">Remove</button>';
            }
            
            $html .= '</div></div>';
        }
    } else {
        $html .= emptyState('No leadership members yet', 'Only an administrator can add the first member.');
    }
    
    $html .= '</div></div>';
    
    return $html;
}

/**
 * Get current time and date formatted
 * 
 * @return array Array with 'time' and 'date' keys
 */
function getFormattedDateTime() {
    return [
        'time' => date('g:i:s A'),
        'date' => date('l, F j, Y'),
        'timezone' => date_default_timezone_get()
    ];
}

?>
