<?php
require_once __DIR__ . '/includes/auth.php';
requireAuth();

$page_title = 'Notifications';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$current_user_id = getCurrentUserId();

// Get notifications
$stmt = $pdo->prepare("
    SELECT n.*, 
           p.name as project_name,
           t.title as task_title
    FROM notifications n
    LEFT JOIN projects p ON n.related_project_id = p.id
    LEFT JOIN tasks t ON n.related_task_id = t.id
    WHERE n.user_id = ?
    ORDER BY n.created_at DESC
    LIMIT 50
");
$stmt->execute([$current_user_id]);
$notifications = $stmt->fetchAll();

// Mark all as read if requested
if (isset($_GET['mark_read'])) {
    markAllNotificationsAsRead();
    redirect('notifications.php');
}
?>

<main class="main-content">
    <div class="container">
        <div class="page-header">
            <h1>Notifications</h1>
            <a href="notifications.php?mark_read=1" class="btn btn-secondary">Mark All as Read</a>
        </div>

        <?php $flash = getFlashMessage(); ?>
        <?php if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <p><?php echo htmlspecialchars($flash['message']); ?></p>
            </div>
        <?php endif; ?>

        <div class="notifications-list">
            <?php if (empty($notifications)): ?>
                <div class="empty-state">
                    <p>No notifications</p>
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-item <?php echo $notification['is_read'] ? 'read' : 'unread'; ?>" data-notification-id="<?php echo $notification['id']; ?>">
                        <div class="notification-icon">
                            <?php 
                            $icon = '🔔';
                            switch ($notification['type']) {
                                case 'project_added':
                                    $icon = '📁';
                                    break;
                                case 'task_assigned':
                                    $icon = '📋';
                                    break;
                                case 'task_completed':
                                    $icon = '✅';
                                    break;
                                case 'comment_added':
                                    $icon = '💬';
                                    break;
                            }
                            echo $icon;
                            ?>
                        </div>
                        <div class="notification-content">
                            <p class="notification-message"><?php echo htmlspecialchars($notification['message']); ?></p>
                            <p class="notification-time"><?php echo formatDateTime($notification['created_at']); ?></p>
                            <?php if ($notification['project_name']): ?>
                                <p class="notification-project">Project: <?php echo htmlspecialchars($notification['project_name']); ?></p>
                            <?php endif; ?>
                        </div>
                        <?php if (!$notification['is_read']): ?>
                            <button class="btn btn-sm btn-secondary mark-read-btn" onclick="markAsRead(<?php echo $notification['id']; ?>)">Mark as Read</button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
function markAsRead(notificationId) {
    fetch('api/notifications.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=mark_read&notification_id=' + notificationId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const item = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (item) {
                item.classList.remove('unread');
                item.classList.add('read');
                const btn = item.querySelector('.mark-read-btn');
                if (btn) btn.remove();
            }
        }
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
