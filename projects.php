<?php
require_once __DIR__ . '/includes/auth.php';
requireAuth();

$page_title = 'My Projects';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$current_user_id = getCurrentUserId();

// Get search and filter parameters
$search = sanitize($_GET['search'] ?? '');
$status_filter = sanitize($_GET['status'] ?? '');

// Build query
$query = "
    SELECT p.*, 
           u.full_name as owner_name,
           pm.role as user_role,
           (SELECT COUNT(*) FROM project_members WHERE project_id = p.id) as member_count,
           (SELECT COUNT(*) FROM tasks WHERE project_id = p.id) as task_count,
           (SELECT COUNT(*) FROM tasks WHERE project_id = p.id AND status = 'Done') as completed_tasks
    FROM projects p
    JOIN project_members pm ON p.id = pm.project_id
    JOIN users u ON p.owner_id = u.id
    WHERE pm.user_id = ?
";

$params = [$current_user_id];

if ($search) {
    $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status_filter) {
    $query .= " AND p.status = ?";
    $params[] = $status_filter;
}

$query .= " ORDER BY p.updated_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$projects = $stmt->fetchAll();
?>

<main class="main-content">
    <div class="container">
        <div class="page-header">
            <h1>My Projects</h1>
            <a href="create-project.php" class="btn btn-primary">➕ Create Project</a>
        </div>

        <?php $flash = getFlashMessage(); ?>
        <?php if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <p><?php echo htmlspecialchars($flash['message']); ?></p>
            </div>
        <?php endif; ?>

        <!-- Search and Filter -->
        <div class="filter-bar">
            <form method="GET" class="filter-form">
                <div class="form-group">
                    <input type="text" name="search" placeholder="Search projects..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="form-group">
                    <select name="status">
                        <option value="">All Statuses</option>
                        <option value="Planning" <?php echo $status_filter === 'Planning' ? 'selected' : ''; ?>>Planning</option>
                        <option value="In Progress" <?php echo $status_filter === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="On Hold" <?php echo $status_filter === 'On Hold' ? 'selected' : ''; ?>>On Hold</option>
                        <option value="Completed" <?php echo $status_filter === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-secondary">Filter</button>
                <a href="projects.php" class="btn btn-secondary">Clear</a>
            </form>
        </div>

        <!-- Projects Grid -->
        <div class="projects-grid">
            <?php if (empty($projects)): ?>
                <div class="empty-state">
                    <p>No projects found. <a href="create-project.php">Create your first project</a></p>
                </div>
            <?php else: ?>
                <?php foreach ($projects as $project): ?>
                    <?php 
                    $progress = $project['task_count'] > 0 ? round(($project['completed_tasks'] / $project['task_count']) * 100) : 0;
                    $status_class = strtolower(str_replace(' ', '-', $project['status']));
                    ?>
                    <div class="project-card">
                        <div class="project-header">
                            <h3><?php echo htmlspecialchars($project['name']); ?></h3>
                            <span class="project-status status-<?php echo $status_class; ?>"><?php echo htmlspecialchars($project['status']); ?></span>
                        </div>
                        <p class="project-description"><?php echo htmlspecialchars(substr($project['description'] ?? '', 0, 150)) . (strlen($project['description'] ?? '') > 150 ? '...' : ''); ?></p>
                        
                        <div class="project-meta">
                            <div class="meta-item">
                                <span>👤</span>
                                <span><?php echo htmlspecialchars($project['owner_name']); ?></span>
                            </div>
                            <div class="meta-item">
                                <span>👥</span>
                                <span><?php echo $project['member_count']; ?> members</span>
                            </div>
                            <div class="meta-item">
                                <span>📋</span>
                                <span><?php echo $project['task_count']; ?> tasks</span>
                            </div>
                        </div>

                        <div class="project-progress">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $progress; ?>%;"></div>
                            </div>
                            <span class="progress-text"><?php echo $progress; ?>% complete</span>
                        </div>

                        <div class="project-footer">
                            <div class="due-date">
                                <?php if ($project['due_date']): ?>
                                    <span>📅 Due: <?php echo formatDate($project['due_date']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="project-actions">
                                <a href="project.php?id=<?php echo $project['id']; ?>" class="btn btn-sm btn-primary">View Board</a>
                                <?php if (canManageProject($project['id'])): ?>
                                    <a href="edit-project.php?id=<?php echo $project['id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
