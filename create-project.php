<?php
require_once __DIR__ . '/includes/auth.php';
requireAuth();

$page_title = 'Create Project';
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
            $pdo->beginTransaction();
            
            // Create project
            $stmt = $pdo->prepare("INSERT INTO projects (owner_id, name, description, start_date, due_date, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([getCurrentUserId(), $name, $description, $start_date ?: null, $due_date ?: null, $status]);
            $project_id = $pdo->lastInsertId();
            
            // Add owner as project member
            $stmt = $pdo->prepare("INSERT INTO project_members (project_id, user_id, role) VALUES (?, ?, 'Owner')");
            $stmt->execute([$project_id, getCurrentUserId()]);
            
            // Log activity
            logActivity($project_id, null, getCurrentUserId(), 'created', 'created this project');
            
            $pdo->commit();
            
            redirect('projects.php', 'Project created successfully!', 'success');
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Failed to create project. Please try again.';
        }
    }
}
?>

<main class="main-content">
    <div class="container">
        <div class="page-header">
            <h1>Create New Project</h1>
            <a href="projects.php" class="btn btn-secondary">← Back to Projects</a>
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
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" rows="4" required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="start_date">Start Date</label>
                        <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($_POST['start_date'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="due_date">Due Date</label>
                        <input type="date" id="due_date" name="due_date" value="<?php echo htmlspecialchars($_POST['due_date'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="Planning" <?php echo ($_POST['status'] ?? '') === 'Planning' ? 'selected' : ''; ?>>Planning</option>
                        <option value="In Progress" <?php echo ($_POST['status'] ?? '') === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="On Hold" <?php echo ($_POST['status'] ?? '') === 'On Hold' ? 'selected' : ''; ?>>On Hold</option>
                        <option value="Completed" <?php echo ($_POST['status'] ?? '') === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Create Project</button>
                    <a href="projects.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
