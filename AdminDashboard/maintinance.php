<?php 
include "../auth_admin.php"; 
include "../connection.php"; 
include "../partials/header.php";
include "../partials/navbar.php"; 
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="page-title mb-0">
            <i class="bi bi-wrench-adjustable me-2"></i>سجلات الصيانة
        </h2>
        <div class="alert alert-info mb-0 py-2 px-3">
            <i class="bi bi-eye me-2"></i><small>عرض فقط - الميكانيكيون هم من يقومون بتحديث الحالة</small>
        </div>
    </div>

    <?php
    // Handle status filter from URL
    $status_filter = isset($_GET['status']) ? $_GET['status'] : '';
    $allowed_statuses = ['pending', 'in_progress', 'done'];
    
    // Build query based on filter
    if (!empty($status_filter) && in_array($status_filter, $allowed_statuses)) {
        $stmt = $conn->prepare("
            SELECT m.*, v.model, v.plate_number, u.name AS mechanic_name
            FROM maintenance_record m 
            JOIN vehicle v ON m.vehicle_id = v.vehicle_id
            LEFT JOIN works_on w ON m.maintenance_id = w.maintenance_id
            LEFT JOIN user u ON w.mechanic_id = u.user_id
            WHERE m.status = ?
            ORDER BY m.date DESC
        ");
        $stmt->bind_param("s", $status_filter);
        $stmt->execute();
        $data = $stmt->get_result();
    } else {
        $data = $conn->query("
            SELECT m.*, v.model, v.plate_number, u.name AS mechanic_name
            FROM maintenance_record m 
            JOIN vehicle v ON m.vehicle_id = v.vehicle_id
            LEFT JOIN works_on w ON m.maintenance_id = w.maintenance_id
            LEFT JOIN user u ON w.mechanic_id = u.user_id
            ORDER BY m.date DESC
        ");
    }
    ?>

    <!-- Filter Buttons -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="btn-group w-100" role="group">
            <a href="maintinance.php" class="btn btn-<?= empty($status_filter) ? 'primary' : 'outline-primary' ?>">
                <i class="bi bi-list-ul me-1"></i>الكل
            </a>
            <a href="?status=pending" class="btn btn-<?= $status_filter == 'pending' ? 'secondary' : 'outline-secondary' ?>">
                <i class="bi bi-clock-history me-1"></i>قيد الانتظار
            </a>
            <a href="?status=in_progress" class="btn btn-<?= $status_filter == 'in_progress' ? 'warning' : 'outline-warning' ?>">
                <i class="bi bi-arrow-repeat me-1"></i>جاري التنفيذ
            </a>
            <a href="?status=done" class="btn btn-<?= $status_filter == 'done' ? 'success' : 'outline-success' ?>">
                <i class="bi bi-check-circle me-1"></i>تم الانتهاء
            </a>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>قائمة سجلات الصيانة</h5>
        <small class="text-white-50"><i class="bi bi-info-circle me-1"></i>للعرض فقط</small>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th><i class="bi bi-calendar me-1"></i>التاريخ</th>
                    <th><i class="bi bi-car-front me-1"></i>العربية</th>
                    <th><i class="bi bi-123 me-1"></i>رقم اللوحة</th>
                    <th><i class="bi bi-person-wrench me-1"></i>الميكانيكي</th>
                    <th><i class="bi bi-file-text me-1"></i>الوصف</th>
                    <th><i class="bi bi-currency-pound me-1"></i>التكلفة</th>
                    <th><i class="bi bi-info-circle me-1"></i>الحالة</th>
                    <th><i class="bi bi-speedometer2 me-1"></i>الكيلومترات</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($data->num_rows == 0): ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="bi bi-inbox" style="font-size: 40px; display: block; margin-bottom: 10px;"></i>
                            لا توجد سجلات صيانة
                        </td>
                    </tr>
                <?php endif; ?>

                <?php while($row = $data->fetch_assoc()): ?>
                <tr>
                    <td><strong>#<?= $row['maintenance_id'] ?></strong></td>
                    <td><?= htmlspecialchars($row['date']) ?></td>
                    <td><?= htmlspecialchars($row['model']) ?></td>
                    <td><span class="badge bg-info"><?= htmlspecialchars($row['plate_number']) ?></span></td>
                    <td><?= htmlspecialchars($row['mechanic_name'] ?? 'غير معين') ?></td>
                    <td><?= htmlspecialchars($row['description'] ?: '-') ?></td>
                    <td><span class="badge bg-success"><?= number_format($row['cost'], 2) ?> جنيه</span></td>
                    <td>
                        <?php
                        $badge_class = '';
                        $status_text = '';
                        switch($row['status']) {
                            case 'pending':
                                $badge_class = 'bg-secondary';
                                $status_text = 'قيد الانتظار';
                                break;
                            case 'in_progress':
                                $badge_class = 'bg-warning';
                                $status_text = 'جاري التنفيذ';
                                break;
                            case 'done':
                                $badge_class = 'bg-success';
                                $status_text = 'تم الانتهاء';
                                break;
                        }
                        ?>
                        <span class="badge <?= $badge_class ?>"><?= $status_text ?></span>
                    </td>
                    <td><?= isset($row['kilometers']) && $row['kilometers'] !== null ? number_format($row['kilometers']) . ' كم' : '-' ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="mt-4">
        <a href="maintenance_add.php" class="btn btn-primary btn-lg">
            <i class="bi bi-plus-circle me-2"></i>إضافة سجل صيانة جديد
        </a>
    </div>
</div>

<?php include "../partials/footer.php"; ?>