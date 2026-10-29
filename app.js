const STORAGE_KEYS = {
  cases: 'eastCoastCases',
  reports: 'eastCoastReports',
  tasks: 'eastCoastTasks',
  leadership: 'eastCoastLeadership'
};

const slideImages = [
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

const slideshow = document.getElementById('bgSlideshow');
if (slideshow) {
  slideImages.forEach((src, index) => {
    const slide = document.createElement('span');
    slide.style.backgroundImage = `url("${src}")`;
    slide.style.animationDelay = `${index * 3.2}s`;
    slideshow.appendChild(slide);
  });
}

const modal = document.getElementById('modal');
const modalContent = document.getElementById('modalContent');
const viewContainer = document.getElementById('viewContainer');
const pageTitle = document.getElementById('pageTitle');
const pageSubtitle = document.getElementById('pageSubtitle');

function closeModalDialog() {
  if (!modal) return;
  modal.classList.remove('open');
  if (modalContent) {
    modalContent.innerHTML = '';
  }
}

if (document.getElementById('closeModal')) {
  document.getElementById('closeModal').addEventListener('click', closeModalDialog);
}

if (modal) {
  modal.addEventListener('click', (event) => {
    if (event.target === modal) closeModalDialog();
  });
}

function getList(key, fallback = []) {
  const saved = JSON.parse(localStorage.getItem(key) || 'null');
  if (!Array.isArray(saved)) {
    localStorage.setItem(key, JSON.stringify(fallback));
    return [...fallback];
  }
  return saved;
}

function saveList(key, data) {
  localStorage.setItem(key, JSON.stringify(data));
}

function escapeSearchText(value) {
  return String(value ?? '').replace(/[&<>'"]/g, (character) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    "'": '&#39;',
    '"': '&quot;'
  }[character]));
}

function getSearchRecords() {
  const records = [];
  const addRecords = (key, view, label, fields) => {
    getList(key, []).forEach((item) => {
      const title = fields.map((field) => item[field]).find((value) => value) || label;
      const searchableText = fields.map((field) => item[field] || '').join(' ');
      records.push({ title, label, searchableText, view });
    });
  };

  addRecords(STORAGE_KEYS.cases, 'cases', 'Case', ['title', 'location', 'status', 'reportedBy']);
  addRecords(STORAGE_KEYS.reports, 'reports', 'Daily report', ['title', 'area', 'status']);
  addRecords(STORAGE_KEYS.tasks, 'tasks', 'Task', ['title', 'priority', 'due']);
  addRecords('eastCoastAnnouncements', 'announcements', 'Announcement', ['title', 'message', 'audience', 'author']);
  addRecords(STORAGE_KEYS.leadership, 'team', 'Leadership member', ['name', 'role']);
  return records;
}

function setupGlobalSearch() {
  const searchInput = document.getElementById('globalSearch');
  const searchResults = document.getElementById('searchResults');
  if (!searchInput || !searchResults) return;

  const closeResults = () => {
    searchResults.classList.remove('open');
    searchResults.innerHTML = '';
  };

  const showResults = () => {
    const query = searchInput.value.trim().toLowerCase();
    if (!query) {
      closeResults();
      return;
    }

    const matches = getSearchRecords()
      .filter((record) => `${record.title} ${record.label} ${record.searchableText}`.toLowerCase().includes(query))
      .slice(0, 8);

    searchResults.innerHTML = matches.length
      ? matches.map((record) => `
          <button class="search-result" type="button" role="option" data-search-view="${escapeSearchText(record.view)}">
            <strong>${escapeSearchText(record.title)}</strong>
            <small>${escapeSearchText(record.label)}</small>
          </button>
        `).join('')
      : '<span class="search-empty">No matching dashboard records.</span>';
    searchResults.classList.add('open');

    searchResults.querySelectorAll('[data-search-view]').forEach((result) => {
      result.addEventListener('click', () => {
        loadView(result.dataset.searchView);
        closeResults();
        searchInput.blur();
      });
    });
  };

  searchInput.addEventListener('input', showResults);
  searchInput.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      searchInput.value = '';
      closeResults();
    }
  });
  document.addEventListener('click', (event) => {
    if (!event.target.closest('.global-search')) closeResults();
  });
}

