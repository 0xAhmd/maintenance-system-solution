function navigateTo(page) {
    window.location.href = page;
}

function logout() {
    if (confirm('هل أنت متأكد من تسجيل الخروج؟')) {
        localStorage.removeItem('isLoggedIn');
        localStorage.removeItem('userRole');
        window.location.href = 'login.html';
    }
}

function getUserRole() {
    return localStorage.getItem('userRole') || 'admin';
}

<!-- ============================================ -->
<!-- FILE 5: dashboard.html (TASK 1 - Dashboard) -->
<!-- ============================================ -->

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="styles/main.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand text-white">
            <h4 class="fw-bold"><i class="bi bi-gear-fill me-2"></i>نظام الصيانة</h4>
            <p class="small text-muted">إدارة متكاملة</p>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="dashboard.html">
                    <i class="bi bi-speedometer2 me-2"></i>لوحة التحكم
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="vehicles.html">
                    <i class="bi bi-car-front me-2"></i>العربيات
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="users.html">
                    <i class="bi bi-people me-2"></i>المستخدمين
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="spare-parts.html">
                    <i class="bi bi-gear me-2"></i>قطع الغيار
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="maintenance.html">
                    <i class="bi bi-tools me-2"></i>الصيانة
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="add-maintenance.html">
                    <i class="bi bi-plus-circle me-2"></i>إضافة صيانة
                </a>
            </li>
        </ul>
    </div>

    <!-- Navbar -->
    <nav class="navbar navbar-light bg-light fixed-top">
        <div class="container-fluid">
            <h5 class="mb-0">مرحباً بك في لوحة التحكم</h5>
            <button class="btn btn-outline-danger btn-sm" onclick="logout()">
                <i class="bi bi-box-arrow-right me-2"></i>تسجيل خروج
            </button>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <h2 class="fw-bold mb-4">
            <i class="bi bi-speedometer2 text-primary me-2"></i>لوحة التحكم
        </h2>

        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="card stats-card">
                    <div class="card-body p-4">
                        <div class="rounded d-inline-flex p-3 mb-3" style="background-color: #0d6efd;">
                            <i class="bi bi-car-front text-white fs-2"></i>
                        </div>
                        <h2 class="fw-bold">48</h2>
                        <p class="text-muted mb-0">إجمالي العربيات</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card stats-card">
                    <div class="card-body p-4">
                        <div class="rounded d-inline-flex p-3 mb-3" style="background-color: #198754;">
                            <i class="bi bi-people text-white fs-2"></i>
                        </div>
                        <h2 class="fw-bold">125</h2>
                        <p class="text-muted mb-0">المستخدمين</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card stats-card">
                    <div class="card-body p-4">
                        <div class="rounded d-inline-flex p-3 mb-3" style="background-color: #ffc107;">
                            <i class="bi bi-tools text-white fs-2"></i>
                        </div>
                        <h2 class="fw-bold">32</h2>
                        <p class="text-muted mb-0">عمليات الصيانة</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card stats-card">
                    <div class="card-body p-4">
                        <div class="rounded d-inline-flex p-3 mb-3" style="background-color: #dc3545;">
                            <i class="bi bi-gear text-white fs-2"></i>
                        </div>
                        <h2 class="fw-bold">256</h2>
                        <p class="text-muted mb-0">قطع الغيار</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>إجراءات سريعة</h5>
                    </div>
                    <div class="card-body">
                        <a href="vehicles.html" class="btn btn-outline-primary w-100 text-start mb-2">
                            <i class="bi bi-car-front-fill me-2"></i>إضافة عربية جديدة
                        </a>
                        <a href="users.html" class="btn btn-outline-success w-100 text-start mb-2">
                            <i class="bi bi-person-plus-fill me-2"></i>إضافة مستخدم
                        </a>
                        <a href="spare-parts.html" class="btn btn-outline-danger w-100 text-start mb-2">
                            <i class="bi bi-gear-fill me-2"></i>إضافة قطعة غيار
                        </a>
                        <a href="add-maintenance.html" class="btn btn-outline-warning w-100 text-start">
                            <i class="bi bi-tools me-2"></i>إضافة عملية صيانة
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="bi bi-filter-circle-fill text-info me-2"></i>فلترة حالة الصيانة</h5>
                    </div>
                    <div class="card-body">
                        <a href="maintenance.html?filter=pending" class="btn btn-outline-warning w-100 mb-2 d-flex justify-content-between">
                            <span><i class="bi bi-clock-history me-2"></i>معلقة</span>
                            <span class="badge bg-warning text-dark">12</span>
                        </a>
                        <a href="maintenance.html?filter=in_progress" class="btn btn-outline-primary w-100 mb-2 d-flex justify-content-between">
                            <span><i class="bi bi-arrow-repeat me-2"></i>قيد التنفيذ</span>
                            <span class="badge bg-primary">8</span>
                        </a>
                        <a href="maintenance.html?filter=done" class="btn btn-outline-success w-100 d-flex justify-content-between">
                            <span><i class="bi bi-check-circle me-2"></i>مكتملة</span>
                            <span class="badge bg-success">45</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/navigation.js"></script>
</body>
</html>
