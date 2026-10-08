<?php
require_once __DIR__ . '/includes/auth.php';
requireGuest();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name'] ?? '');
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($full_name)) {
        $errors[] = 'Full name is required.';
    } elseif (strlen($full_name) < 2) {
        $errors[] = 'Full name must be at least 2 characters.';
    }
    
    if (empty($username)) {
        $errors[] = 'Username is required.';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Username can only contain letters, numbers, and underscores.';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }
    
    // Check for duplicate username
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $errors[] = 'Username already exists.';
        }
    }
    
    // Check for duplicate email
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Email already exists.';
        }
    }
    
    // Handle profile picture upload
    $profile_picture = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadProfilePicture($_FILES['profile_picture']);
        if ($upload_result['success']) {
            $profile_picture = $upload_result['filename'];
        } else {
            $errors[] = $upload_result['message'];
        }
    }
    
    // Create user if no errors
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (full_name, username, email, password, profile_picture) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$full_name, $username, $email, $hashed_password, $profile_picture]);
        
        $success = true;
        redirect('login.php', 'Registration successful! Please login.', 'success');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Create your free ProjectHub account and start managing projects today.">
    <title>Create Account — ProjectHub</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">

    <div class="auth-container" style="max-width: 460px;">

        <!-- Brand mark -->
        <div class="auth-brand">
            <div class="auth-brand-logo">📋</div>
            <h2>ProjectHub</h2>
            <p>Create your free account</p>
        </div>

        <!-- Card -->
        <div class="auth-card">
            <div class="auth-header">
                <h1>Get started</h1>
                <p>Join thousands of teams managing projects better</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error" role="alert">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="auth-form" id="registerForm" novalidate>
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name"
                           placeholder="Jane Doe"
                           value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>"
                           autocomplete="name" required>
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username"
                           placeholder="janedoe"
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                           autocomplete="username" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                           placeholder="jane@example.com"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           autocomplete="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password"
                           placeholder="Min. 6 characters"
                           autocomplete="new-password" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password"
                           placeholder="Repeat your password"
                           autocomplete="new-password" required>
                </div>

                <div class="form-group">
                    <label for="profile_picture">Profile Picture <span style="opacity:0.55;font-weight:400">(Optional)</span></label>
                    <input type="file" id="profile_picture" name="profile_picture" accept="image/*"
                           style="background:rgba(255,255,255,0.05);color:rgba(255,255,255,0.65);border-color:rgba(255,255,255,0.15);">
                    <small style="color:rgba(255,255,255,0.4)">Max 2 MB — JPG, PNG, or GIF</small>
                </div>

                <button type="submit" class="btn btn-primary btn-block" id="register-submit-btn">
                    Create Account →
                </button>
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Sign in</a></p>
            </div>
        </div><!-- /.auth-card -->

    </div><!-- /.auth-container -->

</body>
</html>