function setupSettings() {
  const settingsModal = document.getElementById('settingsModal');
  const themeSelect = document.getElementById('themeSelect');
  const densitySelect = document.getElementById('densitySelect');
  if (!settingsModal || !themeSelect || !densitySelect) return;

  const savedTheme = localStorage.getItem('eastCoastTheme') || 'system';
  const savedDensity = localStorage.getItem('eastCoastDensity') || 'comfortable';
  themeSelect.value = savedTheme;
  densitySelect.value = savedDensity;

  const applyTheme = (theme) => {
    const dark = theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
    document.body.classList.toggle('dark-mode', dark);
  };

  applyTheme(savedTheme);
  document.body.classList.toggle('compact-mode', savedDensity === 'compact');
  themeSelect.addEventListener('change', () => {
    localStorage.setItem('eastCoastTheme', themeSelect.value);
    applyTheme(themeSelect.value);
  });
  densitySelect.addEventListener('change', () => {
    localStorage.setItem('eastCoastDensity', densitySelect.value);
    document.body.classList.toggle('compact-mode', densitySelect.value === 'compact');
  });

  const openSettings = () => {
    settingsModal.classList.add('open');
    settingsModal.setAttribute('aria-hidden', 'false');
  };
  const closeSettings = () => {
    settingsModal.classList.remove('open');
    settingsModal.setAttribute('aria-hidden', 'true');
  };
  document.getElementById('settingsBtn')?.addEventListener('click', openSettings);
  document.getElementById('topSettingsBtn')?.addEventListener('click', openSettings);
  document.getElementById('profileBtn')?.addEventListener('click', openSettings);
  document.getElementById('closeSettings')?.addEventListener('click', closeSettings);
  settingsModal.addEventListener('click', (event) => {
    if (event.target === settingsModal) closeSettings();
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') closeSettings();
  });
}

function setupNotifications() {
  const button = document.getElementById('notificationBtn');
  const panel = document.getElementById('notificationPanel');
  const list = document.getElementById('notificationList');
  const badge = document.getElementById('notificationBadge');
  if (!button || !panel || !list || !badge) return;

  const notifications = [];
  getList(STORAGE_KEYS.cases, []).filter((item) => item.status === 'pending').slice(0, 3).forEach((item) => {
    notifications.push({ title: item.title || 'New case reported', detail: 'Case needs review', view: 'cases' });
  });
  getList(STORAGE_KEYS.tasks, []).filter((item) => !item.complete).slice(0, 3).forEach((item) => {
    notifications.push({ title: item.title || 'Task to complete', detail: 'Assigned task', view: 'tasks' });
  });
  getList('eastCoastAnnouncements', []).slice(0, 2).forEach((item) => {
    notifications.push({ title: item.title || 'School announcement', detail: 'New announcement', view: 'announcements' });
  });

  badge.classList.toggle('active', notifications.length > 0);
  list.innerHTML = notifications.length
    ? notifications.slice(0, 6).map((item) => `<button class="notification-item" type="button" data-notification-view="${escapeSearchText(item.view)}"><strong>${escapeSearchText(item.title)}</strong><small>${escapeSearchText(item.detail)}</small></button>`).join('')
    : '<span class="notification-empty">You have no new notifications.</span>';

  const close = () => {
    panel.classList.remove('open');
    button.setAttribute('aria-expanded', 'false');
  };
  button.addEventListener('click', () => {
    const open = !panel.classList.contains('open');
    panel.classList.toggle('open', open);
    button.setAttribute('aria-expanded', open ? 'true' : 'false');
  });
  list.querySelectorAll('[data-notification-view]').forEach((item) => {
    item.addEventListener('click', () => { loadView(item.dataset.notificationView); close(); });
  });
  document.addEventListener('click', (event) => {
    if (!event.target.closest('.notification-wrap')) close();
  });
}

const ROLE_ACCESS = {
  'Student leader': ['overview', 'cases', 'tasks', 'announcements'],
  'Class monitor': ['overview', 'attendance', 'cases', 'tasks', 'announcements'],
  'Teacher': ['overview', 'cases', 'reports', 'tasks', 'attendance', 'announcements', 'statistics'],
  'Administrator': ['overview', 'cases', 'reports', 'tasks', 'attendance', 'announcements', 'team', 'statistics']
};

function getRole() {
  return document.getElementById('userRoleLabel')?.textContent.trim() || 'Student leader';
}

function canAccessView(view) {
  const allowed = ROLE_ACCESS[getRole()] || ROLE_ACCESS['Student leader'];
  return allowed.includes(view);
}

function getUserDisplayName() {
  const savedName = localStorage.getItem('eastCoastUserName');
  if (savedName && savedName.trim()) return savedName.trim();

  const renderedName = document.getElementById('userDisplayName')?.textContent;
  if (renderedName && renderedName.trim()) return renderedName.trim();

  const email = localStorage.getItem('eastCoastUserEmail') || '';
  if (email) {
    const localPart = email.split('@')[0] || 'User';
    return localPart
      .replace(/[._-]+/g, ' ')
      .replace(/\b\w/g, (char) => char.toUpperCase())
      .trim() || 'User';
  }

  return 'Alex Morgan';
}

