<?php
// partials/header.php
if (session_status() == PHP_SESSION_NONE) session_start();

// Calculate correct path to main.css
// Get the directory of the currently executing script
$script_dir = dirname($_SERVER['PHP_SELF']);
// Remove trailing slash
$script_dir = rtrim($script_dir, '/\\');
// Calculate path to root (where main.css is)
if (strpos($script_dir, '/AdminDashboard') !== false || 
    strpos($script_dir, '/driver') !== false || 
    strpos($script_dir, '/mechanic') !== false) {
    // We're in a subdirectory, go up one level
    $css_path = '../main.css';
} else {
    // We're in root
    $css_path = 'main.css';
}
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>نظام إدارة الصيانة - Fleet Maintenance System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $css_path ?>">
</head>
<body>
