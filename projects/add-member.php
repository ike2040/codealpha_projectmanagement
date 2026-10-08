<?php
require_once __DIR__ . '/../includes/auth.php';
requireAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$project_id = $_POST['project_id'] ?? 0;
$user_id = $_POST['user_id'] ?? 0;

// Check if user can manage project
if (!canManageProject($project_id)) {
    echo json_encode(['success' => false, 'message' => 'You do not have permission to add members']);
    exit;
}

// Check if user is already a member
$stmt = $pdo->prepare("SELECT id FROM project_members WHERE project_id = ? AND user_id = ?");
$stmt->execute([$project_id, $user_id]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'User is already a member']);
    exit;
}

try {
    // Add member with default role 'Member'
    $stmt = $pdo->prepare("INSERT INTO project_members (project_id, user_id, role) VALUES (?, ?, 'Member')");
    $stmt->execute([$project_id, $user_id]);
    
    // Get user details for notification
    $stmt = $pdo->prepare("SELECT full_name FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    // Get project details
    $stmt = $pdo->prepare("SELECT name FROM projects WHERE id = ?");
    $stmt->execute([$project_id]);
    $project = $stmt->fetch();
    
    // Log activity
    logActivity($project_id, null, getCurrentUserId(), 'added_member', 'added ' . htmlspecialchars($user['full_name']) . ' to the project');
    
    // Create notification
    createNotification($user_id, 'project_added', 'You have been added to the project: ' . htmlspecialchars($project['name']), $project_id);
    
    echo json_encode(['success' => true, 'message' => 'Member added successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to add member']);
}