function getUserInitials(name) {
  const parts = (name || 'User').trim().split(/\s+/).filter(Boolean);
  if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
}

function getGreeting() {
  const hour = new Date().getHours();

  if (hour >= 5 && hour < 12) return 'Good morning';
  if (hour >= 12 && hour < 15) return 'Good noon';
  if (hour >= 15 && hour < 18) return 'Good afternoon';
  if (hour >= 18 && hour < 22) return 'Good evening';
  return 'Good midnight';
}

function isAdmin() {
  return getRole() === 'Administrator';
}

function isTeacher() {
  return getRole() === 'Teacher' || getRole() === 'Administrator';
}

function formatStatus(status) {
  const map = {
    pending: 'Pending',
    progress: 'In progress',
    resolved: 'Resolved',
    closed: 'Closed',
    urgent: 'Urgent',
    normal: 'Normal'
  };
  return map[status] || status || 'Pending';
}

function emptyState(title, text) {
  return `
    <div class="empty-state-box">
      <strong>${title}</strong>
      <p>${text}</p>
    </div>
  `;
}

function openModal(mode) {
  if (mode === 'case') {
    modalContent.innerHTML = `
      <button class="modal-close" type="button" aria-label="Close dialog">×</button>
      <span class="eyebrow coral">NEW CASE</span>
      <h2>Report an incident</h2>
      <p>Share the details and a teacher will verify it shortly.</p>
      <label>What happened?<textarea id="caseDetail" placeholder="Describe the incident or problem..."></textarea></label>
      <div class="form-row">
        <label>Location<input id="caseLocation" placeholder="e.g. Science block"></label>
        <label>Urgency<select id="caseUrgency"><option value="normal">Normal</option><option value="urgent">Urgent</option></select></label>
      </div>
      <button class="primary-btn full" id="submitCase">Submit case</button>
    `;

    modalContent.querySelector('.modal-close').onclick = closeModalDialog;
    document.getElementById('submitCase').onclick = () => {
      const detail = document.getElementById('caseDetail').value.trim();
      const location = document.getElementById('caseLocation').value.trim();
      const urgency = document.getElementById('caseUrgency').value;
      if (!detail) return;

      const cases = getList(STORAGE_KEYS.cases, []);
      cases.unshift({
        title: detail,
        location: location || 'General campus',
        status: 'pending',
        urgency,
        verified: false,
        reportedBy: getUserDisplayName(),
        createdAt: new Date().toISOString()
      });
      saveList(STORAGE_KEYS.cases, cases);
      modal.classList.remove('open');
      loadView('cases');
    };
  }

  if (mode === 'report') {
    modalContent.innerHTML = `
      <button class="modal-close" type="button" aria-label="Close dialog">×</button>
      <span class="eyebrow coral">NEW REPORT</span>
      <h2>File a daily report</h2>
      <p>Log the observations for the day and keep the team informed.</p>
      <label>Report summary<textarea id="reportSummary" placeholder="Describe the day’s notes or observations..."></textarea></label>
      <div class="form-row">
        <label>Area<input id="reportArea" placeholder="e.g. Morning patrol"></label>
        <label>Status<select id="reportStatus"><option value="pending">Pending</option><option value="resolved">Verified</option></select></label>
      </div>
      <button class="primary-btn full" id="submitReport">Add report</button>
    `;

    modalContent.querySelector('.modal-close').onclick = closeModalDialog;
    document.getElementById('submitReport').onclick = () => {
      const summary = document.getElementById('reportSummary').value.trim();
      const area = document.getElementById('reportArea').value.trim();
      const status = document.getElementById('reportStatus').value;
      if (!summary) return;

      const reports = getList(STORAGE_KEYS.reports, []);
      reports.unshift({ title: summary, area: area || 'Campus overview', status });
      saveList(STORAGE_KEYS.reports, reports);
      modal.classList.remove('open');
      loadView('reports');
    };
  }

  if (mode === 'task') {
    modalContent.innerHTML = `
      <button class="modal-close" type="button" aria-label="Close dialog">×</button>
      <span class="eyebrow coral">NEW TASK</span>
      <h2>Add a task</h2>
      <p>Create a task for the team and assign the next action.</p>
      <label>Task details<textarea id="taskDetail" placeholder="Describe the task..."></textarea></label>
      <div class="form-row">
        <label>Priority<select id="taskPriority"><option value="High">High</option><option value="Normal">Normal</option><option value="Low">Low</option></select></label>
        <label>Due date<input id="taskDue" type="date"></label>
      </div>
      <button class="primary-btn full" id="submitTask">Add task</button>
    `;

    modalContent.querySelector('.modal-close').onclick = closeModalDialog;
    document.getElementById('submitTask').onclick = () => {
      const detail = document.getElementById('taskDetail').value.trim();
      const priority = document.getElementById('taskPriority').value;
      const due = document.getElementById('taskDue').value;
      if (!detail) return;

      const tasks = getList(STORAGE_KEYS.tasks, []);
      tasks.unshift({ title: detail, priority, due: due || '', complete: false });
      saveList(STORAGE_KEYS.tasks, tasks);
      modal.classList.remove('open');
      loadView('tasks');
    };
  }

  if (mode === 'team') {
    modalContent.innerHTML = `
      <button class="modal-close" type="button" aria-label="Close dialog">×</button>
      <span class="eyebrow coral">NEW TEAM MEMBER</span>
      <h2>Add leadership member</h2>
      <p>Only an administrator can add or remove leadership entries.</p>
      <div class="form-row">
        <label>Name<input id="memberName" placeholder="Full name"></label>
        <label>Role<input id="memberRole" placeholder="e.g. Prefect"></label>
      </div>
      <button class="primary-btn full" id="submitMember">Save member</button>
    `;

    modalContent.querySelector('.modal-close').onclick = closeModalDialog;
    document.getElementById('submitMember').onclick = () => {
      const name = document.getElementById('memberName').value.trim();
      const role = document.getElementById('memberRole').value.trim();
      if (!name || !role) return;

      const team = getList(STORAGE_KEYS.leadership, []);
      team.unshift({ name, role });
      saveList(STORAGE_KEYS.leadership, team);
      modal.classList.remove('open');
      loadView('team');
    };
  }

  if (mode === 'announcement') {
    modalContent.innerHTML = `
      <button class="modal-close" type="button" aria-label="Close dialog">×</button>
      <span class="eyebrow coral">NEW ANNOUNCEMENT</span>
      <h2>Share an update</h2>
      <p>Publish a clear message for the school community.</p>
      <label>Title<input id="announcementTitle" placeholder="Announcement title"></label>
      <label>Message<textarea id="announcementMessage" placeholder="Write the update..."></textarea></label>
      <div class="form-row"><label>Audience<select id="announcementAudience"><option>Everyone</option><option>Student leaders</option><option>Teachers</option></select></label></div>
      <button class="primary-btn full" id="submitAnnouncement">Publish announcement</button>
    `;

    modalContent.querySelector('.modal-close').onclick = closeModalDialog;
    document.getElementById('submitAnnouncement').onclick = () => {
      const title = document.getElementById('announcementTitle').value.trim();
      const message = document.getElementById('announcementMessage').value.trim();
      if (!title || !message) return;
      const announcements = getList('eastCoastAnnouncements', []);
      announcements.unshift({ title, message, audience: document.getElementById('announcementAudience').value, author: getUserDisplayName(), date: new Date().toISOString() });
      saveList('eastCoastAnnouncements', announcements);
      closeModalDialog();
      loadView('announcements');
    };
  }

  modal.classList.add('open');
}

