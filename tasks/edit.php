<?php
require_once __DIR__ . '/../includes/auth.php';
requireAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$task_id = $_POST['task_id'] ?? 0;
$title = sanitize($_POST['title'] ?? '');
$description = sanitize($_POST['description'] ?? '');
$priority = sanitize($_POST['priority'] ?? 'Medium');
$status = sanitize($_POST['status'] ?? 'Todo');
$assigned_to = $_POST['assigned_to'] ?? null;
$due_date = $_POST['due_date'] ?? null;

// Check if user can edit task
if (!canEditTask($task_id)) {
    echo json_encode(['success' => false, 'message' => 'You do not have permission to edit this task']);
    exit;
}

// Validation
if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Task title is required']);
    exit;
}

try {
    // Get current task details for comparison
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
    $stmt->execute([$task_id]);
    $old_task = $stmt->fetch();
    
    // Update task
    $stmt = $pdo->prepare("
        UPDATE tasks 
        SET title = ?, description = ?, priority = ?, status = ?, assigned_to = ?, due_date = ?, updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    ");
    $stmt->execute([$title, $description, $priority, $status, $assigned_to ?: null, $due_date ?: null, $task_id]);
    
    // Log changes
    if ($old_task['status'] !== $status) {
        logActivity($old_task['project_id'], $task_id, getCurrentUserId(), 'changed_status', 'changed status from ' . htmlspecialchars($old_task['status']) . ' to ' . htmlspecialchars($status));
    }
    
    if ($old_task['priority'] !== $priority) {
        logActivity($old_task['project_id'], $task_id, getCurrentUserId(), 'changed_priority', 'changed priority to ' . htmlspecialchars($priority));
    }
    
    if ($old_task['assigned_to'] != $assigned_to) {
        logActivity($old_task['project_id'], $task_id, getCurrentUserId(), 'reassigned_task', 'reassigned the task');
        
        // Notify new assignee
        if ($assigned_to && $assigned_to != getCurrentUserId()) {
            createNotification($assigned_to, 'task_assigned', 'You have been assigned to task: ' . htmlspecialchars($title), $old_task['project_id'], $task_id);
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'Task updated successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to update task']);
}
