<?php 
include "../auth_admin.php"; 
include "../connection.php"; 
include "../partials/header.php"; 
include "../partials/navbar.php"; 
?>

<div class="container mt-4">
  <h2 class="page-title">
    <i class="bi bi-plus-circle me-2"></i>إضافة سجل صيانة جديد
  </h2>

  <?php
  // Fetch vehicles, mechanics, parts
  $vehicles = $conn->query("SELECT vehicle_id, model, plate_number FROM vehicle ORDER BY model");
  $mechanics = $conn->query("SELECT user_id, name FROM `user` WHERE role='mechanic' ORDER BY name");
  $parts = $conn->query("SELECT part_id, name, price FROM spare_part ORDER BY name");

  // ✅ Check if queries succeeded
  if (!$vehicles || !$mechanics || !$parts) {
      echo "<div class='alert alert-danger'>Error loading data: " . $conn->error . "</div>";
      exit;
  }

  if ($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['add_maintenance'])) {
      
      $date = trim($_POST['date']);
      $desc = trim($_POST['description']);
      $cost = floatval($_POST['cost']);
      $kilometers = !empty($_POST['kilometers']) ? intval($_POST['kilometers']) : NULL;
      $vehicle_id = intval($_POST['vehicle_id']);
      $center_id = !empty($_POST['center_id']) ? intval($_POST['center_id']) : NULL;
      $mechanic_id = intval($_POST['mechanic_id']);

      // Basic validation
      if (empty($date) || empty($vehicle_id) || empty($mechanic_id)) {
          echo "<div class='alert alert-danger'>Date, vehicle, and mechanic are required</div>";
      } elseif ($cost < 0) {
          echo "<div class='alert alert-danger'>Cost cannot be negative</div>";
      } elseif ($kilometers !== NULL && $kilometers < 0) {
          echo "<div class='alert alert-danger'>Kilometers cannot be negative</div>";
      } else {

          // ✅ Check connection before starting transaction
          if (!$conn->ping()) {
              echo "<div class='alert alert-danger'>Database connection lost. Please try again.</div>";
          } else {

              // ✅ Use transaction for data integrity
              $conn->begin_transaction();

              try {
                  // 1) Insert maintenance_record
                  // Check if kilometers column exists
                  $check_km = $conn->query("SHOW COLUMNS FROM maintenance_record LIKE 'kilometers'");
                  $has_kilometers = $check_km->num_rows > 0;
                  
                  if ($has_kilometers) {
                      $stmt = $conn->prepare("INSERT INTO maintenance_record (date, description, cost, kilometers, vehicle_id, center_id, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
                      $stmt->bind_param("ssdiii", $date, $desc, $cost, $kilometers, $vehicle_id, $center_id);
                  } else {
                      // Insert without kilometers column
                      $stmt = $conn->prepare("INSERT INTO maintenance_record (date, description, cost, vehicle_id, center_id, status) VALUES (?, ?, ?, ?, ?, 'pending')");
                      $stmt->bind_param("ssdii", $date, $desc, $cost, $vehicle_id, $center_id);
                  }
                  
                  if (!$stmt->execute()) {
                      throw new Exception("Failed to insert maintenance record: " . $stmt->error);
                  }
                  
                  $mid = $stmt->insert_id;
                  $stmt->close();

                  // 2) Insert works_on
                  $stmt2 = $conn->prepare("INSERT INTO works_on (maintenance_id, mechanic_id) VALUES (?, ?)");
                  $stmt2->bind_param("ii", $mid, $mechanic_id);
                  
                  if (!$stmt2->execute()) {
                      throw new Exception("Failed to assign mechanic: " . $stmt2->error);
                  }
                  
                  $stmt2->close();

                  // 3) Insert maintenance_parts if any
                  if (!empty($_POST['selected_parts'])) {
                      $selected_parts = json_decode($_POST['selected_parts'], true);
                      
                      if (is_array($selected_parts) && count($selected_parts) > 0) {
                          $pstmt = $conn->prepare("INSERT INTO maintenance_parts (maintenance_id, part_id, quantity) VALUES (?, ?, ?)");
                          
                          foreach($selected_parts as $part) {
                              $pid = intval($part['part_id']);
                              $qty = intval($part['quantity']);
                              
                              if ($qty > 0) {
                                  $pstmt->bind_param("iii", $mid, $pid, $qty);
                                  
                                  if (!$pstmt->execute()) {
                                      throw new Exception("Failed to add part: " . $pstmt->error);
                                  }
                              }
                          }
                          
                          $pstmt->close();
                      }
                  }

                  // ✅ Commit transaction if everything succeeded
                  $conn->commit();
                  echo "<div class='alert alert-success'>Maintenance record created successfully (ID: $mid)</div>";

              } catch (Exception $e) {
                  // ✅ Rollback only if connection is still alive
                  if ($conn->ping()) {
                      $conn->rollback();
                  }
                  echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
              }
          }
      }
  }
  ?>

  <div class="card">
    <div class="card-header">
      <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>معلومات الصيانة</h5>
    </div>
    <div class="card-body">
      <form method="POST" id="maintenanceForm">
        <div class="row mb-3">
          <div class="col-md-3">
            <label class="form-label">التاريخ *</label>
            <input type="date" name="date" class="form-control" required>
          </div>
          
          <div class="col-md-3">
            <label class="form-label">الكيلومترات (اختياري)</label>
            <input type="number" name="kilometers" class="form-control" min="0" placeholder="مثال: 50000">
            <small class="text-muted">عدد الكيلومترات الحالي للعربية</small>
          </div>
          
          <div class="col-md-3">
            <label class="form-label">العربية *</label>
            <select name="vehicle_id" class="form-select" required>
              <option value="">اختر العربية</option>
              <?php 
              $vehicles->data_seek(0);
              while($v = $vehicles->fetch_assoc()): ?>
                <option value="<?=$v['vehicle_id']?>"><?=htmlspecialchars($v['model'])?> (<?=htmlspecialchars($v['plate_number'])?>)</option>
              <?php endwhile; ?>
            </select>
          </div>
          
          <div class="col-md-3">
            <label class="form-label">الميكانيكي *</label>
            <select name="mechanic_id" class="form-select" required>
              <option value="">اختر الميكانيكي</option>
              <?php 
              $mechanics->data_seek(0);
              while($m = $mechanics->fetch_assoc()): ?>
                <option value="<?=$m['user_id']?>"><?=htmlspecialchars($m['name'])?></option>
              <?php endwhile; ?>
            </select>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">الوصف</label>
          <textarea name="description" class="form-control" placeholder="وصف عملية الصيانة..." rows="3"></textarea>
        </div>

        <div class="row mb-3">
          <div class="col-md-3">
            <label class="form-label">التكلفة</label>
            <input name="cost" type="number" step="0.01" min="0" class="form-control" placeholder="0.00" value="0">
          </div>
          
          <div class="col-md-3">
            <label class="form-label">رقم المركز (اختياري)</label>
            <input name="center_id" type="number" class="form-control" placeholder="رقم المركز">
          </div>
        </div>

        <hr class="my-4">

        <h5 class="section-title">
          <i class="bi bi-gear me-2"></i>إضافة قطع الغيار
        </h5>
        
        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">اختر القطعة</label>
            <select id="partSelect" class="form-select">
              <option value="">-- اختر قطعة --</option>
              <?php 
              $parts->data_seek(0);
              while($p = $parts->fetch_assoc()): 
              ?>
                <option value="<?=$p['part_id']?>" data-name="<?=htmlspecialchars($p['name'])?>" data-price="<?=$p['price']?>">
                  <?=htmlspecialchars($p['name'])?> - <?=number_format($p['price'], 2)?> جنيه
                </option>
              <?php endwhile; ?>
            </select>
          </div>
          
          <div class="col-md-3">
            <label class="form-label">الكمية</label>
            <input type="number" id="partQuantity" class="form-control" min="1" value="1">
          </div>
          
          <div class="col-md-3 d-flex align-items-end">
            <button type="button" class="btn btn-success w-100" onclick="addPart()">
              <i class="bi bi-plus-circle me-1"></i>إضافة
            </button>
          </div>
        </div>

        <!-- Selected Parts Table -->
        <div id="selectedPartsContainer" style="display: none;" class="card mt-3">
          <div class="card-header">
            <h6 class="mb-0"><i class="bi bi-list-check me-2"></i>القطع المختارة</h6>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-sm table-bordered">
                <thead class="table-light">
                  <tr>
                    <th>اسم القطعة</th>
                    <th>السعر</th>
                    <th>الكمية</th>
                    <th>المجموع</th>
                    <th>الإجراءات</th>
                  </tr>
                </thead>
                <tbody id="selectedPartsList">
                </tbody>
                <tfoot>
                  <tr class="table-light">
                    <td colspan="3" class="text-end"><strong>إجمالي تكلفة القطع:</strong></td>
                    <td id="totalCost"><strong>0.00 جنيه</strong></td>
                    <td></td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>

        <!-- Hidden input to store selected parts as JSON -->
        <input type="hidden" name="selected_parts" id="selectedPartsInput">

        <div class="mt-4">
          <button name="add_maintenance" class="btn btn-primary btn-lg">
            <i class="bi bi-check-circle me-2"></i>إنشاء سجل الصيانة
          </button>
          <a href="maintinance.php" class="btn btn-secondary btn-lg">
            <i class="bi bi-x-circle me-2"></i>إلغاء
          </a>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Store selected parts
let selectedParts = [];

function addPart() {
    const selectElement = document.getElementById('partSelect');
    const quantityInput = document.getElementById('partQuantity');
    
    const partId = selectElement.value;
    const quantity = parseInt(quantityInput.value);
    
    if (!partId) {
        alert('Please select a part');
        return;
    }
    
    if (quantity <= 0) {
        alert('Quantity must be greater than 0');
        return;
    }
    
    // Get part details
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const partName = selectedOption.getAttribute('data-name');
    const partPrice = parseFloat(selectedOption.getAttribute('data-price'));
    
    // Check if part already added
    const existingIndex = selectedParts.findIndex(p => p.part_id === partId);
    
    if (existingIndex !== -1) {
        // Update quantity if already exists
        selectedParts[existingIndex].quantity += quantity;
    } else {
        // Add new part
        selectedParts.push({
            part_id: partId,
            name: partName,
            price: partPrice,
            quantity: quantity
        });
    }
    
    // Reset inputs
    selectElement.value = '';
    quantityInput.value = 1;
    
    // Update display
    updatePartsDisplay();
}

function removePart(partId) {
    selectedParts = selectedParts.filter(p => p.part_id !== partId);
    updatePartsDisplay();
}

function updatePartsDisplay() {
    const container = document.getElementById('selectedPartsContainer');
    const tbody = document.getElementById('selectedPartsList');
    const input = document.getElementById('selectedPartsInput');
    
    if (selectedParts.length === 0) {
        container.style.display = 'none';
        tbody.innerHTML = '';
        input.value = '';
        return;
    }
    
    container.style.display = 'block';
    
    // Build table rows
    let html = '';
    let totalCost = 0;
    
    selectedParts.forEach(part => {
        const subtotal = part.price * part.quantity;
        totalCost += subtotal;
        
        html += `
            <tr>
                    <td>${part.name}</td>
                    <td>${part.price.toFixed(2)} جنيه</td>
                    <td>${part.quantity}</td>
                    <td>${subtotal.toFixed(2)} جنيه</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removePart('${part.part_id}')">
                            <i class="bi bi-trash me-1"></i>حذف
                        </button>
                    </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    document.getElementById('totalCost').innerHTML = `<strong>${totalCost.toFixed(2)} جنيه</strong>`;
    
    // Update hidden input with JSON
    input.value = JSON.stringify(selectedParts);
}
</script>

<?php include "../partials/footer.php"; ?>