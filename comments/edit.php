<?php
require_once __DIR__ . '/../includes/auth.php';
requireAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$comment_id = $_POST['comment_id'] ?? 0;
$comment = sanitize($_POST['comment'] ?? '');

// Check if user can edit comment
if (!canEditComment($comment_id)) {
    echo json_encode(['success' => false, 'message' => 'You do not have permission to edit this comment']);
    exit;
}

// Validation
if (empty($comment)) {
    echo json_encode(['success' => false, 'message' => 'Comment cannot be empty']);
    exit;
}

try {
    // Update comment
    $stmt = $pdo->prepare("UPDATE comments SET comment = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->execute([$comment, $comment_id]);
    
    echo json_encode(['success' => true, 'message' => 'Comment updated successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to update comment']);
}
