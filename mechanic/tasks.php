<?php 
include "../auth_mechanic.php"; 
include "../connection.php"; 
include "../partials/header.php"; 
include "../partials/navbar.php"; 
?>

<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="page-title mb-0">
      <i class="bi bi-list-task me-2"></i>مهام الصيانة الخاصة بي
    </h2>
    <div class="alert alert-success mb-0 py-2 px-3">
      <i class="bi bi-pencil-square me-2"></i><small>يمكنك تحديث حالة المهام من هنا</small>
    </div>
  </div>

  <?php
  // Get logged-in mechanic ID
  $mechanic_id = $_SESSION['user']['user_id'];
  
  // Handle status update from POST
  if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
      $maintenance_id = intval($_POST['maintenance_id']);
      $new_status = $_POST['status'];
      
      // Validate status
      $allowed_statuses = ['pending', 'in_progress', 'done'];
      if (!in_array($new_status, $allowed_statuses)) {
          echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                  <i class='bi bi-exclamation-triangle-fill me-2'></i>حالة غير صالحة
                  <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                </div>";
      } else {
          // Verify this maintenance job belongs to this mechanic
          $check_stmt = $conn->prepare("
              SELECT 1 FROM works_on 
              WHERE maintenance_id = ? AND mechanic_id = ?
          ");
          $check_stmt->bind_param("ii", $maintenance_id, $mechanic_id);
          $check_stmt->execute();
          $check_stmt->store_result();
          
          if ($check_stmt->num_rows > 0) {
              // Update status
              $update_stmt = $conn->prepare("
                  UPDATE maintenance_record 
                  SET status = ? 
                  WHERE maintenance_id = ?
              ");
              $update_stmt->bind_param("si", $new_status, $maintenance_id);
              
              if ($update_stmt->execute()) {
                  $status_names = [
                      'pending' => 'قيد الانتظار',
                      'in_progress' => 'جاري التنفيذ',
                      'done' => 'تم الانتهاء'
                  ];
                  echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                          <i class='bi bi-check-circle-fill me-2'></i>تم تحديث الحالة بنجاح إلى: <strong>" . $status_names[$new_status] . "</strong>
                          <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                        </div>";
                  // Redirect to prevent form resubmission
                  header("Location: tasks.php?updated=1");
                  exit();
              } else {
                  echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                          <i class='bi bi-x-circle-fill me-2'></i>حدث خطأ أثناء تحديث الحالة
                          <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                        </div>";
              }
              $update_stmt->close();
          } else {
              echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                      <i class='bi bi-shield-exclamation me-2'></i>هذه المهمة غير معينة لك
                      <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                    </div>";
          }
          $check_stmt->close();
      }
  }
  
  // Show success message if redirected after update
  if (isset($_GET['updated']) && $_GET['updated'] == '1') {
      echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
              <i class='bi bi-check-circle-fill me-2'></i>تم تحديث الحالة بنجاح
              <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            </div>";
  }

  // Fetch all maintenance tasks assigned to this mechanic
  // Check if kilometers column exists first
  $check_km = $conn->query("SHOW COLUMNS FROM maintenance_record LIKE 'kilometers'");
  $has_kilometers = $check_km->num_rows > 0;
  
  if ($has_kilometers) {
      $query = "
          SELECT 
              m.maintenance_id,
              m.date,
              m.description,
              m.cost,
              m.status,
              m.kilometers,
              v.model AS vehicle_model,
              v.plate_number,
              v.vehicle_id
          FROM maintenance_record m
          JOIN works_on w ON m.maintenance_id = w.maintenance_id
          JOIN vehicle v ON m.vehicle_id = v.vehicle_id
          WHERE w.mechanic_id = ?
          ORDER BY m.date DESC
      ";
  } else {
      // Query without kilometers column
      $query = "
          SELECT 
              m.maintenance_id,
              m.date,
              m.description,
              m.cost,
              m.status,
              NULL AS kilometers,
              v.model AS vehicle_model,
              v.plate_number,
              v.vehicle_id
          FROM maintenance_record m
          JOIN works_on w ON m.maintenance_id = w.maintenance_id
          JOIN vehicle v ON m.vehicle_id = v.vehicle_id
          WHERE w.mechanic_id = ?
          ORDER BY m.date DESC
      ";
  }
  
  $stmt = $conn->prepare($query);
  $stmt->bind_param("i", $mechanic_id);
  $stmt->execute();
  $result = $stmt->get_result();
  ?>

  <?php if ($result->num_rows == 0): ?>
    <div class="alert alert-info text-center mt-3">
      <i class="bi bi-inbox" style="font-size: 50px; display: block; margin-bottom: 15px;"></i>
      <p class="mb-0">لا توجد مهام صيانة معينة لك بعد</p>
    </div>
  <?php else: ?>
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>قائمة المهام</h5>
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
                <th><i class="bi bi-file-text me-1"></i>الوصف</th>
                <th><i class="bi bi-speedometer2 me-1"></i>الكيلومترات</th>
                <th><i class="bi bi-currency-pound me-1"></i>التكلفة</th>
                <th><i class="bi bi-info-circle me-1"></i>الحالة</th>
                <th><i class="bi bi-gear me-1"></i>الإجراءات</th>
              </tr>
            </thead>
            <tbody>
              <?php while($job = $result->fetch_assoc()): ?>
                <tr>
                  <td><strong>#<?= $job['maintenance_id'] ?></strong></td>
                  <td><?= htmlspecialchars($job['date']) ?></td>
                  <td><?= htmlspecialchars($job['vehicle_model']) ?></td>
                  <td><span class="badge bg-info"><?= htmlspecialchars($job['plate_number']) ?></span></td>
                  <td><?= htmlspecialchars($job['description'] ?: 'لا يوجد وصف') ?></td>
                  <td><?= isset($job['kilometers']) && $job['kilometers'] !== null ? number_format($job['kilometers']) . ' كم' : '-' ?></td>
                  <td><span class="badge bg-success"><?= number_format($job['cost'], 2) ?> جنيه</span></td>
                  <td>
                    <?php
                    $status_class = '';
                    $status_text = '';
                    switch($job['status']) {
                        case 'pending':
                            $status_class = 'bg-secondary';
                            $status_text = 'قيد الانتظار';
                            break;
                        case 'in_progress':
                            $status_class = 'bg-warning';
                            $status_text = 'جاري التنفيذ';
                            break;
                        case 'done':
                            $status_class = 'bg-success';
                            $status_text = 'تم الانتهاء';
                            break;
                    }
                    ?>
                    <span class="badge <?= $status_class ?>"><?= $status_text ?></span>
                  </td>
                  <td>
                    <form method="POST" class="d-flex gap-2" onsubmit="return confirm('هل أنت متأكد من تحديث حالة هذه المهمة؟')">
                      <input type="hidden" name="maintenance_id" value="<?= $job['maintenance_id'] ?>">
                      <select name="status" class="form-select form-select-sm" required>
                        <option value="pending" <?= $job['status'] == 'pending' ? 'selected' : '' ?>>قيد الانتظار</option>
                        <option value="in_progress" <?= $job['status'] == 'in_progress' ? 'selected' : '' ?>>جاري التنفيذ</option>
                        <option value="done" <?= $job['status'] == 'done' ? 'selected' : '' ?>>تم الانتهاء</option>
                      </select>
                      <button type="submit" name="update_status" class="btn btn-primary btn-sm" title="تحديث حالة المهمة">
                        <i class="bi bi-check-circle me-1"></i>تحديث
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <?php $stmt->close(); ?>

  <hr class="my-5">
  
  <h4 class="section-title">
    <i class="bi bi-bar-chart me-2"></i>ملخص الحالة
  </h4>
  <?php
  // Get status counts for this mechanic
  $summary_query = "
      SELECT 
          m.status,
          COUNT(*) as count
      FROM maintenance_record m
      JOIN works_on w ON m.maintenance_id = w.maintenance_id
      WHERE w.mechanic_id = ?
      GROUP BY m.status
  ";
  $summary_stmt = $conn->prepare($summary_query);
  $summary_stmt->bind_param("i", $mechanic_id);
  $summary_stmt->execute();
  $summary_result = $summary_stmt->get_result();
  
  $status_counts = [
      'pending' => 0,
      'in_progress' => 0,
      'done' => 0
  ];
  
  while($row = $summary_result->fetch_assoc()) {
      $status_counts[$row['status']] = $row['count'];
  }
  $summary_stmt->close();
  ?>
  
  <div class="row">
    <div class="col-md-4 mb-3">
      <div class="card stats-card text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="card-body">
          <i class="bi bi-clock-history" style="font-size: 40px; opacity: 0.8;"></i>
          <h4 class="mt-3"><?= $status_counts['pending'] ?></h4>
          <p class="mb-0">مهام قيد الانتظار</p>
        </div>
      </div>
    </div>
    <div class="col-md-4 mb-3">
      <div class="card stats-card text-white" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
        <div class="card-body">
          <i class="bi bi-arrow-repeat" style="font-size: 40px; opacity: 0.8;"></i>
          <h4 class="mt-3"><?= $status_counts['in_progress'] ?></h4>
          <p class="mb-0">جاري التنفيذ</p>
        </div>
      </div>
    </div>
    <div class="col-md-4 mb-3">
      <div class="card stats-card text-white" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
        <div class="card-body">
          <i class="bi bi-check-circle" style="font-size: 40px; opacity: 0.8;"></i>
          <h4 class="mt-3"><?= $status_counts['done'] ?></h4>
          <p class="mb-0">مكتملة</p>
        </div>
      </div>
    </div>
  </div>

</div>

<?php include "../partials/footer.php"; ?>