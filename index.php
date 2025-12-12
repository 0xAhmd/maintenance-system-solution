<?php
session_start();
$user = $_SESSION['user'] ?? null;
?>

<?php include "partials/header.php"; ?>
<?php include "partials/navbar.php"; ?>

<div class="container mt-5">
    <?php if ($user): ?>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card text-center">
                    <div class="card-body p-5">
                        <div class="mb-4">
                            <i class="bi bi-person-check-fill" style="font-size: 80px; color: #667eea;"></i>
                        </div>
                        <h2 class="card-title mb-3">مرحباً <?= htmlspecialchars($user['name']) ?></h2>
                        <p class="text-muted mb-4">أنت مسجل دخول كـ 
                            <span class="badge bg-primary"><?= $user['role'] == 'admin' ? 'مدير' : ($user['role'] == 'driver' ? 'سائق' : 'ميكانيكي') ?></span>
                        </p>
                        
                        <div class="d-grid gap-2 col-md-6 mx-auto">
                            <?php if ($user['role'] == 'admin'): ?>
                                <a href="AdminDashboard/dashboard.php" class="btn btn-primary btn-lg">
                                    <i class="bi bi-speedometer2 me-2"></i>الذهاب إلى لوحة التحكم
                                </a>
                            <?php elseif ($user['role'] == 'driver'): ?>
                                <a href="driver/my_vehicle.php" class="btn btn-primary btn-lg">
                                    <i class="bi bi-car-front me-2"></i>عرض عربيتي
                                </a>
                            <?php elseif ($user['role'] == 'mechanic'): ?>
                                <a href="mechanic/tasks.php" class="btn btn-primary btn-lg">
                                    <i class="bi bi-list-task me-2"></i>عرض المهام
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card text-center">
                    <div class="card-body p-5">
                        <div class="mb-4">
                            <i class="bi bi-tools" style="font-size: 80px; color: #667eea;"></i>
                        </div>
                        <h2 class="card-title mb-3">مرحباً بك في نظام إدارة الصيانة</h2>
                        <p class="text-muted mb-4">قم بتسجيل الدخول للوصول إلى حسابك والاستفادة من جميع الميزات</p>
                        <div class="d-grid gap-2 col-md-6 mx-auto">
                            <a href="login.php" class="btn btn-success btn-lg">
                                <i class="bi bi-box-arrow-in-right me-2"></i>تسجيل الدخول
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include "partials/footer.php"; ?>
