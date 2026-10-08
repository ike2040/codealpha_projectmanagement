<?php
require_once __DIR__ . '/includes/auth.php';
requireAuth();

$page_title = 'My Tasks — ProjectHub';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$current_user_id = getCurrentUserId();

// Filter
$status_filter = sanitize($_GET['status'] ?? '');
$priority_filter = sanitize($_GET['priority'] ?? '');

$query = "
    SELECT t.*, 
           p.name as project_name,
           p.id as project_id,
           u.full_name as created_by_name
    FROM tasks t
    JOIN projects p ON t.project_id = p.id
    JOIN users u ON t.created_by = u.id
    WHERE t.assigned_to = ?
";
$params = [$current_user_id];

if ($status_filter) {
    $query .= ' AND t.status = ?';
    $params[] = $status_filter;
}
if ($priority_filter) {
    $query .= ' AND t.priority = ?';
    $params[] = $priority_filter;
}

$query .= " ORDER BY FIELD(t.status,'Todo','In Progress','Review','Done'), FIELD(t.priority,'Urgent','High','Medium','Low'), t.due_date ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$my_tasks = $stmt->fetchAll();

// Count by status
$stmt2 = $pdo->prepare("SELECT status, COUNT(*) as count FROM tasks WHERE assigned_to = ? GROUP BY status");
$stmt2->execute([$current_user_id]);
$status_counts = [];
foreach ($stmt2->fetchAll() as $row) {
    $status_counts[$row['status']] = $row['count'];
}
?>

<main class="main-content">
    <div class="container">
        <div class="page-header">
            <div class="page-title">
                <h1>My Tasks</h1>
                <p>All tasks assigned to you across all projects.</p>
            </div>
        </div>

        <!-- Status Summary -->
        <div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:1.5rem">
            <?php
            $statuses = [
                'Todo'        => ['icon'=>'📋','class'=>'stat-icon-blue'],
                'In Progress' => ['icon'=>'⚙️','class'=>'stat-icon-orange'],
                'Review'      => ['icon'=>'🔍','class'=>'stat-icon-purple'],
                'Done'        => ['icon'=>'✅','class'=>'stat-icon-teal'],
            ];
            foreach ($statuses as $s => $meta):
            ?>
            <div class="stat-card">
                <div class="stat-icon <?php echo $meta['class']; ?>"><?php echo $meta['icon']; ?></div>
                <div class="stat-content">
                    <h3><?php echo $status_counts[$s] ?? 0; ?></h3>
                    <p><?php echo $s; ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Filters -->
        <div class="filter-bar">
            <form method="GET" class="filter-form">
                <div class="form-group">
                    <select name="status">
                        <option value="">All Statuses</option>
                        <?php foreach(['Todo','In Progress','Review','Done'] as $s): ?>
                        <option value="<?php echo $s; ?>" <?php echo $status_filter===$s?'selected':''; ?>><?php echo $s; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <select name="priority">
                        <option value="">All Priorities</option>
                        <?php foreach(['Urgent','High','Medium','Low'] as $p): ?>
                        <option value="<?php echo $p; ?>" <?php echo $priority_filter===$p?'selected':''; ?>><?php echo $p; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-secondary">Filter</button>
                <a href="my-tasks.php" class="btn btn-secondary">Clear</a>
            </form>
        </div>

        <!-- Tasks Table -->
        <?php if (empty($my_tasks)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">📋</div>
            <p>No tasks assigned to you yet.</p>
        </div>
        <?php else: ?>
        <div class="dashboard-section">
            <table class="tasks-table">
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>Project</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Due Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($my_tasks as $task):
                        $is_overdue = $task['due_date'] && $task['status'] !== 'Done' && strtotime($task['due_date']) < strtotime(date('Y-m-d'));
                        $priority_class = 'priority-' . strtolower($task['priority']);
                    ?>
                    <tr class="<?php echo $is_overdue ? 'overdue-row' : ''; ?>">
                        <td>
                            <strong><?php echo htmlspecialchars($task['title']); ?></strong>
                            <?php if ($task['description']): ?>
                            <p style="font-size:.75rem;color:var(--text-muted);margin-top:.2rem"><?php echo htmlspecialchars(substr($task['description'],0,80)); ?><?php echo strlen($task['description'])>80?'...':''; ?></p>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="project.php?id=<?php echo $task['project_id']; ?>"><?php echo htmlspecialchars($task['project_name']); ?></a>
                        </td>
                        <td><span class="task-priority <?php echo $priority_class; ?>"><?php echo htmlspecialchars($task['priority']); ?></span></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower(str_replace(' ','-',$task['status'])); ?>">
                                <?php echo htmlspecialchars($task['status']); ?>
                            </span>
                        </td>
                        <td class="<?php echo $is_overdue ? 'text-danger' : ''; ?>">
                            <?php echo $task['due_date'] ? '📅 ' . formatDate($task['due_date']) : '<span style="color:var(--text-muted)">—</span>'; ?>
                            <?php if ($is_overdue): ?><span style="font-size:.7rem;color:var(--danger);font-weight:700"> Overdue</span><?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-secondary" onclick="openTaskModal(<?php echo $task['id']; ?>)">
                                View
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</main>

<!-- Task Modal -->
<div id="taskModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTaskTitle">Task Details</h2>
            <button class="modal-close" onclick="closeTaskModal()">&times;</button>
        </div>
        <div class="modal-body" id="modalTaskBody">
            <!-- Task details will be loaded here -->
        </div>
    </div>
</div>

<style>
.tasks-table { width: 100%; border-collapse: collapse; }
.tasks-table th { text-align: left; padding: .6rem .8rem; font-size: .75rem; text-transform: uppercase; letter-spacing: .06em; color: var(--text-muted); border-bottom: 2px solid var(--border); }
.tasks-table td { padding: .75rem .8rem; font-size: .875rem; border-bottom: 1px solid var(--border); vertical-align: top; }
.tasks-table tr:hover td { background: var(--primary-subtle); }
.overdue-row td:first-child { border-left: 3px solid var(--danger); }
.text-danger { color: var(--danger); }
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
