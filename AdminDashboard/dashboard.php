<?php 
include "../auth_admin.php"; 
include "../connection.php"; 
include "../partials/header.php"; 
include "../partials/navbar.php"; 
?>

<div class="container mt-4">
    <h2 class="page-title">
        <i class="bi bi-speedometer2 me-2"></i>لوحة تحكم الأدمن
    </h2>

    <?php
    // أرقام الإحصائيات
    $vehicles_count = $conn->query("SELECT COUNT(*) AS c FROM vehicle")->fetch_assoc()['c'];
    $users_count = $conn->query("SELECT COUNT(*) AS c FROM user")->fetch_assoc()['c'];
    $parts_count = $conn->query("SELECT COUNT(*) AS c FROM spare_part")->fetch_assoc()['c'];
    $maintenance_count = $conn->query("SELECT COUNT(*) AS c FROM maintenance_record")->fetch_assoc()['c'];
    ?>

    <!-- الإحصائيات -->
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card stats-card text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body">
                    <i class="bi bi-car-front" style="font-size: 40px; opacity: 0.8;"></i>
                    <h4 class="mt-3"><?= $vehicles_count ?></h4>
                    <p class="mb-0">عدد العربيات</p>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card stats-card text-white" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <div class="card-body">
                    <i class="bi bi-people" style="font-size: 40px; opacity: 0.8;"></i>
                    <h4 class="mt-3"><?= $users_count ?></h4>
                    <p class="mb-0">عدد المستخدمين</p>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card stats-card text-white" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body">
                    <i class="bi bi-gear" style="font-size: 40px; opacity: 0.8;"></i>
                    <h4 class="mt-3"><?= $parts_count ?></h4>
                    <p class="mb-0">عدد قطع الغيار</p>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card stats-card text-white" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                <div class="card-body">
                    <i class="bi bi-wrench-adjustable" style="font-size: 40px; opacity: 0.8;"></i>
                    <h4 class="mt-3"><?= $maintenance_count ?></h4>
                    <p class="mb-0">عدد عمليات الصيانة</p>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-5">

    <h4 class="section-title">
        <i class="bi bi-gear-wide-connected me-2"></i>إدارة النظام
    </h4>

    <!-- أزرار Actions -->
    <div class="row">
        <div class="col-md-3 mb-3">
            <a href="vehicles.php" class="btn btn-outline-primary w-100 p-3">
                <i class="bi bi-car-front d-block mb-2" style="font-size: 30px;"></i>
                عرض العربيات
            </a>
        </div>

        <div class="col-md-3 mb-3">
            <a href="users.php" class="btn btn-outline-primary w-100 p-3">
                <i class="bi bi-people d-block mb-2" style="font-size: 30px;"></i>
                عرض المستخدمين
            </a>
        </div>

        <div class="col-md-3 mb-3">
            <a href="spare_parts.php" class="btn btn-outline-primary w-100 p-3">
                <i class="bi bi-gear d-block mb-2" style="font-size: 30px;"></i>
                عرض قطع الغيار
            </a>
        </div>

        <div class="col-md-3 mb-3">
            <a href="maintinance.php" class="btn btn-outline-primary w-100 p-3">
                <i class="bi bi-wrench-adjustable d-block mb-2" style="font-size: 30px;"></i>
                عرض الصيانة
            </a>
        </div>

        <!-- أزرار الإضافة -->
        <div class="col-md-3 mb-3">
            <a href="maintenance_add.php" class="btn btn-success w-100 p-3">
                <i class="bi bi-plus-circle d-block mb-2" style="font-size: 30px;"></i>
                إضافة عملية صيانة
            </a>
        </div>

        <div class="col-md-3 mb-3">
            <a href="vehicles.php#add" class="btn btn-success w-100 p-3">
                <i class="bi bi-plus-circle d-block mb-2" style="font-size: 30px;"></i>
                إضافة عربية
            </a>
        </div>

        <div class="col-md-3 mb-3">
            <a href="users.php#add" class="btn btn-success w-100 p-3">
                <i class="bi bi-plus-circle d-block mb-2" style="font-size: 30px;"></i>
                إضافة مستخدم
            </a>
        </div>

        <div class="col-md-3 mb-3">
            <a href="spare_parts.php#add" class="btn btn-success w-100 p-3">
                <i class="bi bi-plus-circle d-block mb-2" style="font-size: 30px;"></i>
                إضافة قطعة غيار
            </a>
        </div>
    </div>

    <hr class="my-5">

    <h4 class="section-title">
        <i class="bi bi-funnel me-2"></i>عمليات الصيانة (حسب الحالة)
    </h4>

    <div class="row">
        <div class="col-md-4 mb-3">
            <a href="maintinance.php?status=pending" class="btn btn-outline-secondary w-100 p-3">
                <i class="bi bi-clock-history d-block mb-2" style="font-size: 30px;"></i>
                قيد الانتظار
            </a>
        </div>

        <div class="col-md-4 mb-3">
            <a href="maintinance.php?status=in_progress" class="btn btn-outline-warning w-100 p-3">
                <i class="bi bi-arrow-repeat d-block mb-2" style="font-size: 30px;"></i>
                جاري التنفيذ
            </a>
        </div>

        <div class="col-md-4 mb-3">
            <a href="maintinance.php?status=done" class="btn btn-outline-success w-100 p-3">
                <i class="bi bi-check-circle d-block mb-2" style="font-size: 30px;"></i>
                تم الانتهاء
            </a>
        </div>
    </div>

</div>

<?php include "../partials/footer.php"; ?>
