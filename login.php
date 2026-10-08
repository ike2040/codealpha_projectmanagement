<?php
require_once __DIR__ . '/includes/auth.php';
requireGuest();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_field = sanitize($_POST['login_field'] ?? '');
    $password    = $_POST['password'] ?? '';

    if (empty($login_field)) {
        $errors[] = 'Email or username is required.';
    }
    if (empty($password)) {
        $errors[] = 'Password is required.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare(
            "SELECT id, full_name, username, email, password, profile_picture
             FROM users WHERE email = ? OR username = ?"
        );
        $stmt->execute([$login_field, $login_field]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']         = $user['id'];
            $_SESSION['full_name']       = $user['full_name'];
            $_SESSION['username']        = $user['username'];
            $_SESSION['email']           = $user['email'];
            $_SESSION['profile_picture'] = $user['profile_picture'];

            regenerateSession();
            redirect('dashboard.php', 'Welcome back, ' . htmlspecialchars($user['full_name']) . '!', 'success');
        } else {
            $errors[] = 'Invalid email/username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sign in to ProjectHub — your collaborative project management workspace.">
    <title>Sign In — ProjectHub</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">

    <div class="auth-container">

        <!-- Brand mark -->
        <div class="auth-brand">
            <div class="auth-brand-logo">📋</div>
            <h2>ProjectHub</h2>
            <p>Your collaborative project workspace</p>
        </div>

        <!-- Card -->
        <div class="auth-card">
            <div class="auth-header">
                <h1>Welcome back</h1>
                <p>Sign in to continue to your workspace</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error" role="alert">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php $flash = getFlashMessage(); ?>
            <?php if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type']; ?>" role="alert">
                    <p><?php echo htmlspecialchars($flash['message']); ?></p>
                </div>
            <?php endif; ?>

            <form method="POST" class="auth-form" id="loginForm" novalidate>
                <div class="form-group">
                    <label for="login_field">Email or Username</label>
                    <input type="text"
                           id="login_field"
                           name="login_field"
                           placeholder="you@example.com"
                           value="<?php echo htmlspecialchars($_POST['login_field'] ?? ''); ?>"
                           autocomplete="username"
                           required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password"
                           id="password"
                           name="password"
                           placeholder="••••••••"
                           autocomplete="current-password"
                           required>
                </div>

                <button type="submit" class="btn btn-primary btn-block" id="login-submit-btn">
                    Sign In →
                </button>
            </form>

            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php">Create one free</a></p>
            </div>
        </div><!-- /.auth-card -->

    </div><!-- /.auth-container -->

</body>
</html>