function renderOverview() {
  const cases = getList(STORAGE_KEYS.cases, []);
  const reports = getList(STORAGE_KEYS.reports, []);
  const tasks = getList(STORAGE_KEYS.tasks, []);
  const attendance = getList('eastCoastAttendance', []);
  const present = attendance.filter((row) => row.status === 'Present').length;
  const percent = attendance.length ? Math.round((present / attendance.length) * 100) : 0;

  return `
    <div class="stat-grid">
      <div class="stat"><span class="stat-label">Open cases</span><strong>${cases.length}</strong><span class="stat-change up">live</span></div>
      <div class="stat"><span class="stat-label">Tasks due</span><strong>${tasks.filter((task) => !task.complete).length}</strong><span class="stat-change neutral">this week</span></div>
      <div class="stat"><span class="stat-label">Attendance</span><strong>${percent}%</strong><span class="stat-change up">${present}/${attendance.length}</span></div>
      <div class="stat"><span class="stat-label">Reports filed</span><strong>${reports.length}</strong><span class="stat-change up">today</span></div>
    </div>
    <div class="dashboard-grid">
      <div class="panel">
        <div class="panel-head">
          <div><h2>Recent cases</h2><div class="panel-kicker">Keep an eye on your active reports</div></div>
          <button class="text-link" data-view="cases">View all →</button>
        </div>
        <div class="case-list">
          ${cases.length ? cases.slice(0, 3).map((item) => `
            <div class="case-row">
              <div class="case-type">♧</div>
              <div class="case-info"><strong>${item.title}</strong><small>${item.location} · ${formatStatus(item.status)}</small></div>
              <span class="status ${item.urgency === 'urgent' ? 'pending' : 'progress'}">${item.urgency === 'urgent' ? 'Urgent' : 'In progress'}</span>
            </div>
          `).join('') : emptyState('No cases yet', 'Use the new case form to add the first incident.')}
        </div>
      </div>

      <div class="attendance-card">
        <span class="eyebrow">TODAY'S CHECK-IN</span>
        <h2>Attendance snapshot</h2>
        <p>Class attendance for today.</p>
        <div class="attendance-number">${percent}%</div>
        <div class="attendance-foot">${present} present of ${attendance.length} students</div>
      </div>

      <div class="panel">
        <div class="panel-head">
          <div><h2>Daily reports</h2><div class="panel-kicker">Reports filed this week</div></div>
          <button class="text-link" data-view="reports">See reports →</button>
        </div>
        <div class="case-list">
          ${reports.length ? reports.slice(0, 2).map((item) => `
            <div class="case-row">
              <div class="case-type teal">✓</div>
              <div class="case-info"><strong>${item.title}</strong><small>${item.area} · ${formatStatus(item.status)}</small></div>
              <span class="status ${item.status === 'resolved' ? 'resolved' : 'pending'}">${formatStatus(item.status)}</span>
            </div>
          `).join('') : emptyState('No reports yet', 'Add a daily report to keep the school updated.')}
        </div>
      </div>
    </div>
  `;
}

