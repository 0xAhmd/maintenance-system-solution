<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'driver') {
    // Calculate correct path to login.php
    $base_path = dirname($_SERVER['PHP_SELF']);
    $login_path = ($base_path === '/' || $base_path === '\\') ? '/login.php' : $base_path . '/login.php';
    header("Location: " . $login_path);
    exit();
}
?>
