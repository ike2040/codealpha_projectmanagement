<?php
require_once __DIR__ . '/../includes/auth.php';
requireAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$task_id = $_POST['task_id'] ?? 0;
$comment = sanitize($_POST['comment'] ?? '');

// Check if user has access to the task
$stmt = $pdo->prepare("SELECT project_id FROM tasks WHERE id = ?");
$stmt->execute([$task_id]);
$task = $stmt->fetch();

if (!$task || !isProjectMember($task['project_id'])) {
    echo json_encode(['success' => false, 'message' => 'You do not have access to this task']);
    exit;
}

// Validation
if (empty($comment)) {
    echo json_encode(['success' => false, 'message' => 'Comment cannot be empty']);
    exit;
}

try {
    // Create comment
    $stmt = $pdo->prepare("INSERT INTO comments (task_id, user_id, comment) VALUES (?, ?, ?)");
    $stmt->execute([$task_id, getCurrentUserId(), $comment]);
    $comment_id = $pdo->lastInsertId();
    
    // Get task details for notification
    $stmt = $pdo->prepare("SELECT title, created_by, assigned_to FROM tasks WHERE id = ?");
    $stmt->execute([$task_id]);
    $task_details = $stmt->fetch();
    
    // Log activity
    logActivity($task['project_id'], $task_id, getCurrentUserId(), 'added_comment', 'added a comment');
    
    // Notify task creator and assignee
    $current_user_id = getCurrentUserId();
    if ($task_details['created_by'] != $current_user_id) {
        createNotification($task_details['created_by'], 'comment_added', 'New comment on task: ' . htmlspecialchars($task_details['title']), $task['project_id'], $task_id);
    }
    
    if ($task_details['assigned_to'] && $task_details['assigned_to'] != $current_user_id && $task_details['assigned_to'] != $task_details['created_by']) {
        createNotification($task_details['assigned_to'], 'comment_added', 'New comment on task: ' . htmlspecialchars($task_details['title']), $task['project_id'], $task_id);
    }
    
    // Get comment details for response
    $stmt = $pdo->prepare("
        SELECT c.*, u.full_name, u.username, u.profile_picture
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.id = ?
    ");
    $stmt->execute([$comment_id]);
    $comment_data = $stmt->fetch();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Comment added successfully',
        'comment' => $comment_data
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to add comment']);
}
