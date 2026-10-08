<?php
require_once __DIR__ . '/../includes/auth.php';
requireAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$task_id = $_POST['task_id'] ?? 0;

// Check if user can delete task
if (!canDeleteTask($task_id)) {
    echo json_encode(['success' => false, 'message' => 'You do not have permission to delete this task']);
    exit;
}

try {
    // Get task details before deletion
    $stmt = $pdo->prepare("SELECT project_id, title FROM tasks WHERE id = ?");
    $stmt->execute([$task_id]);
    $task = $stmt->fetch();
    
    // Delete task
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->execute([$task_id]);
    
    // Log activity
    logActivity($task['project_id'], null, getCurrentUserId(), 'deleted_task', 'deleted task: ' . htmlspecialchars($task['title']));
    
    echo json_encode(['success' => true, 'message' => 'Task deleted successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to delete task']);
}
