<?php
require_once __DIR__ . '/../includes/auth.php';
requireAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'mark_read') {
        $notification_id = $_POST['notification_id'] ?? 0;
        markNotificationAsRead($notification_id);
        echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit;
}

// GET request - return unread count
$unread_count = getUnreadNotificationCount();
echo json_encode(['success' => true, 'unread_count' => $unread_count]);
