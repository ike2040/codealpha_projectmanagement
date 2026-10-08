<?php
if (!isset($page_title)) {
    $page_title = 'ProjectHub — Project Management Tool';
}
// Build absolute asset path (works for any subfolder depth)
$base_url = '//' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$assets_path = '/codealpha_projectmanagement/assets';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="ProjectHub – Manage your projects, collaborate with your team, and track progress effortlessly.">
    <meta name="theme-color" content="#6366f1">
    <title><?php echo htmlspecialchars($page_title); ?></title>

    <!-- Google Fonts: Inter + Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">

    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="<?php echo $assets_path; ?>/css/style.css">
    <script>
        // Apply dark mode before page renders to prevent flash
        (function() {
            var theme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
</head>
<body>
