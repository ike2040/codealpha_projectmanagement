<?php
// Helper Functions for Project Management Tool

// Sanitize input
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Get current user data
function getCurrentUser() {
    global $pdo;
    if (!isLoggedIn()) return null;
    
    $stmt = $pdo->prepare("SELECT id, full_name, username, email, profile_picture, created_at FROM users WHERE id = ?");
    $stmt->execute([getCurrentUserId()]);
    return $stmt->fetch();
}

// Check if user is project member
function isProjectMember($project_id, $user_id = null) {
    global $pdo;
    $user_id = $user_id ?? getCurrentUserId();
    
    $stmt = $pdo->prepare("SELECT id FROM project_members WHERE project_id = ? AND user_id = ?");
    $stmt->execute([$project_id, $user_id]);
    return $stmt->fetch() !== false;
}

// Get user role in project
function getProjectRole($project_id, $user_id = null) {
    global $pdo;
    $user_id = $user_id ?? getCurrentUserId();
    
    $stmt = $pdo->prepare("SELECT role FROM project_members WHERE project_id = ? AND user_id = ?");
    $stmt->execute([$project_id, $user_id]);
    $result = $stmt->fetch();
    return $result['role'] ?? null;
}

// Check if user can manage project (Owner or Admin)
function canManageProject($project_id, $user_id = null) {
    $role = getProjectRole($project_id, $user_id);
    return in_array($role, ['Owner', 'Admin']);
}

// Check if user is project owner
function isProjectOwner($project_id, $user_id = null) {
    return getProjectRole($project_id, $user_id) === 'Owner';
}

// Check if user can edit task
function canEditTask($task_id, $user_id = null) {
    global $pdo;
    $user_id = $user_id ?? getCurrentUserId();
    
    $stmt = $pdo->prepare("SELECT t.project_id, t.created_by, t.assigned_to FROM tasks t WHERE t.id = ?");
    $stmt->execute([$task_id]);
    $task = $stmt->fetch();
    
    if (!$task) return false;
    
    // Can edit if created by user, assigned to user, or can manage project
    if ($task['created_by'] == $user_id || $task['assigned_to'] == $user_id) {
        return true;
    }
    
    return canManageProject($task['project_id'], $user_id);
}

// Check if user can delete task
function canDeleteTask($task_id, $user_id = null) {
    global $pdo;
    $user_id = $user_id ?? getCurrentUserId();
    
    $stmt = $pdo->prepare("SELECT project_id, created_by FROM tasks WHERE id = ?");
    $stmt->execute([$task_id]);
    $task = $stmt->fetch();
    
    if (!$task) return false;
    
    // Can delete if created by user or can manage project
    if ($task['created_by'] == $user_id) {
        return true;
    }
    
    return canManageProject($task['project_id'], $user_id);
}

// Check if user can edit comment
function canEditComment($comment_id, $user_id = null) {
    global $pdo;
    $user_id = $user_id ?? getCurrentUserId();
    
    $stmt = $pdo->prepare("SELECT user_id FROM comments WHERE id = ?");
    $stmt->execute([$comment_id]);
    $comment = $stmt->fetch();
    
    return $comment && $comment['user_id'] == $user_id;
}

// Check if user can delete comment
function canDeleteComment($comment_id, $user_id = null) {
    global $pdo;
    $user_id = $user_id ?? getCurrentUserId();
    
    $stmt = $pdo->prepare("SELECT c.user_id, t.project_id FROM comments c JOIN tasks t ON c.task_id = t.id WHERE c.id = ?");
    $stmt->execute([$comment_id]);
    $comment = $stmt->fetch();
    
    if (!$comment) return false;
    
    // Can delete if comment owner or can manage project
    if ($comment['user_id'] == $user_id) {
        return true;
    }
    
    return canManageProject($comment['project_id'], $user_id);
}

