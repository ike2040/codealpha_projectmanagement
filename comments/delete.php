<?php
require_once __DIR__ . '/../includes/auth.php';
requireAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$comment_id = $_POST['comment_id'] ?? 0;

// Check if user can delete comment
if (!canDeleteComment($comment_id)) {
    echo json_encode(['success' => false, 'message' => 'You do not have permission to delete this comment']);
    exit;
}

try {
    // Delete comment
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
    $stmt->execute([$comment_id]);
    
    echo json_encode(['success' => true, 'message' => 'Comment deleted successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to delete comment']);
}
