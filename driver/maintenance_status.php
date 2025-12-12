<?php include "../auth_driver.php"; include "../connection.php"; include "../partials/header.php"; include "../partials/navbar.php"; ?>

<div class="container mt-4">
  <h2 class="page-title">
    <i class="bi bi-clipboard-check me-2"></i>حالة صيانة عربيتي
  </h2>

  <?php
  $vehicle_id = $_SESSION['user']['vehicle_id'];
  $data = $conn->query("SELECT * FROM maintenance_record WHERE vehicle_id=$vehicle_id ORDER BY date DESC");
  ?>

  <div class="card">
    <div class="card-header">
      <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>سجلات الصيانة</h5>
    </div>
    <div class="card-body">
      <?php if ($data->num_rows == 0): ?>
        <div class="alert alert-info text-center">
          <i class="bi bi-inbox" style="font-size: 50px; display: block; margin-bottom: 15px;"></i>
          <p class="mb-0">لا توجد سجلات صيانة لعربيتك</p>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th><i class="bi bi-calendar me-1"></i>التاريخ</th>
                <th><i class="bi bi-file-text me-1"></i>الوصف</th>
                <th><i class="bi bi-info-circle me-1"></i>الحالة</th>
              </tr>
            </thead>
            <tbody>
              <?php while($row = $data->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($row['date']) ?></td>
                <td><?= htmlspecialchars($row['description'] ?: 'لا يوجد وصف') ?></td>
                <td>
                  <?php if ($row['status'] == 'done'): ?>
                      <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>جاهزة للاستلام</span>
                  <?php elseif($row['status'] == 'in_progress'): ?>
                      <span class="badge bg-warning"><i class="bi bi-arrow-repeat me-1"></i>قيد الصيانة</span>
                  <?php else: ?>
                      <span class="badge bg-secondary"><i class="bi bi-clock-history me-1"></i>معلقة</span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include "../partials/footer.php"; ?>
