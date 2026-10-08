<?php
require_once __DIR__ . '/includes/auth.php';

// If user is logged in, redirect to dashboard
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$page_title = 'ProjectHub — Manage Projects with Ease';
require_once __DIR__ . '/includes/header.php';
?>

<div class="landing-page">
    <div class="landing-container">

        <!-- Logo / Icon -->
        <div class="landing-logo" aria-hidden="true">📋</div>

        <!-- Headline -->
        <h1 class="landing-title">Ship faster,<br>collaborate smarter.</h1>

        <p class="landing-subtitle">
            ProjectHub brings your team's projects, tasks, and conversations into one beautiful workspace — so nothing falls through the cracks.
        </p>

        <!-- Feature Grid -->
        <div class="landing-features">
            <div class="feature-card">
                <span class="feature-icon">📁</span>
                <h3 class="feature-title">Project Management</h3>
                <p class="feature-description">Create, track, and deliver projects on time with powerful tools.</p>
            </div>
            <div class="feature-card">
                <span class="feature-icon">👥</span>
                <h3 class="feature-title">Team Collaboration</h3>
                <p class="feature-description">Invite members, assign roles, and work together seamlessly.</p>
            </div>
            <div class="feature-card">
                <span class="feature-icon">📋</span>
                <h3 class="feature-title">Kanban Boards</h3>
                <p class="feature-description">Visualize work with drag-and-drop Kanban task management.</p>
            </div>
            <div class="feature-card">
                <span class="feature-icon">🔔</span>
                <h3 class="feature-title">Real-time Updates</h3>
                <p class="feature-description">Stay in sync with instant activity notifications.</p>
            </div>
        </div>

        <!-- CTA Buttons -->
        <div class="landing-cta">
            <a href="register.php" class="btn btn-primary" id="cta-register">
                🚀 Get Started Free
            </a>
            <a href="login.php" class="btn btn-secondary" id="cta-login">
                Sign In
            </a>
        </div>

    </div><!-- /.landing-container -->
</div><!-- /.landing-page -->

</body>
</html>
