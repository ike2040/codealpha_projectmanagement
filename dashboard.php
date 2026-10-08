<?php
require_once __DIR__ . '/includes/auth.php';
requireAuth();

$page_title = 'Dashboard — ProjectHub';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$current_user_id = getCurrentUserId();

// Statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM projects p JOIN project_members pm ON p.id = pm.project_id WHERE pm.user_id = ?");
$stmt->execute([$current_user_id]);
$total_projects = $stmt->fetch()['count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM projects WHERE owner_id = ?");
$stmt->execute([$current_user_id]);
$owned_projects = $stmt->fetch()['count'];

$joined_projects = $total_projects - $owned_projects;

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tasks WHERE assigned_to = ?");
$stmt->execute([$current_user_id]);
$total_tasks = $stmt->fetch()['count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tasks WHERE assigned_to = ? AND status IN ('Todo', 'In Progress', 'Review')");
$stmt->execute([$current_user_id]);
$pending_tasks = $stmt->fetch()['count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tasks WHERE assigned_to = ? AND status = 'Done'");
$stmt->execute([$current_user_id]);
$completed_tasks = $stmt->fetch()['count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tasks WHERE assigned_to = ? AND status != 'Done' AND due_date < CURDATE()");
$stmt->execute([$current_user_id]);
$overdue_tasks = $stmt->fetch()['count'];

// Recent activity
$stmt = $pdo->prepare("
    SELECT al.*, u.full_name, u.username, p.name as project_name, t.title as task_title
    FROM activity_logs al
    JOIN users u ON al.user_id = u.id
    LEFT JOIN projects p ON al.project_id = p.id
    LEFT JOIN tasks t ON al.task_id = t.id
    WHERE al.project_id IN (SELECT project_id FROM project_members WHERE user_id = ?)
    ORDER BY al.created_at DESC
    LIMIT 10
");
$stmt->execute([$current_user_id]);
$recent_activity = $stmt->fetchAll();

// Recent projects
$stmt = $pdo->prepare("
    SELECT p.*, COUNT(t.id) as task_count,
           SUM(CASE WHEN t.status = 'Done' THEN 1 ELSE 0 END) as done_count
    FROM projects p
    JOIN project_members pm ON p.id = pm.project_id
    LEFT JOIN tasks t ON p.id = t.project_id
    WHERE pm.user_id = ?
    GROUP BY p.id
    ORDER BY p.updated_at DESC
    LIMIT 4
");
$stmt->execute([$current_user_id]);
$recent_projects = $stmt->fetchAll();
?>

<main class="main-content">
    <div class="container">

        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title">
                <h1>Dashboard</h1>
                <p>Welcome back, <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong> 👋 Here's what's happening.</p>
            </div>
            <div class="page-actions">
                <a href="create-project.php" class="btn btn-primary" id="btn-create-project">
                    ➕ New Project
                </a>
            </div>
        </div>

        <!-- Flash message -->
        <?php $flash = getFlashMessage(); ?>
        <?php if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>" role="alert">
                <p><?php echo htmlspecialchars($flash['message']); ?></p>
            </div>
        <?php endif; ?>

        <!-- ── Statistics Grid ── -->
        <div class="stats-grid">
            <div class="stat-card" style="animation-delay:.05s">
                <div class="stat-icon stat-icon-blue">📁</div>
                <div class="stat-content">
                    <h3><?php echo $total_projects; ?></h3>
                    <p>Total Projects</p>
                </div>
            </div>
            <div class="stat-card" style="animation-delay:.10s">
                <div class="stat-icon stat-icon-green">👑</div>
                <div class="stat-content">
                    <h3><?php echo $owned_projects; ?></h3>
                    <p>Projects Owned</p>
                </div>
            </div>
            <div class="stat-card" style="animation-delay:.15s">
                <div class="stat-icon stat-icon-purple">👥</div>
                <div class="stat-content">
                    <h3><?php echo $joined_projects; ?></h3>
                    <p>Projects Joined</p>
                </div>
            </div>
            <div class="stat-card" style="animation-delay:.20s">
                <div class="stat-icon stat-icon-orange">📋</div>
                <div class="stat-content">
                    <h3><?php echo $total_tasks; ?></h3>
                    <p>Tasks Assigned</p>
                </div>
            </div>
            <div class="stat-card" style="animation-delay:.25s">
                <div class="stat-icon stat-icon-yellow">⏳</div>
                <div class="stat-content">
                    <h3><?php echo $pending_tasks; ?></h3>
                    <p>Pending Tasks</p>
                </div>
            </div>
            <div class="stat-card" style="animation-delay:.30s">
                <div class="stat-icon stat-icon-teal">✅</div>
                <div class="stat-content">
                    <h3><?php echo $completed_tasks; ?></h3>
                    <p>Completed Tasks</p>
                </div>
            </div>
            <div class="stat-card" style="animation-delay:.35s">
                <div class="stat-icon stat-icon-red">⚠️</div>
                <div class="stat-content">
                    <h3><?php echo $overdue_tasks; ?></h3>
                    <p>Overdue Tasks</p>
                </div>
            </div>
        </div>

        <!-- ── Two-column layout ── -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem;" class="dashboard-two-col">

            <!-- Recent Projects -->
            <div class="dashboard-section" style="animation-delay:.2s">
                <div class="section-header">
                    <h2>Recent Projects</h2>
                    <a href="projects.php" class="btn btn-secondary btn-sm">View All</a>
                </div>
                <?php if (empty($recent_projects)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📁</div>
                        <p>No projects yet. <a href="create-project.php">Create one!</a></p>
                    </div>
                <?php else: ?>
                    <div style="display:flex;flex-direction:column;gap:.75rem;">
                        <?php foreach ($recent_projects as $proj):
                            $progress = $proj['task_count'] > 0
                                ? round(($proj['done_count'] / $proj['task_count']) * 100)
                                : 0;
                            $status_class = 'status-' . strtolower(str_replace(' ', '-', $proj['status']));
                        ?>
                        <a href="project.php?id=<?php echo $proj['id']; ?>"
                           style="display:flex;flex-direction:column;gap:.4rem;padding:.85rem 1rem;background:var(--bg);border-radius:var(--r-md);border:1.5px solid var(--border);text-decoration:none;color:inherit;transition:all .2s;"
                           onmouseover="this.style.borderColor='var(--primary)';this.style.background='var(--primary-subtle)'"
                           onmouseout="this.style.borderColor='var(--border)';this.style.background='var(--bg)'">
                            <div style="display:flex;justify-content:space-between;align-items:center;">
                                <span style="font-weight:600;font-size:.9rem;"><?php echo htmlspecialchars($proj['name']); ?></span>
                                <span class="project-status <?php echo $status_class; ?>"><?php echo htmlspecialchars($proj['status']); ?></span>
                            </div>
                            <div class="progress-bar" style="margin:0">
                                <div class="progress-fill" style="width:<?php echo $progress; ?>%"></div>
                            </div>
                            <span style="font-size:.75rem;color:var(--text-muted)"><?php echo $progress; ?>% complete · <?php echo $proj['task_count']; ?> tasks</span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Recent Activity -->
            <div class="dashboard-section" style="animation-delay:.25s">
                <div class="section-header">
                    <h2>Recent Activity</h2>
                </div>
                <div class="activity-list">
                    <?php if (empty($recent_activity)): ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">📭</div>
                            <p>No recent activity yet.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_activity as $activity): ?>
                            <div class="activity-item">
                                <div class="activity-avatar">
                                    <img src="<?php echo getProfilePictureUrl(['profile_picture' => null]); ?>"
                                         alt="<?php echo htmlspecialchars($activity['full_name']); ?>">
                                </div>
                                <div class="activity-content">
                                    <p class="activity-text">
                                        <strong><?php echo htmlspecialchars($activity['full_name']); ?></strong>
                                        <?php echo htmlspecialchars($activity['description']); ?>
                                    </p>
                                    <p class="activity-time">
                                        🕐 <?php echo formatDateTime($activity['created_at']); ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div><!-- /.dashboard-two-col -->

        <!-- ── Quick Actions ── -->
        <div class="dashboard-section" style="animation-delay:.3s">
            <div class="section-header">
                <h2>Quick Actions</h2>
            </div>
            <div class="quick-actions">
                <a href="create-project.php" class="btn btn-primary" id="quick-create-project">
                    ➕ Create New Project
                </a>
                <a href="projects.php" class="btn btn-secondary" id="quick-view-projects">
                    📁 View All Projects
                </a>
                <a href="notifications.php" class="btn btn-secondary" id="quick-view-notifications">
                    🔔 View Notifications
                </a>
            </div>
        </div>

    </div><!-- /.container -->
</main>

<!-- Responsive two-col fix -->
<style>
@media (max-width: 900px) {
    .dashboard-two-col { grid-template-columns: 1fr !important; }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