function renderCases() {
  const items = getList(STORAGE_KEYS.cases, []);
  const rows = items.length ? items.map((item, index) => `
    <div class="case-row">
      <div class="case-type">♧</div>
      <div class="case-info"><strong>${item.title}</strong><small>${item.location} · ${item.urgency || 'normal'} urgency · Reported by ${item.reportedBy || 'student leader'}</small></div>
      <div style="display:flex; align-items:center; gap:8px;">
        ${isTeacher() ? `<select class="inline-select" data-case-status="${index}" aria-label="Update case status"><option value="pending" ${item.status === 'pending' ? 'selected' : ''}>Pending</option><option value="progress" ${item.status === 'progress' ? 'selected' : ''}>In progress</option><option value="resolved" ${item.status === 'resolved' ? 'selected' : ''}>Resolved</option><option value="closed" ${item.status === 'closed' ? 'selected' : ''}>Closed</option></select>` : `<span class="status ${item.status === 'resolved' || item.status === 'closed' ? 'resolved' : item.status === 'pending' ? 'pending' : 'progress'}">${formatStatus(item.status)}</span>`}
        ${isTeacher() ? `<span class="tag">${item.verified ? 'Verified' : 'Needs review'}</span>` : ''}
        ${isTeacher() && !item.verified ? `<button class="text-link" data-verify-case="${index}" type="button">Verify</button>` : ''}
        <button class="text-link" data-remove-case="${index}" type="button">Remove</button>
      </div>
    </div>
  `).join('') : emptyState('No cases yet', 'Use the form to add the first case.');

  return `
    <div class="panel">
      <div class="panel-head">
        <div><h2>Case management</h2><div class="panel-kicker">Track, verify and resolve student concerns</div></div>
        <button class="primary-btn" id="addCaseBtn">＋ New case</button>
      </div>
      <div class="case-list">${rows}</div>
    </div>
  `;
}

