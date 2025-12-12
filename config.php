<?php
/**
 * Central Configuration File
 * Defines BASE_URL and other constants used throughout the application
 */

// Calculate BASE_URL based on document root, not current file location
// This ensures consistent paths regardless of which file includes this
$script_name = $_SERVER['SCRIPT_NAME'];
$base_path = dirname($script_name);
$BASE_URL = $base_path === '/' || $base_path === '\\' ? '' : $base_path;

// Define root directory path for includes
define('ROOT_PATH', dirname(__FILE__));

// Helper function to get base URL
function getBaseUrl() {
    global $BASE_URL;
    return $BASE_URL;
}

// Helper function for redirects
function redirect($path) {
    global $BASE_URL;
    $url = $BASE_URL . '/' . ltrim($path, '/');
    header("Location: " . $url);
    exit();
}