// Log activity
function logActivity($project_id, $task_id, $user_id, $action, $description) {
    global $pdo;
    
    $stmt = $pdo->prepare("INSERT INTO activity_logs (project_id, task_id, user_id, action, description) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$project_id, $task_id, $user_id, $action, $description]);
}

// Create notification
function createNotification($user_id, $type, $message, $project_id = null, $task_id = null) {
    global $pdo;
    
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, type, message, related_project_id, related_task_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $type, $message, $project_id, $task_id]);
}

// Get unread notification count
function getUnreadNotificationCount($user_id = null) {
    global $pdo;
    $user_id = $user_id ?? getCurrentUserId();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = FALSE");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    return $result['count'] ?? 0;
}

// Mark notification as read
function markNotificationAsRead($notification_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE id = ? AND user_id = ?");
    $stmt->execute([$notification_id, getCurrentUserId()]);
}

// Mark all notifications as read
function markAllNotificationsAsRead($user_id = null) {
    global $pdo;
    $user_id = $user_id ?? getCurrentUserId();
    
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ?");
    $stmt->execute([$user_id]);
}

// Format date
function formatDate($date, $format = 'M d, Y') {
    if (!$date) return 'N/A';
    return date($format, strtotime($date));
}

// Format datetime
function formatDateTime($datetime, $format = 'M d, Y g:i A') {
    if (!$datetime) return 'N/A';
    return date($format, strtotime($datetime));
}

// Calculate project progress
function calculateProjectProgress($project_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status = 'Done' THEN 1 ELSE 0 END) as completed FROM tasks WHERE project_id = ?");
    $stmt->execute([$project_id]);
    $result = $stmt->fetch();
    
    $total = $result['total'] ?? 0;
    $completed = $result['completed'] ?? 0;
    
    if ($total == 0) return 0;
    return round(($completed / $total) * 100);
}

// Get project members
function getProjectMembers($project_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT u.id, u.full_name, u.username, u.email, u.profile_picture, pm.role 
        FROM project_members pm 
        JOIN users u ON pm.user_id = u.id 
        WHERE pm.project_id = ?
        ORDER BY pm.role DESC, u.full_name ASC
    ");
    $stmt->execute([$project_id]);
    return $stmt->fetchAll();
}

// Get project task count
function getProjectTaskCount($project_id, $status = null) {
    global $pdo;
    
    if ($status) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tasks WHERE project_id = ? AND status = ?");
        $stmt->execute([$project_id, $status]);
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tasks WHERE project_id = ?");
        $stmt->execute([$project_id]);
    }
    
    $result = $stmt->fetch();
    return $result['count'] ?? 0;
}

// Redirect with message
function redirect($url, $message = null, $type = 'success') {
    if ($message) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    header("Location: $url");
    exit;
}

// Get flash message
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

// Generate CSRF token
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Upload profile picture
function uploadProfilePicture($file) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $filename = $file['name'];
    $filetype = pathinfo($filename, PATHINFO_EXTENSION);
    
    if (!in_array(strtolower($filetype), $allowed)) {
        return ['success' => false, 'message' => 'Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.'];
    }
    
    if ($file['size'] > 2097152) { // 2MB
        return ['success' => false, 'message' => 'File size too large. Maximum 2MB allowed.'];
    }
    
    $newFilename = uniqid() . '.' . $filetype;
    $uploadPath = __DIR__ . '/../uploads/profiles/' . $newFilename;
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return ['success' => true, 'filename' => $newFilename];
    }
    
    return ['success' => false, 'message' => 'Failed to upload file.'];
}

// Get default profile picture
function getDefaultProfilePicture($gender = 'male') {
    return 'https://ui-avatars.com/api/?name=User&background=random&size=128';
}

// Get user profile picture URL
function getProfilePictureUrl($user) {
    if ($user['profile_picture']) {
        return 'uploads/profiles/' . $user['profile_picture'];
    }
    return getDefaultProfilePicture();
}