function renderStatistics() {
  const cases = getList(STORAGE_KEYS.cases, []);
  const reports = getList(STORAGE_KEYS.reports, []);
  const tasks = getList(STORAGE_KEYS.tasks, []);
  const resolvedCases = cases.filter((item) => item.status === 'resolved' || item.status === 'closed').length;
  const verifiedCases = cases.filter((item) => item.verified).length;
  const completedTasks = tasks.filter((item) => item.complete).length;
  const taskRate = tasks.length ? Math.round((completedTasks / tasks.length) * 100) : 0;
  const verificationRate = cases.length ? Math.round((verifiedCases / cases.length) * 100) : 0;

  return `
    <div class="stat-grid">
      <div class="stat"><span class="stat-label">Cases resolved</span><strong>${resolvedCases}</strong><span class="stat-change up">of ${cases.length}</span></div>
      <div class="stat"><span class="stat-label">Reports verified</span><strong>${verificationRate}%</strong><span class="stat-change neutral">teacher review</span></div>
      <div class="stat"><span class="stat-label">Task completion</span><strong>${taskRate}%</strong><span class="stat-change up">${completedTasks}/${tasks.length}</span></div>
      <div class="stat"><span class="stat-label">Reports filed</span><strong>${reports.length}</strong><span class="stat-change neutral">all time</span></div>
    </div>
    <div class="panel">
      <div class="panel-head"><div><h2>Leadership performance</h2><div class="panel-kicker">A quick view of follow-through and teacher verification</div></div></div>
      <div class="case-list">
        <div class="case-row"><div class="case-type teal">✓</div><div class="case-info"><strong>Case resolution</strong><small>${resolvedCases} of ${cases.length} cases resolved or closed</small></div><span class="status resolved">${cases.length ? Math.round((resolvedCases / cases.length) * 100) : 0}%</span></div>
        <div class="case-row"><div class="case-type teal">✓</div><div class="case-info"><strong>Teacher verification</strong><small>${verifiedCases} of ${cases.length} cases verified</small></div><span class="status progress">${verificationRate}%</span></div>
        <div class="case-row"><div class="case-type teal">✓</div><div class="case-info"><strong>Task follow-through</strong><small>${completedTasks} of ${tasks.length} assigned tasks completed</small></div><span class="status progress">${taskRate}%</span></div>
      </div>
    </div>
  `;
}

function renderReports() {
  const items = getList(STORAGE_KEYS.reports, []);
  const rows = items.length ? items.map((item, index) => `
    <div class="case-row">
      <div class="case-type teal">✓</div>
      <div class="case-info"><strong>${item.title}</strong><small>${item.area} · ${formatStatus(item.status)}</small></div>
      <div style="display:flex; align-items:center; gap:8px;">
        <span class="status ${item.status === 'resolved' ? 'resolved' : 'pending'}">${formatStatus(item.status)}</span>
        <button class="text-link" data-remove-report="${index}" type="button">Remove</button>
      </div>
    </div>
  `).join('') : emptyState('No reports yet', 'Add the first report to keep everyone informed.');

  return `
    <div class="panel">
      <div class="panel-head">
        <div><h2>Daily reports</h2><div class="panel-kicker">Share the daily school updates for the team</div></div>
        <button class="primary-btn" id="addReportBtn">＋ Add report</button>
      </div>
      <div class="case-list">${rows}</div>
    </div>
  `;
}

function renderTasks() {
  const items = getList(STORAGE_KEYS.tasks, []);
  const rows = items.length ? items.map((item, index) => `
    <div class="task-row">
      <span class="task-check ${item.complete ? 'done' : ''}">${item.complete ? '✓' : ''}</span>
      <div class="case-info"><strong>${item.title}</strong><small>${item.due ? `Due ${item.due}` : 'No due date'} · ${item.priority || 'Normal'}</small></div>
      <div style="display:flex; align-items:center; gap:8px;">
        <button class="text-link" data-task-toggle="${index}" type="button">${item.complete ? 'Undo' : 'Done'}</button>
        <button class="text-link" data-remove-task="${index}" type="button">Remove</button>
      </div>
    </div>
  `).join('') : emptyState('No tasks yet', 'Create the first task for your team.');

  return `
    <div class="panel">
      <div class="panel-head">
        <div><h2>Assigned tasks</h2><div class="panel-kicker">Small actions that keep the school moving</div></div>
        <button class="primary-btn" id="addTaskBtn">＋ New task</button>
      </div>
      <div class="task-list">${rows}</div>
    </div>
  `;
}

function renderTeam() {
  const items = getList(STORAGE_KEYS.leadership, []);
  const rows = items.length ? items.map((member, index) => `
    <div class="case-row">
      <span class="avatar small">${(member.name || 'A').split(' ').map((part) => part[0]).slice(0, 2).join('').toUpperCase()}</span>
      <div class="case-info"><strong>${member.name}</strong><small>${member.role}</small></div>
      <div style="display:flex; align-items:center; gap:8px;">
        <span class="status resolved">Active</span>
        ${isAdmin() ? `<button class="text-link" data-remove-member="${index}" type="button">Remove</button>` : ''}
      </div>
    </div>
  `).join('') : emptyState('No leadership members yet', 'Only an administrator can add the first member.');

  return `
    <div class="panel">
      <div class="panel-head">
        <div><h2>School leadership team</h2><div class="panel-kicker">Your school leadership community</div></div>
        ${isAdmin() ? '<button class="primary-btn" id="addTeamBtn">＋ Add member</button>' : '<span class="tag">Admin only</span>'}
      </div>
      <div class="case-list">${rows}</div>
    </div>
  `;
}

