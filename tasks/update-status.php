<?php
require_once __DIR__ . '/../includes/auth.php';
requireAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$task_id = $_POST['task_id'] ?? 0;
$new_status = sanitize($_POST['status'] ?? '');

// Check if user is project member
$stmt = $pdo->prepare("SELECT project_id FROM tasks WHERE id = ?");
$stmt->execute([$task_id]);
$task = $stmt->fetch();

if (!$task || !isProjectMember($task['project_id'])) {
    echo json_encode(['success' => false, 'message' => 'You do not have access to this task']);
    exit;
}

// Validate status
$valid_statuses = ['Todo', 'In Progress', 'Review', 'Done'];
if (!in_array($new_status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

try {
    // Get current status
    $stmt = $pdo->prepare("SELECT status, title FROM tasks WHERE id = ?");
    $stmt->execute([$task_id]);
    $current_task = $stmt->fetch();
    
    // Update status
    $stmt = $pdo->prepare("UPDATE tasks SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->execute([$new_status, $task_id]);
    
    // Log activity
    logActivity($task['project_id'], $task_id, getCurrentUserId(), 'changed_status', 'changed status from ' . htmlspecialchars($current_task['status']) . ' to ' . htmlspecialchars($new_status));
    
    // Notify if task is completed
    if ($new_status === 'Done' && $current_task['status'] !== 'Done') {
        $stmt = $pdo->prepare("SELECT created_by, assigned_to FROM tasks WHERE id = ?");
        $stmt->execute([$task_id]);
        $task_details = $stmt->fetch();
        
        // Notify creator
        if ($task_details['created_by'] != getCurrentUserId()) {
            createNotification($task_details['created_by'], 'task_completed', 'Task completed: ' . htmlspecialchars($current_task['title']), $task['project_id'], $task_id);
        }
        
        // Notify assignee if different
        if ($task_details['assigned_to'] && $task_details['assigned_to'] != getCurrentUserId() && $task_details['assigned_to'] != $task_details['created_by']) {
            createNotification($task_details['assigned_to'], 'task_completed', 'Task completed: ' . htmlspecialchars($current_task['title']), $task['project_id'], $task_id);
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'Task status updated successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to update task status']);
}
