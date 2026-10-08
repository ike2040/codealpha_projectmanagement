<?php
$current_user  = getCurrentUser();
$unread_count  = getUnreadNotificationCount();
$current_page  = basename($_SERVER['PHP_SELF']);

function nav_active(string $page): string {
    global $current_page;
    return $current_page === $page ? 'active' : '';
}
?>
<nav class="navbar" role="navigation" aria-label="Main navigation">
    <div class="navbar-container">

        <!-- Brand -->
        <div class="navbar-brand">
            <a href="dashboard.php" aria-label="ProjectHub home">
                <span class="logo-icon">📋</span>
                <span class="logo-text">ProjectHub</span>
            </a>
        </div>

        <!-- Mobile toggle -->
        <button class="mobile-menu-toggle" id="mobileMenuToggle"
                aria-expanded="false" aria-controls="navbarNav"
                aria-label="Toggle navigation">
            <span></span><span></span><span></span>
        </button>

        <!-- Nav links -->
        <ul class="navbar-nav" id="navbarNav" role="list">
            <li class="nav-item">
                <a href="dashboard.php"
                   class="nav-link <?php echo nav_active('dashboard.php'); ?>"
                   id="nav-dashboard">
                    <span class="nav-icon">📊</span>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="projects.php"
                   class="nav-link <?php echo nav_active('projects.php'); ?>"
                   id="nav-projects">
                    <span class="nav-icon">📁</span>
                    <span>Projects</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="my-tasks.php"
                   class="nav-link <?php echo nav_active('my-tasks.php'); ?>"
                   id="nav-my-tasks">
                    <span class="nav-icon">✅</span>
                    <span>My Tasks</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="notifications.php"
                   class="nav-link <?php echo nav_active('notifications.php'); ?>"
                   id="nav-notifications">
                    <span class="nav-icon">🔔</span>
                    <span>Notifications</span>
                    <?php if ($unread_count > 0): ?>
                        <span class="notification-badge" aria-label="<?php echo $unread_count; ?> unread">
                            <?php echo $unread_count; ?>
                        </span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a href="profile.php"
                   class="nav-link <?php echo nav_active('profile.php'); ?>"
                   id="nav-profile">
                    <span class="nav-icon">👤</span>
                    <span>Profile</span>
                </a>
            </li>
            <li class="nav-divider" role="separator"></li>
            <li class="nav-item">
                <a href="logout.php" class="nav-link nav-logout" id="nav-logout">
                    <span class="nav-icon">🚪</span>
                    <span>Logout</span>
                </a>
            </li>
            <li class="nav-item">
                <button class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode" title="Toggle dark mode">🌙</button>
            </li>
        </ul>

        <!-- User chip -->
        <div class="navbar-user" aria-label="User info">
            <div class="user-avatar">
                <img src="<?php echo getProfilePictureUrl($current_user); ?>"
                     alt="<?php echo htmlspecialchars($current_user['full_name']); ?>">
            </div>
            <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars($current_user['full_name']); ?></span>
                <span class="user-username">@<?php echo htmlspecialchars($current_user['username']); ?></span>
            </div>
        </div>

    </div><!-- /.navbar-container -->
</nav>

<script>
// Mobile menu toggle
(function() {
    var btn = document.getElementById('mobileMenuToggle');
    var nav = document.getElementById('navbarNav');
    if (btn && nav) {
        btn.addEventListener('click', function() {
            var open = nav.classList.toggle('active');
            btn.setAttribute('aria-expanded', open);
        });
    }
})();
</script>
