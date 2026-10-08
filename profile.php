<?php
require_once __DIR__ . '/includes/auth.php';
requireAuth();

$page_title = 'My Profile';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$current_user = getCurrentUser();

// User statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM projects p JOIN project_members pm ON p.id = pm.project_id WHERE pm.user_id = ?");
$stmt->execute([getCurrentUserId()]);
$user_total_projects = $stmt->fetch()['count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tasks WHERE assigned_to = ?");
$stmt->execute([getCurrentUserId()]);
$user_total_tasks = $stmt->fetch()['count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tasks WHERE assigned_to = ? AND status = 'Done'");
$stmt->execute([getCurrentUserId()]);
$user_completed_tasks = $stmt->fetch()['count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM comments WHERE user_id = ?");
$stmt->execute([getCurrentUserId()]);
$user_comments = $stmt->fetch()['count'];

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name'] ?? '');
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($full_name)) {
        $errors[] = 'Full name is required.';
    }
    
    if (empty($username)) {
        $errors[] = 'Username is required.';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }
    
    // Check for duplicate username (if changed)
    if ($username !== $current_user['username']) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$username, getCurrentUserId()]);
        if ($stmt->fetch()) {
            $errors[] = 'Username already exists.';
        }
    }
    
    // Check for duplicate email (if changed)
    if ($email !== $current_user['email']) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, getCurrentUserId()]);
        if ($stmt->fetch()) {
            $errors[] = 'Email already exists.';
        }
    }
    
    // Password change validation
    if ($new_password || $confirm_password || $current_password) {
        if (empty($current_password)) {
            $errors[] = 'Current password is required to change password.';
        } else {
            // Verify current password
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([getCurrentUserId()]);
            $user_data = $stmt->fetch();
            
            if (!password_verify($current_password, $user_data['password'])) {
                $errors[] = 'Current password is incorrect.';
            }
        }
        
        if ($new_password !== $confirm_password) {
            $errors[] = 'New passwords do not match.';
        }
        
        if (strlen($new_password) < 6) {
            $errors[] = 'New password must be at least 6 characters.';
        }
    }
    
    // Handle profile picture upload
    $profile_picture = $current_user['profile_picture'];
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadProfilePicture($_FILES['profile_picture']);
        if ($upload_result['success']) {
            $profile_picture = $upload_result['filename'];
        } else {
            $errors[] = $upload_result['message'];
        }
    }
    
    if (empty($errors)) {
        try {
            // Update user info
            if ($new_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, username = ?, email = ?, profile_picture = ?, password = ? WHERE id = ?");
                $stmt->execute([$full_name, $username, $email, $profile_picture, $hashed_password, getCurrentUserId()]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, username = ?, email = ?, profile_picture = ? WHERE id = ?");
                $stmt->execute([$full_name, $username, $email, $profile_picture, getCurrentUserId()]);
            }
            
            // Update session
            $_SESSION['full_name'] = $full_name;
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            $_SESSION['profile_picture'] = $profile_picture;
            
            $success = true;
            redirect('profile.php', 'Profile updated successfully!', 'success');
        } catch (Exception $e) {
            $errors[] = 'Failed to update profile. Please try again.';
        }
    }
}
?>

<main class="main-content">
    <div class="container">
        <div class="page-header">
            <h1>My Profile</h1>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php $flash = getFlashMessage(); ?>
        <?php if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <p><?php echo htmlspecialchars($flash['message']); ?></p>
            </div>
        <?php endif; ?>

        <div class="profile-container">
            <div class="profile-card">
                <div class="profile-avatar-large">
                    <img src="<?php echo getProfilePictureUrl($current_user); ?>" alt="<?php echo htmlspecialchars($current_user['full_name']); ?>">
                </div>
                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($current_user['full_name']); ?></h2>
                    <p>@<?php echo htmlspecialchars($current_user['username']); ?></p>
                    <p><?php echo htmlspecialchars($current_user['email']); ?></p>
                    <p class="member-since">Member since: <?php echo formatDate($current_user['created_at']); ?></p>
                </div>

                <!-- Stats -->
                <div style="display:flex;flex-direction:column;gap:.5rem;margin-top:1.5rem;padding-top:1.5rem;border-top:1px solid var(--border);">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;">
                        <div style="background:var(--primary-subtle);border-radius:var(--r-md);padding:.75rem;text-align:center;">
                            <div style="font-size:1.4rem;font-weight:700;color:var(--primary);"><?php echo $user_total_projects; ?></div>
                            <div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em;">Projects</div>
                        </div>
                        <div style="background:var(--success-bg);border-radius:var(--r-md);padding:.75rem;text-align:center;">
                            <div style="font-size:1.4rem;font-weight:700;color:var(--success);"><?php echo $user_completed_tasks; ?></div>
                            <div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em;">Done</div>
                        </div>
                        <div style="background:var(--warning-bg);border-radius:var(--r-md);padding:.75rem;text-align:center;">
                            <div style="font-size:1.4rem;font-weight:700;color:var(--warning);"><?php echo $user_total_tasks; ?></div>
                            <div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em;">Tasks</div>
                        </div>
                        <div style="background:var(--info-bg);border-radius:var(--r-md);padding:.75rem;text-align:center;">
                            <div style="font-size:1.4rem;font-weight:700;color:var(--info);"><?php echo $user_comments; ?></div>
                            <div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em;">Comments</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-container">
                <form method="POST" enctype="multipart/form-data" class="profile-form">
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($current_user['full_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($current_user['username']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($current_user['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="profile_picture">Profile Picture</label>
                        <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
                        <small>Max size: 2MB. Allowed: JPG, PNG, GIF</small>
                    </div>
                    
                    <hr class="form-divider">
                    
                    <h3>Change Password</h3>
                    <p class="form-help">Leave blank to keep current password</p>
                    
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password">
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password">
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
