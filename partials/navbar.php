<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$user = $_SESSION['user'] ?? null;

// Calculate BASE_URL - always get the project root, not current directory
$script_name = $_SERVER['SCRIPT_NAME'];
$base_path = dirname($script_name);

// Normalize path
$base_path = trim($base_path, '/\\');

// If the path contains subdirectories (AdminDashboard, driver, mechanic), go up to root
// This ensures BASE_URL is always the project root, not the current subdirectory
if (empty($base_path) || $base_path === '/') {
    $BASE_URL = '';
} else {
    $path_parts = explode('/', $base_path);
    $project_root_parts = [];

    foreach ($path_parts as $part) {
        if (empty($part)) continue; // Skip empty parts
        if (in_array($part, ['AdminDashboard', 'driver', 'mechanic', 'partials'])) {
            break; // Stop before subdirectories
        }
        $project_root_parts[] = $part;
    }

    $BASE_URL = empty($project_root_parts) ? '' : '/' . implode('/', $project_root_parts);
}
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?= $BASE_URL ?>/index.php">
      <i class="bi bi-tools me-2"></i>نظام إدارة الصيانة
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto">

        <?php if($user && $user['role'] == 'admin'): ?>

          <li class="nav-item">
            <a class="nav-link" href="<?= $BASE_URL ?>/AdminDashboard/dashboard.php">
              <i class="bi bi-speedometer2 me-1"></i>لوحة التحكم
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= $BASE_URL ?>/AdminDashboard/vehicles.php">
              <i class="bi bi-car-front me-1"></i>العربيات
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= $BASE_URL ?>/AdminDashboard/users.php">
              <i class="bi bi-people me-1"></i>المستخدمون
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= $BASE_URL ?>/AdminDashboard/spare_parts.php">
              <i class="bi bi-gear me-1"></i>قطع الغيار
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= $BASE_URL ?>/AdminDashboard/maintinance.php">
              <i class="bi bi-wrench-adjustable me-1"></i>الصيانة
            </a>
          </li>

        <?php elseif($user && $user['role'] == 'driver'): ?>

          <li class="nav-item">
            <a class="nav-link" href="<?= $BASE_URL ?>/driver/my_vehicle.php">
              <i class="bi bi-car-front me-1"></i>عربيتي
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= $BASE_URL ?>/driver/maintenance_status.php">
              <i class="bi bi-clipboard-check me-1"></i>حالة الصيانة
            </a>
          </li>

        <?php elseif($user && $user['role'] == 'mechanic'): ?>

          <li class="nav-item">
            <a class="nav-link" href="<?= $BASE_URL ?>/mechanic/tasks.php">
              <i class="bi bi-list-task me-1"></i>المهام
            </a>
          </li>

        <?php endif; ?>

      </ul>

      <ul class="navbar-nav ms-auto">

        <?php if($user): ?>
          <li class="nav-item">
            <span class="nav-link">
              <i class="bi bi-person-circle me-1"></i>
              مرحباً، <?= htmlspecialchars($user['name']) ?>
            </span>
          </li>
          <li class="nav-item">
            <a class="nav-link text-danger" href="<?= $BASE_URL ?>/logout.php">
              <i class="bi bi-box-arrow-right me-1"></i>تسجيل الخروج
            </a>
          </li>

        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="<?= $BASE_URL ?>/login.php">
              <i class="bi bi-box-arrow-in-right me-1"></i>تسجيل الدخول
            </a>
          </li>
        <?php endif; ?>

      </ul>

    </div>
  </div>
</nav>