function bindActions() {
  document.getElementById('addCaseBtn')?.addEventListener('click', () => openModal('case'));
  document.getElementById('addReportBtn')?.addEventListener('click', () => openModal('report'));
  document.getElementById('addTaskBtn')?.addEventListener('click', () => openModal('task'));
  document.getElementById('addTeamBtn')?.addEventListener('click', () => openModal('team'));

  document.querySelectorAll('[data-remove-case]').forEach((button) => {
    button.addEventListener('click', () => {
      const index = Number(button.dataset.removeCase);
      const items = getList(STORAGE_KEYS.cases, []);
      items.splice(index, 1);
      saveList(STORAGE_KEYS.cases, items);
      loadView('cases');
    });
  });

  document.querySelectorAll('[data-verify-case]').forEach((button) => {
    button.addEventListener('click', () => {
      const index = Number(button.dataset.verifyCase);
      const items = getList(STORAGE_KEYS.cases, []);
      if (!items[index] || !isTeacher()) return;
      items[index].verified = true;
      if (items[index].status === 'pending') items[index].status = 'progress';
      saveList(STORAGE_KEYS.cases, items);
      loadView('cases');
    });
  });

  document.querySelectorAll('[data-case-status]').forEach((select) => {
    select.addEventListener('change', () => {
      const index = Number(select.dataset.caseStatus);
      const items = getList(STORAGE_KEYS.cases, []);
      if (!items[index] || !isTeacher()) return;
      items[index].status = select.value;
      saveList(STORAGE_KEYS.cases, items);
      loadView('cases');
    });
  });

  document.querySelectorAll('[data-remove-report]').forEach((button) => {
    button.addEventListener('click', () => {
      const index = Number(button.dataset.removeReport);
      const items = getList(STORAGE_KEYS.reports, []);
      items.splice(index, 1);
      saveList(STORAGE_KEYS.reports, items);
      loadView('reports');
    });
  });

  document.querySelectorAll('[data-remove-task]').forEach((button) => {
    button.addEventListener('click', () => {
      const index = Number(button.dataset.removeTask);
      const items = getList(STORAGE_KEYS.tasks, []);
      items.splice(index, 1);
      saveList(STORAGE_KEYS.tasks, items);
      loadView('tasks');
    });
  });

  document.querySelectorAll('[data-task-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
      const index = Number(button.dataset.taskToggle);
      const items = getList(STORAGE_KEYS.tasks, []);
      if (!items[index]) return;
      items[index].complete = !items[index].complete;
      saveList(STORAGE_KEYS.tasks, items);
      loadView('tasks');
    });
  });

  document.querySelectorAll('[data-remove-member]').forEach((button) => {
    button.addEventListener('click', () => {
      const index = Number(button.dataset.removeMember);
      const items = getList(STORAGE_KEYS.leadership, []);
      items.splice(index, 1);
      saveList(STORAGE_KEYS.leadership, items);
      loadView('team');
    });
  });
}

