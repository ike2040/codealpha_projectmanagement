<?php
require_once __DIR__ . '/../includes/auth.php';
requireAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$project_id = $_POST['project_id'] ?? 0;
$title = sanitize($_POST['title'] ?? '');
$description = sanitize($_POST['description'] ?? '');
$priority = sanitize($_POST['priority'] ?? 'Medium');
$status = sanitize($_POST['status'] ?? 'Todo');
$assigned_to = $_POST['assigned_to'] ?? null;
$due_date = $_POST['due_date'] ?? null;

// Check if user is project member
if (!isProjectMember($project_id)) {
    echo json_encode(['success' => false, 'message' => 'You do not have access to this project']);
    exit;
}

// Validation
if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Task title is required']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // Create task
    $stmt = $pdo->prepare("
        INSERT INTO tasks (project_id, created_by, assigned_to, title, description, priority, status, due_date) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$project_id, getCurrentUserId(), $assigned_to ?: null, $title, $description, $priority, $status, $due_date ?: null]);
    $task_id = $pdo->lastInsertId();
    
    // Get project details
    $stmt = $pdo->prepare("SELECT name FROM projects WHERE id = ?");
    $stmt->execute([$project_id]);
    $project = $stmt->fetch();
    
    // Log activity
    logActivity($project_id, $task_id, getCurrentUserId(), 'created_task', 'created task: ' . htmlspecialchars($title));
    
    // Notify assigned user
    if ($assigned_to && $assigned_to != getCurrentUserId()) {
        createNotification($assigned_to, 'task_assigned', 'You have been assigned to task: ' . htmlspecialchars($title), $project_id, $task_id);
    }
    
    $pdo->commit();
    
    echo json_encode(['success' => true, 'message' => 'Task created successfully', 'task_id' => $task_id]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Failed to create task']);
}
