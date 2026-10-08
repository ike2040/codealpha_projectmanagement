<?php
require_once __DIR__ . '/includes/auth.php';
requireAuth();

$project_id = $_GET['id'] ?? 0;

// Check if user is a project member
if (!isProjectMember($project_id)) {
    redirect('projects.php', 'You do not have access to this project.', 'error');
}

// Get project details
$stmt = $pdo->prepare("SELECT p.*, u.full_name as owner_name FROM projects p JOIN users u ON p.owner_id = u.id WHERE p.id = ?");
$stmt->execute([$project_id]);
$project = $stmt->fetch();

if (!$project) {
    redirect('projects.php', 'Project not found.', 'error');
}

$page_title = $project['name'] . ' - Board';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

// Get project members
$members = getProjectMembers($project_id);

// Get tasks for each column
$statuses = ['Todo', 'In Progress', 'Review', 'Done'];
$tasks_by_status = [];

foreach ($statuses as $status) {
    $stmt = $pdo->prepare("
        SELECT t.*, 
               u1.full_name as created_by_name,
               u2.full_name as assigned_to_name,
               u2.profile_picture as assigned_to_picture
        FROM tasks t
        LEFT JOIN users u1 ON t.created_by = u1.id
        LEFT JOIN users u2 ON t.assigned_to = u2.id
        WHERE t.project_id = ? AND t.status = ?
        ORDER BY t.priority DESC, t.created_at DESC
    ");
    $stmt->execute([$project_id, $status]);
    $tasks_by_status[$status] = $stmt->fetchAll();
}

// Get search and filter parameters
$search = sanitize($_GET['search'] ?? '');
$priority_filter = sanitize($_GET['priority'] ?? '');
$assigned_filter = sanitize($_GET['assigned'] ?? '');

// Filter tasks if filters are applied
if ($search || $priority_filter || $assigned_filter) {
    foreach ($statuses as $status) {
        $filtered_tasks = [];
        foreach ($tasks_by_status[$status] as $task) {
            $match = true;
            
            if ($search && !stripos($task['title'], $search) && !stripos($task['description'] ?? '', $search)) {
                $match = false;
            }
            
            if ($priority_filter && $task['priority'] !== $priority_filter) {
                $match = false;
            }
            
            if ($assigned_filter && $task['assigned_to'] != $assigned_filter) {
                $match = false;
            }
            
            if ($match) {
                $filtered_tasks[] = $task;
            }
        }
        $tasks_by_status[$status] = $filtered_tasks;
    }
}

$can_manage = canManageProject($project_id);
$user_role = getProjectRole($project_id);
?>

<main class="main-content">
    <div class="container">
        <div class="page-header">
            <div class="page-title">
                <h1><?php echo htmlspecialchars($project['name']); ?></h1>
                <p><?php echo htmlspecialchars($project['description']); ?></p>
            </div>
            <div class="page-actions">
                <?php if ($can_manage): ?>
                    <a href="edit-project.php?id=<?php echo $project_id; ?>" class="btn btn-secondary">Edit Project</a>
                <?php endif; ?>
                <a href="projects.php" class="btn btn-secondary">← Back</a>
            </div>
        </div>

        <?php $flash = getFlashMessage(); ?>
        <?php if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <p><?php echo htmlspecialchars($flash['message']); ?></p>
            </div>
        <?php endif; ?>

        <!-- Project Info Bar -->
        <div class="project-info-bar">
            <div class="info-item">
                <span>👤 Owner:</span>
                <span><?php echo htmlspecialchars($project['owner_name']); ?></span>
            </div>
            <div class="info-item">
                <span>👥 Members:</span>
                <span><?php echo count($members); ?></span>
            </div>
            <?php if ($project['start_date']): ?>
                <div class="info-item">
                    <span>📅 Start:</span>
                    <span><?php echo formatDate($project['start_date']); ?></span>
                </div>
            <?php endif; ?>
            <?php if ($project['due_date']): ?>
                <div class="info-item">
                    <span>📅 Due:</span>
                    <span><?php echo formatDate($project['due_date']); ?></span>
                </div>
            <?php endif; ?>
            <div class="info-item">
                <span>📊 Status:</span>
                <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $project['status'])); ?>"><?php echo htmlspecialchars($project['status']); ?></span>
            </div>
            <div class="info-item">
                <span>📈 Progress:</span>
                <span><?php echo calculateProjectProgress($project_id); ?>%</span>
            </div>
        </div>

        <!-- Task Filters -->
        <div class="task-filters">
            <form method="GET" class="filter-form-inline">
                <input type="hidden" name="id" value="<?php echo $project_id; ?>">
                <input type="text" name="search" placeholder="Search tasks..." value="<?php echo htmlspecialchars($search); ?>">
                <select name="priority">
                    <option value="">All Priorities</option>
                    <option value="Low" <?php echo $priority_filter === 'Low' ? 'selected' : ''; ?>>Low</option>
                    <option value="Medium" <?php echo $priority_filter === 'Medium' ? 'selected' : ''; ?>>Medium</option>
                    <option value="High" <?php echo $priority_filter === 'High' ? 'selected' : ''; ?>>High</option>
                    <option value="Urgent" <?php echo $priority_filter === 'Urgent' ? 'selected' : ''; ?>>Urgent</option>
                </select>
                <select name="assigned">
                    <option value="">All Members</option>
                    <?php foreach ($members as $member): ?>
                        <option value="<?php echo $member['id']; ?>" <?php echo $assigned_filter == $member['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($member['full_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-sm btn-secondary">Filter</button>
                <a href="project.php?id=<?php echo $project_id; ?>" class="btn btn-sm btn-secondary">Clear</a>
            </form>
            <button class="btn btn-primary" onclick="openCreateTaskModal()">➕ Add Task</button>
        </div>

        <!-- Kanban Board -->
        <div class="kanban-board" data-project-id="<?php echo $project_id; ?>">
            <?php foreach ($statuses as $status): ?>
                <?php 
                $status_class = strtolower(str_replace(' ', '-', $status));
                $task_count = count($tasks_by_status[$status]);
                ?>
                <div class="kanban-column" data-status="<?php echo $status; ?>">
                    <div class="column-header">
                        <h3><?php echo $status; ?></h3>
                        <span class="task-count"><?php echo $task_count; ?></span>
                    </div>
                    <div class="column-tasks" id="column-<?php echo $status_class; ?>">
                        <?php foreach ($tasks_by_status[$status] as $task): ?>
                            <?php 
                            $priority_class = strtolower($task['priority']);
                            $is_overdue = $task['due_date'] && $task['status'] !== 'Done' && strtotime($task['due_date']) < strtotime(date('Y-m-d'));
                            ?>
                            <div class="task-card" 
                                 data-task-id="<?php echo $task['id']; ?>" 
                                 draggable="true"
                                 onclick="openTaskModal(<?php echo $task['id']; ?>)">
                                <div class="task-header">
                                    <span class="task-priority priority-<?php echo $priority_class; ?>"><?php echo htmlspecialchars($task['priority']); ?></span>
                                    <?php if ($is_overdue): ?>
                                        <span class="task-overdue">⚠️ Overdue</span>
                                    <?php endif; ?>
                                </div>
                                <h4 class="task-title"><?php echo htmlspecialchars($task['title']); ?></h4>
                                <p class="task-description"><?php echo htmlspecialchars(substr($task['description'] ?? '', 0, 100)) . (strlen($task['description'] ?? '') > 100 ? '...' : ''); ?></p>
                                
                                <?php if ($task['assigned_to']): ?>
                                    <div class="task-assignee">
                                        <img src="<?php echo getProfilePictureUrl(['profile_picture' => $task['assigned_to_picture']]); ?>" alt="<?php echo htmlspecialchars($task['assigned_to_name']); ?>">
                                        <span><?php echo htmlspecialchars($task['assigned_to_name']); ?></span>
                                    </div>
                                <?php endif; ?>
                                <div class="task-footer">
                                    <?php if ($task['due_date']): ?>
                                        <span class="task-due-date <?php echo $is_overdue ? 'overdue' : ''; ?>">
                                            📅 <?php echo formatDate($task['due_date']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
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

<!-- Create Task Modal -->
<div id="createTaskModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Create New Task</h2>
            <button class="modal-close" onclick="closeCreateTaskModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="createTaskForm" class="task-form">
                <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
                <div class="form-group">
                    <label for="task_title">Title *</label>
                    <input type="text" id="task_title" name="title" required>
                </div>
                <div class="form-group">
                    <label for="task_description">Description</label>
                    <textarea id="task_description" name="description" rows="3"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="task_priority">Priority</label>
                        <select id="task_priority" name="priority">
                            <option value="Low">Low</option>
                            <option value="Medium" selected>Medium</option>
                            <option value="High">High</option>
                            <option value="Urgent">Urgent</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="task_status">Status</label>
                        <select id="task_status" name="status">
                            <option value="Todo" selected>Todo</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Review">Review</option>
                            <option value="Done">Done</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="task_assigned_to">Assign To</label>
                        <select id="task_assigned_to" name="assigned_to">
                            <option value="">Unassigned</option>
                            <?php foreach ($members as $member): ?>
                                <option value="<?php echo $member['id']; ?>"><?php echo htmlspecialchars($member['full_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="task_due_date">Due Date</label>
                        <input type="date" id="task_due_date" name="due_date">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Create Task</button>
                    <button type="button" class="btn btn-secondary" onclick="closeCreateTaskModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