function loadView(view) {
  if (!canAccessView(view)) {
    viewContainer.innerHTML = emptyState('Access restricted', 'This section is not available for your registered role.');
    pageTitle.textContent = 'Access restricted';
    pageSubtitle.textContent = 'Please use an area that is allowed for your role.';
    document.querySelectorAll('.nav-item').forEach((button) => {
      button.classList.toggle('active', false);
      button.style.display = canAccessView(button.dataset.view) ? 'flex' : 'none';
    });
    return;
  }

  if (view === 'attendance') {
    window.location.href = 'attendance.php';
    return;
  }

  if (view === 'overview') {
    viewContainer.innerHTML = renderOverview();
  } else if (view === 'cases') {
    viewContainer.innerHTML = renderCases();
  } else if (view === 'reports') {
    viewContainer.innerHTML = renderReports();
  } else if (view === 'tasks') {
    viewContainer.innerHTML = renderTasks();
  } else if (view === 'team') {
    viewContainer.innerHTML = renderTeam();
  } else if (view === 'statistics') {
    viewContainer.innerHTML = renderStatistics();
  } else if (view === 'announcements') {
    const canPublish = getRole() === 'Teacher' || getRole() === 'Administrator';
    const announcements = getList('eastCoastAnnouncements', []);
    viewContainer.innerHTML = `
      <div class="panel">
        <div class="panel-head">
          <div><h2>School announcements</h2><div class="panel-kicker">Stay up to date with your community</div></div>
          ${canPublish ? '<button class="primary-btn" id="createAnnouncement">＋ New announcement</button>' : '<span class="tag">Read only</span>'}
        </div>
        <div class="case-list">${announcements.length ? announcements.map((item) => `<div class="case-row"><div class="case-type">♢</div><div class="case-info"><strong>${item.title}</strong><small>${item.message} · ${item.author || 'School team'} · ${item.audience || 'Everyone'}</small></div></div>`).join('') : emptyState('No announcements yet', canPublish ? 'Publish the first update for students.' : 'Announcements from your school team will appear here.')}</div>
      </div>
    `;
  } else {
    viewContainer.innerHTML = renderOverview();
  }

  document.querySelectorAll('.nav-item').forEach((button) => {
    const allowed = canAccessView(button.dataset.view);
    button.style.display = allowed ? 'flex' : 'none';
    button.classList.toggle('active', button.dataset.view === view);
  });

  const userName = getUserDisplayName();
  const labels = {
    overview: [`${getGreeting()}, ${userName} ✦`, 'Here is what is happening across your school community today.'],
    cases: ['Cases', 'Track concerns from report to resolution.'],
    reports: ['Daily reports', 'Keep your teacher team informed with a clear daily pulse.'],
    tasks: ['Your tasks', 'Small actions that keep the school moving.'],
    announcements: ['Announcements', 'The latest updates from your school community.'],
    team: ['School leadership team', 'Your school leadership community at a glance.'],
    statistics: ['Performance', 'Review case resolution, verification and task follow-through.']
  };

  const selected = labels[view] || labels.overview;
  pageTitle.textContent = selected[0];
  pageSubtitle.textContent = selected[1];
  const profileName = document.getElementById('userDisplayName');
  const profileRole = document.getElementById('userRoleLabel');
  const profileInitials = document.getElementById('profileInitials');
  if (profileName) profileName.textContent = userName;
  if (profileRole) profileRole.textContent = getRole();
  if (profileInitials) profileInitials.textContent = getUserInitials(userName);

  document.querySelectorAll('[data-view]').forEach((button) => {
    button.onclick = () => loadView(button.dataset.view);
  });

  document.getElementById('createAnnouncement')?.addEventListener('click', () => openModal('announcement'));
  bindActions();
}

function updateClock() {
  const now = new Date();
  const timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone || 'Local time';
  const timeText = new Intl.DateTimeFormat(undefined, { hour: 'numeric', minute: '2-digit', second: '2-digit', hour12: true }).format(now);
  const dateText = new Intl.DateTimeFormat(undefined, { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }).format(now);

  const timeEl = document.getElementById('localTime');
  const zoneEl = document.getElementById('timezoneLabel');
  const breadcrumb = document.querySelector('.breadcrumb');
  const eyebrow = document.querySelector('.page-heading .eyebrow');

  if (timeEl) timeEl.textContent = timeText;
  if (zoneEl) zoneEl.textContent = timeZone;
  if (breadcrumb) breadcrumb.innerHTML = `${dateText} <span>/</span> <strong>Overview</strong>`;
  if (eyebrow) eyebrow.textContent = dateText.toUpperCase();
}

loadView('overview');
setupGlobalSearch();
setupSettings();
setupNotifications();
updateClock();
setInterval(updateClock, 1000);

document.getElementById('reportBtn').onclick = () => {
  if (canAccessView('cases')) {
    openModal('case');
  }
};
const mobileMenuButton = document.getElementById('mobileMenuBtn');
const sidebar = document.querySelector('.sidebar');
const setSidebarOpen = (isOpen) => {
  sidebar?.classList.toggle('open', isOpen);
  mobileMenuButton?.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  mobileMenuButton?.setAttribute('aria-label', isOpen ? 'Hide navigation' : 'Show navigation');
};

mobileMenuButton?.addEventListener('click', () => setSidebarOpen(!sidebar?.classList.contains('open')));
document.addEventListener('click', (event) => {
  if (sidebar?.classList.contains('open') && !event.target.closest('.sidebar, #mobileMenuBtn')) setSidebarOpen(false);
});
document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape') setSidebarOpen(false);
});
document.querySelectorAll('.nav-item').forEach((button) => {
  button.addEventListener('click', () => setSidebarOpen(false));
});

const roleSelectContainer = document.querySelector('.role-select');
if (roleSelectContainer) {
  const role = getRole();
  roleSelectContainer.style.color = role === 'Administrator' ? '#e9674f' : role === 'Teacher' ? '#d69a3c' : '#2f8e83';
}

document.getElementById('logoutBtn')?.addEventListener('click', () => {
  localStorage.removeItem('eastCoastLoggedIn');
  localStorage.removeItem('eastCoastRole');
  localStorage.removeItem('eastCoastUserName');
  localStorage.removeItem('eastCoastUserEmail');
  window.location.href = 'logout.php';
});
