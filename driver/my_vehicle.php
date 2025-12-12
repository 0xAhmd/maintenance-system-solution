<?php 
include "../auth_driver.php"; 
include "../connection.php"; 
include "../partials/header.php"; 
include "../partials/navbar.php"; 
?>

<div class="container mt-4">
  <h2 class="page-title">
    <i class="bi bi-car-front me-2"></i>لوحة تحكم عربيتي
  </h2>

  <?php
  $user_id = $_SESSION['user']['user_id'];
  $vehicle_id = $_SESSION['user']['vehicle_id'];
  
  // Get vehicle details
  $vehicle = $conn->query("SELECT * FROM vehicle WHERE vehicle_id=$vehicle_id")->fetch_assoc();
  
  // Get all maintenance records for this vehicle
  $maintenance_records = $conn->query("
    SELECT 
      m.*,
      u.name AS mechanic_name,
      sc.location AS center_location
    FROM maintenance_record m
    LEFT JOIN works_on w ON m.maintenance_id = w.maintenance_id
    LEFT JOIN user u ON w.mechanic_id = u.user_id
    LEFT JOIN service_center sc ON m.center_id = sc.center_id
    WHERE m.vehicle_id = $vehicle_id
    ORDER BY m.date DESC
  ");
  
  // Get statistics
  $total_maintenance = $conn->query("SELECT COUNT(*) AS count FROM maintenance_record WHERE vehicle_id=$vehicle_id")->fetch_assoc()['count'];
  $total_cost = $conn->query("SELECT SUM(cost) AS total FROM maintenance_record WHERE vehicle_id=$vehicle_id")->fetch_assoc()['total'];
  $pending_count = $conn->query("SELECT COUNT(*) AS count FROM maintenance_record WHERE vehicle_id=$vehicle_id AND status='pending'")->fetch_assoc()['count'];
  $last_maintenance = $conn->query("SELECT date FROM maintenance_record WHERE vehicle_id=$vehicle_id ORDER BY date DESC LIMIT 1")->fetch_assoc();
  ?>

  <!-- Vehicle Info Card -->
  <div class="card mb-4">
    <div class="card-header bg-primary text-white">
      <h5 class="mb-0">Vehicle Information</h5>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <p><strong>Model:</strong> <?= htmlspecialchars($vehicle['model']) ?></p>
          <p><strong>Plate Number:</strong> <?= htmlspecialchars($vehicle['plate_number']) ?></p>
        </div>
        <div class="col-md-6">
          <p><strong>Total Maintenance Records:</strong> <?= $total_maintenance ?></p>
          <p><strong>Last Maintenance:</strong> <?= $last_maintenance ? htmlspecialchars($last_maintenance['date']) : 'No maintenance yet' ?></p>
        </div>
      </div>
    </div>
  </div>

  <!-- Statistics Cards -->
  <div class="row mb-4">
    <div class="col-md-4">
      <div class="card text-center p-3 bg-warning text-white">
        <h4><?= $pending_count ?></h4>
        <p class="mb-0">Pending Maintenance</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card text-center p-3 bg-info text-white">
        <h4><?= $total_maintenance ?></h4>
        <p class="mb-0">Total Maintenance</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card text-center p-3 bg-success text-white">
        <h4><?= number_format($total_cost ?? 0, 2) ?> EGP</h4>
        <p class="mb-0">Total Cost</p>
      </div>
    </div>
  </div>

  <!-- Maintenance History -->
  <h4 class="mt-4 mb-3">Maintenance History & Invoices</h4>

  <?php if ($maintenance_records->num_rows == 0): ?>
    <div class="alert alert-info">No maintenance records found for your vehicle.</div>
  <?php else: ?>
    
    <?php while($record = $maintenance_records->fetch_assoc()): ?>
      <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
          <div>
            <strong>Maintenance #<?= $record['maintenance_id'] ?></strong> - 
            <?= date('d/m/Y', strtotime($record['date'])) ?>
          </div>
          <div>
            <?php
            $badge_class = '';
            $status_text = '';
            switch($record['status']) {
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
          </div>
        </div>
        
        <div class="card-body">
          <div class="row mb-3">
            <div class="col-md-6">
              <p><strong>Description:</strong> <?= htmlspecialchars($record['description'] ?: 'No description') ?></p>
              <p><strong>Mechanic:</strong> <?= htmlspecialchars($record['mechanic_name'] ?? 'Not assigned') ?></p>
              <p><strong>Service Center:</strong> <?= htmlspecialchars($record['center_location'] ?? 'N/A') ?></p>
            </div>
            <div class="col-md-6">
              <p><strong>Kilometers:</strong> <?= isset($record['kilometers']) && $record['kilometers'] !== null ? number_format($record['kilometers']) . ' km' : 'Not recorded' ?></p>
              <p><strong>Cost:</strong> <span class="text-success fs-5"><?= number_format($record['cost'], 2) ?> EGP</span></p>
            </div>
          </div>

          <?php
          // Get parts used in this maintenance
          $parts_query = $conn->query("
            SELECT 
              sp.name,
              sp.price,
              mp.quantity,
              (sp.price * mp.quantity) AS subtotal
            FROM maintenance_parts mp
            JOIN spare_part sp ON mp.part_id = sp.part_id
            WHERE mp.maintenance_id = {$record['maintenance_id']}
          ");
          ?>

          <?php if ($parts_query->num_rows > 0): ?>
            <h6 class="mt-3">Parts Used (Invoice Details):</h6>
            <table class="table table-sm table-bordered">
              <thead class="table-light">
                <tr>
                  <th>Part Name</th>
                  <th>Price</th>
                  <th>Quantity</th>
                  <th>Subtotal</th>
                </tr>
              </thead>
              <tbody>
                <?php 
                $parts_total = 0;
                while($part = $parts_query->fetch_assoc()): 
                  $parts_total += $part['subtotal'];
                ?>
                  <tr>
                    <td><?= htmlspecialchars($part['name']) ?></td>
                    <td><?= number_format($part['price'], 2) ?> EGP</td>
                    <td><?= $part['quantity'] ?></td>
                    <td><?= number_format($part['subtotal'], 2) ?> EGP</td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
              <tfoot class="table-light">
                <tr>
                  <td colspan="3" class="text-end"><strong>Parts Total:</strong></td>
                  <td><strong><?= number_format($parts_total, 2) ?> EGP</strong></td>
                </tr>
                <tr>
                  <td colspan="3" class="text-end"><strong>Total Cost:</strong></td>
                  <td><strong><?= number_format($record['cost'], 2) ?> EGP</strong></td>
                </tr>
              </tfoot>
            </table>
          <?php else: ?>
            <p class="text-muted">No parts used in this maintenance.</p>
          <?php endif; ?>

        </div>
      </div>
    <?php endwhile; ?>

  <?php endif; ?>

</div>

<?php include "../partials/footer.php"; ?>