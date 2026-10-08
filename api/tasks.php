<?php
require_once __DIR__ . '/../includes/auth.php';
requireAuth();

header('Content-Type: application/json');

$task_id = $_GET['task_id'] ?? 0;

// Check if user has access to the task
$stmt = $pdo->prepare("SELECT project_id FROM tasks WHERE id = ?");
$stmt->execute([$task_id]);
$task = $stmt->fetch();

if (!$task || !isProjectMember($task['project_id'])) {
    echo json_encode(['success' => false, 'message' => 'You do not have access to this task']);
    exit;
}

// Get task details
$stmt = $pdo->prepare("
    SELECT t.*, 
           p.name as project_name,
           u1.full_name as created_by_name,
           u1.username as created_by_username,
           u1.profile_picture as created_by_picture,
           u2.full_name as assigned_to_name,
           u2.username as assigned_to_username,
           u2.profile_picture as assigned_to_picture
    FROM tasks t
    JOIN projects p ON t.project_id = p.id
    LEFT JOIN users u1 ON t.created_by = u1.id
    LEFT JOIN users u2 ON t.assigned_to = u2.id
    WHERE t.id = ?
");
$stmt->execute([$task_id]);
$task_details = $stmt->fetch();

if (!$task_details) {
    echo json_encode(['success' => false, 'message' => 'Task not found']);
    exit;
}

// Get task comments
$stmt = $pdo->prepare("
    SELECT c.*, u.full_name, u.username, u.profile_picture
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.task_id = ?
    ORDER BY c.created_at ASC
");
$stmt->execute([$task_id]);
$comments = $stmt->fetchAll();

// Get task activity
$stmt = $pdo->prepare("
    SELECT al.*, u.full_name, u.username
    FROM activity_logs al
    JOIN users u ON al.user_id = u.id
    WHERE al.task_id = ?
    ORDER BY al.created_at DESC
    LIMIT 20
");
$stmt->execute([$task_id]);
$activity = $stmt->fetchAll();

// Get project members for assignment
$members = getProjectMembers($task['project_id']);

echo json_encode([
    'success' => true,
    'task' => $task_details,
    'comments' => $comments,
    'activity' => $activity,
    'members' => $members,
    'can_edit' => canEditTask($task_id),
    'can_delete' => canDeleteTask($task_id)
]);
