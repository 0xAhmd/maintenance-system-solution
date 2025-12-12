<?php include "../auth_admin.php"; include "../connection.php"; include "../partials/header.php"; include "../partials/navbar.php"; ?>

<div class="container mt-4">
  <h2 class="page-title">
    <i class="bi bi-car-front me-2"></i>إدارة العربيات
  </h2>

  <?php
  // Display success or error messages from URL parameters
  if (isset($_GET['success']) && $_GET['success'] == 'added') {
      echo "<div class='alert alert-success'>تم إضافة العربية بنجاح</div>";
  }
  if (isset($_GET['error']) && $_GET['error'] == 'duplicate') {
      echo "<div class='alert alert-danger'>عربية موجودة بالفعل! الرجاء ادخال عربية جديده برقم لوحة مختلف</div>";
  }
  if (isset($_GET['success']) && $_GET['success'] == 'deleted') {
      echo "<div class='alert alert-success'>تم حذف العربية بنجاح</div>";
  }

  // Handle add vehicle
  if (isset($_POST['add_vehicle'])) {
      $model = $conn->real_escape_string($_POST['model']);
      $plate = $conn->real_escape_string($_POST['plate']);
      
      // Check if plate number already exists
      $check_stmt = $conn->prepare("SELECT vehicle_id FROM vehicle WHERE plate_number = ?");
      $check_stmt->bind_param("s", $plate);
      $check_stmt->execute();
      $check_stmt->store_result();
      
      if ($check_stmt->num_rows > 0) {
          // Plate number already exists
          $check_stmt->close();
          header("Location: vehicles.php?error=duplicate");
          exit();
      }
      $check_stmt->close();
      
      // Insert new vehicle
      $stmt = $conn->prepare("INSERT INTO vehicle (model, plate_number) VALUES (?,?)");
      $stmt->bind_param("ss", $model, $plate);
      
      if ($stmt->execute()) {
          $stmt->close();
          // Redirect to prevent form resubmission
          header("Location: vehicles.php?success=added");
          exit();
      } else {
          $stmt->close();
          header("Location: vehicles.php?error=unknown");
          exit();
      }
  }

  // Handle delete vehicle
  if (isset($_GET['delete'])) {
      $id = intval($_GET['delete']);
      $conn->query("DELETE FROM vehicle WHERE vehicle_id=$id");
      // Redirect after delete
      header("Location: vehicles.php?success=deleted");
      exit();
  }

  // Fetch all vehicles
  $res = $conn->query("SELECT * FROM vehicle");
  ?>

  <div class="card">
    <div class="card-header">
      <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>قائمة العربيات</h5>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover table-bordered">
          <thead>
            <tr>
              <th>#</th>
              <th><i class="bi bi-car-front me-1"></i>الموديل</th>
              <th><i class="bi bi-123 me-1"></i>رقم اللوحة</th>
              <th><i class="bi bi-gear me-1"></i>الإجراءات</th>
            </tr>
          </thead>
          <tbody>
            <?php while($r = $res->fetch_assoc()): ?>
              <tr>
                <td><?=$r['vehicle_id']?></td>
                <td><?=htmlspecialchars($r['model'])?></td>
                <td><span class="badge bg-info"><?=htmlspecialchars($r['plate_number'])?></span></td>
                <td>
                  <a class="btn btn-sm btn-danger" href="?delete=<?=$r['vehicle_id']?>" onclick="return confirm('هل أنت متأكد من حذف هذه العربية؟')">
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
      <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>إضافة عربية جديدة</h5>
    </div>
    <div class="card-body">
      <form method="POST" class="row g-3">
        <div class="col-md-4">
          <label class="form-label">الموديل</label>
          <input name="model" class="form-control" placeholder="مثال: تويوتا كامري" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">رقم اللوحة</label>
          <input name="plate" class="form-control" placeholder="مثال: أ ب ج 1234" required>
        </div>
        <div class="col-md-4 d-flex align-items-end">
          <button name="add_vehicle" class="btn btn-success w-100">
            <i class="bi bi-plus-circle me-2"></i>إضافة
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include "../partials/footer.php"; ?>