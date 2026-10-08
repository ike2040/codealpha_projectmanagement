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
    echo json_encode(['success' => false, 'message' => 'You do not have permission to remove members']);
    exit;
}

// Prevent removing the owner
$stmt = $pdo->prepare("SELECT owner_id FROM projects WHERE id = ?");
$stmt->execute([$project_id]);
$project = $stmt->fetch();

if ($project['owner_id'] == $user_id) {
    echo json_encode(['success' => false, 'message' => 'Cannot remove the project owner']);
    exit;
}

try {
    // Get user details for logging
    $stmt = $pdo->prepare("SELECT full_name FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    // Remove member
    $stmt = $pdo->prepare("DELETE FROM project_members WHERE project_id = ? AND user_id = ?");
    $stmt->execute([$project_id, $user_id]);
    
    // Log activity
    logActivity($project_id, null, getCurrentUserId(), 'removed_member', 'removed ' . htmlspecialchars($user['full_name']) . ' from the project');
    
    echo json_encode(['success' => true, 'message' => 'Member removed successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to remove member']);
}
