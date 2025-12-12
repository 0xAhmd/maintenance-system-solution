<?php include "../auth_admin.php"; ?>
<?php include "../connection.php"; ?>
<?php include "../partials/header.php"; include "../partials/navbar.php"; ?>

<div class="container mt-4">
  <h2 class="page-title">
    <i class="bi bi-people me-2"></i>إدارة المستخدمين
  </h2>

  <?php
  // ============================
  // CREATE USER
  // ============================
  if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {

      $name = trim($_POST['name']);
      $email = trim($_POST['email']);
      $password = $_POST['password'];
      $role = $_POST['role'];
      $phone = trim($_POST['phone']);

      // convert empty to NULL
      $center_id = empty($_POST['center_id']) ? NULL : intval($_POST['center_id']);
      $vehicle_id = empty($_POST['vehicle_id']) ? NULL : intval($_POST['vehicle_id']);

      // Basic validation
      if (empty($name) || empty($email) || empty($password)) {
          echo "<div class='alert alert-danger'>Name, email, and password are required</div>";
      } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
          echo "<div class='alert alert-danger'>Invalid email format</div>";
      } else {

          // 1) ✅ Check email duplication
          $email_check = $conn->prepare("SELECT 1 FROM user WHERE email = ?");
          $email_check->bind_param("s", $email);
          $email_check->execute();
          $email_check->store_result();

          if ($email_check->num_rows > 0) {
              echo "<div class='alert alert-danger'>هذا البريد الإلكتروني مستخدم بالفعل</div>";
              $email_check->close();
          } else {
              $email_check->close();

              // 2) ✅ Check if vehicle is already assigned to another user
              if (!empty($vehicle_id)) {
                  $vehicle_check = $conn->prepare("SELECT u.name, u.email FROM user u WHERE u.vehicle_id = ?");
                  $vehicle_check->bind_param("i", $vehicle_id);
                  $vehicle_check->execute();
                  $vehicle_result = $vehicle_check->get_result();

                  if ($vehicle_result->num_rows > 0) {
                      $existing_user = $vehicle_result->fetch_assoc();
                      echo "<div class='alert alert-danger'>This vehicle is already assigned to: <strong>" . htmlspecialchars($existing_user['name']) . "</strong> (" . htmlspecialchars($existing_user['email']) . ")</div>";
                      $vehicle_check->close();
                      goto skip_insert;
                  }
                  $vehicle_check->close();

                  // 3) ✅ Check if vehicle exists
                  $vehicle_exists = $conn->prepare("SELECT 1 FROM vehicle WHERE vehicle_id = ?");
                  $vehicle_exists->bind_param("i", $vehicle_id);
                  $vehicle_exists->execute();
                  $vehicle_exists->store_result();

                  if ($vehicle_exists->num_rows == 0) {
                      echo "<div class='alert alert-danger'>Vehicle ID does not exist. Please select a valid vehicle.</div>";
                      $vehicle_exists->close();
                      goto skip_insert;
                  }
                  $vehicle_exists->close();
              }

              // 4) Allow only one admin
              if ($role == 'admin') {
                  $admin_check = $conn->prepare("SELECT COUNT(*) AS cnt FROM user WHERE role='admin'");
                  $admin_check->execute();
                  $result = $admin_check->get_result();
                  $cnt = $result->fetch_assoc()['cnt'];
                  $admin_check->close();

                  if ($cnt > 0) {
                      echo "<div class='alert alert-danger'>مسموح Admin واحد فقط</div>";
                      goto skip_insert;
                  }
              }

              // 5) ✅ Hash password before storing
              $hashed_password = password_hash($password, PASSWORD_DEFAULT);

              // 6) ✅ Insert user
              $stmt = $conn->prepare("
                INSERT INTO user (name, email, password, role, phone, center_id, vehicle_id)
                VALUES (?, ?, ?, ?, ?, ?, ?)
              ");

              $stmt->bind_param("sssssii",
                  $name, $email, $hashed_password, $role, $phone, $center_id, $vehicle_id
              );

              // ✅ Error handling
              if ($stmt->execute()) {
                  echo "<div class='alert alert-success'>تم إضافة المستخدم بنجاح</div>";
              } else {
                  echo "<div class='alert alert-danger'>حدث خطأ أثناء الإضافة: " . htmlspecialchars($stmt->error) . "</div>";
              }

              $stmt->close();
          }
      }

      skip_insert:
  }

  // ============================
  // DELETE USER
  // ============================
  if (isset($_GET['delete'])) {
      $id = intval($_GET['delete']);
      
      // ✅ Use prepared statement
      $stmt = $conn->prepare("DELETE FROM user WHERE user_id = ?");
      $stmt->bind_param("i", $id);
      
      if ($stmt->execute()) {
          if ($stmt->affected_rows > 0) {
              echo "<div class='alert alert-success'>User deleted successfully</div>";
          } else {
              echo "<div class='alert alert-warning'>User not found</div>";
          }
      } else {
          echo "<div class='alert alert-danger'>Error deleting user</div>";
      }
      
      $stmt->close();
  }

  // Load centers
  $centers = $conn->query("SELECT center_id, location FROM service_center");

  // Load vehicles (show which ones are available)
  $vehicles = $conn->query("
    SELECT 
        v.vehicle_id, 
        v.model, 
        v.plate_number,
        u.name AS assigned_to
    FROM vehicle v
    LEFT JOIN user u ON v.vehicle_id = u.vehicle_id
    ORDER BY v.vehicle_id DESC
  ");

  // Load users
  $users = $conn->query("
    SELECT 
        u.*, 
        s.location AS center_name, 
        v.model AS vehicle_model,
        v.plate_number,
        (
            SELECT mr.date 
            FROM maintenance_record mr
            WHERE mr.vehicle_id = v.vehicle_id
            ORDER BY mr.date DESC
            LIMIT 1
        ) AS last_maintenance_date
    FROM user u
    LEFT JOIN service_center s ON u.center_id = s.center_id
    LEFT JOIN vehicle v ON u.vehicle_id = v.vehicle_id
    ORDER BY u.user_id DESC
  ");

  // ✅ Check if query succeeded
  if (!$users) {
      echo "<div class='alert alert-danger'>Error loading users: " . $conn->error . "</div>";
      exit;
  }
 ?>

<div class="card">
  <div class="card-header">
    <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>قائمة المستخدمين</h5>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover table-bordered">
        <thead>
          <tr>
            <th>#</th>
            <th><i class="bi bi-person me-1"></i>الاسم</th>
            <th><i class="bi bi-envelope me-1"></i>البريد</th>
            <th><i class="bi bi-shield-check me-1"></i>الدور</th>
            <th><i class="bi bi-telephone me-1"></i>الهاتف</th>
            <th><i class="bi bi-building me-1"></i>المركز</th>
            <th><i class="bi bi-car-front me-1"></i>العربية</th>
            <th><i class="bi bi-123 me-1"></i>اللوحة</th>
            <th><i class="bi bi-calendar-check me-1"></i>آخر صيانة</th>
            <th><i class="bi bi-gear me-1"></i>الإجراءات</th>
          </tr>
        </thead>
        <tbody>
          <?php if($users->num_rows == 0): ?>
            <tr>
              <td colspan="10" class="text-center text-muted py-4">
                <i class="bi bi-inbox" style="font-size: 40px; display: block; margin-bottom: 10px;"></i>
                لا يوجد مستخدمون
              </td>
            </tr>
          <?php endif; ?>

          <?php while($r = $users->fetch_assoc()): ?>
            <tr>
              <td><?=$r['user_id']?></td>
              <td><strong><?=htmlspecialchars($r['name'])?></strong></td>
              <td><?=htmlspecialchars($r['email'])?></td>
              <td>
                <span class="badge bg-<?= $r['role']=='admin' ? 'danger' : ($r['role']=='mechanic' ? 'warning' : 'info') ?>">
                  <?= $r['role']=='admin' ? 'مدير' : ($r['role']=='mechanic' ? 'ميكانيكي' : 'سائق') ?>
                </span>
              </td>
              <td><?=htmlspecialchars($r['phone'] ?: '-')?></td>
              <td><?=htmlspecialchars($r['center_name'] ?? '-')?></td>
              <td><?=htmlspecialchars($r['vehicle_model'] ?? '-')?></td>
              <td><?=htmlspecialchars($r['plate_number'] ?? '-')?></td>
              <td>
                <?php 
                  if ($r['last_maintenance_date']) {
                      echo htmlspecialchars($r['last_maintenance_date']);
                  } else {
                      echo "<span class='text-muted'>لا يوجد</span>";
                  }
                ?>
              </td>
              <td>
                <a class="btn btn-sm btn-danger" href="?delete=<?=$r['user_id']?>" onclick="return confirm('هل أنت متأكد من حذف هذا المستخدم؟')">
                  <i class="bi bi-trash me-1"></i>حذف
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>


  <div class="card mt-4" id="add">
    <div class="card-header">
      <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>إضافة مستخدم جديد</h5>
    </div>
    <div class="card-body">
      <form method="POST">
        <div class="row mb-3">
          <div class="col-md-3">
            <label class="form-label">الاسم *</label>
            <input required name="name" class="form-control" placeholder="اسم المستخدم">
          </div>

          <div class="col-md-3">
            <label class="form-label">البريد الإلكتروني *</label>
            <input required name="email" type="email" class="form-control" placeholder="example@email.com">
          </div>

          <div class="col-md-2">
            <label class="form-label">كلمة المرور *</label>
            <input required name="password" type="password" class="form-control" placeholder="كلمة المرور">
          </div>

          <div class="col-md-2">
            <label class="form-label">الدور *</label>
            <select name="role" class="form-select">
              <option value="driver">سائق</option>
              <option value="mechanic">ميكانيكي</option>
              <option value="admin">مدير</option>
            </select>
          </div>

          <div class="col-md-2">
            <label class="form-label">الهاتف</label>
            <input name="phone" class="form-control" placeholder="رقم الهاتف">
          </div>
        </div>

        <div class="row">
          <div class="col-md-3">
            <label class="form-label">مركز الخدمة (اختياري)</label>
            <select name="center_id" class="form-select">
              <option value="">اختر مركز الخدمة</option>
              <?php 
              $centers->data_seek(0);
              while ($c = $centers->fetch_assoc()): 
              ?>
                <option value="<?=$c['center_id']?>"><?=htmlspecialchars($c['location'])?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">تعيين عربية (اختياري)</label>
            <select name="vehicle_id" class="form-select">
              <option value="">اختر عربية</option>
              <?php while ($v = $vehicles->fetch_assoc()): ?>
                <option value="<?=$v['vehicle_id']?>" <?= $v['assigned_to'] ? 'disabled' : '' ?>>
                  <?=htmlspecialchars($v['model'])?> (<?=htmlspecialchars($v['plate_number'])?>)
                  <?= $v['assigned_to'] ? ' - معينة لـ: ' . htmlspecialchars($v['assigned_to']) : '' ?>
                </option>
              <?php endwhile; ?>
            </select>
            <small class="text-muted">فقط العربيات غير المعينة متاحة</small>
          </div>

          <div class="col-md-3 d-flex align-items-end">
            <button name="add_user" class="btn btn-primary w-100">
              <i class="bi bi-plus-circle me-2"></i>إضافة مستخدم
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

</div>

<?php include "../partials/footer.php"; ?>