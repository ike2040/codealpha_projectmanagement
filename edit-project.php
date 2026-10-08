<?php
require_once __DIR__ . '/includes/auth.php';
requireAuth();

$project_id = $_GET['id'] ?? 0;

// Check if user can manage project
if (!canManageProject($project_id)) {
    redirect('projects.php', 'You do not have permission to edit this project.', 'error');
}

// Get project details
$stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
$stmt->execute([$project_id]);
$project = $stmt->fetch();

if (!$project) {
    redirect('projects.php', 'Project not found.', 'error');
}

$page_title = 'Edit Project';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $start_date = $_POST['start_date'] ?? '';
    $due_date = $_POST['due_date'] ?? '';
    $status = sanitize($_POST['status'] ?? 'Planning');
    
    // Validation
    if (empty($name)) {
        $errors[] = 'Project name is required.';
    }
    
    if (empty($description)) {
        $errors[] = 'Description is required.';
    }
    
    if ($start_date && $due_date && strtotime($start_date) > strtotime($due_date)) {
        $errors[] = 'Start date cannot be after due date.';
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE projects SET name = ?, description = ?, start_date = ?, due_date = ?, status = ? WHERE id = ?");
            $stmt->execute([$name, $description, $start_date ?: null, $due_date ?: null, $status, $project_id]);
            
            logActivity($project_id, null, getCurrentUserId(), 'updated', 'updated the project details');
            
            redirect('project.php?id=' . $project_id, 'Project updated successfully!', 'success');
        } catch (Exception $e) {
            $errors[] = 'Failed to update project. Please try again.';
        }
    }
}
?>

<main class="main-content">
    <div class="container">
        <div class="page-header">
            <h1>Edit Project</h1>
            <a href="project.php?id=<?php echo $project_id; ?>" class="btn btn-secondary">← Back to Project</a>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" class="project-form">
                <div class="form-group">
                    <label for="name">Project Name *</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($project['name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" rows="4" required><?php echo htmlspecialchars($project['description']); ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="start_date">Start Date</label>
                        <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($project['start_date']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="due_date">Due Date</label>
                        <input type="date" id="due_date" name="due_date" value="<?php echo htmlspecialchars($project['due_date']); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="Planning" <?php echo $project['status'] === 'Planning' ? 'selected' : ''; ?>>Planning</option>
                        <option value="In Progress" <?php echo $project['status'] === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="On Hold" <?php echo $project['status'] === 'On Hold' ? 'selected' : ''; ?>>On Hold</option>
                        <option value="Completed" <?php echo $project['status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Project</button>
                    <a href="project.php?id=<?php echo $project_id; ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>

        <?php if (isProjectOwner($project_id)): ?>
        <div style="margin-top:2rem;padding-top:1.5rem;border-top:1px solid var(--border);">
            <h3 style="font-size:.85rem;text-transform:uppercase;letter-spacing:.06em;color:var(--danger);margin-bottom:.75rem;">⚠️ Danger Zone</h3>
            <p style="font-size:.85rem;color:var(--text-secondary);margin-bottom:1rem;">Permanently delete this project and all its tasks, comments, and activity. This cannot be undone.</p>
            <form method="POST" action="projects/delete.php" onsubmit="return confirm('DELETE this project permanently? All tasks and data will be lost. This cannot be undone.')">
                <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
                <button type="submit" class="btn btn-danger">🗑️ Delete Project</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
