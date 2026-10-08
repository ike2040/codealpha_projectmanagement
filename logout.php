<?php
require_once __DIR__ . '/includes/auth.php';

destroySession();

redirect('login.php', 'You have been logged out successfully.', 'success');
