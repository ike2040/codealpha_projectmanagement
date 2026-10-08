<?php
require_once __DIR__ . '/../includes/auth.php';
requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('projects.php', 'Invalid request.', 'error');
}

$project_id = intval($_POST['project_id'] ?? 0);

if (!$project_id || !isProjectOwner($project_id)) {
    redirect('projects.php', 'You do not have permission to delete this project.', 'error');
}

try {
    // Cascading deletes handle members, tasks, comments, notifications, activity_logs
    $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ? AND owner_id = ?");
    $stmt->execute([$project_id, getCurrentUserId()]);
    redirect('projects.php', 'Project deleted successfully.', 'success');
} catch (Exception $e) {
    redirect('projects.php', 'Failed to delete project.', 'error');
}
