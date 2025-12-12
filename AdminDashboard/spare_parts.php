<?php include "../auth_admin.php"; include "../connection.php"; include "../partials/header.php"; include "../partials/navbar.php"; ?>

<div class="container mt-4">
  <h2 class="page-title">
    <i class="bi bi-gear me-2"></i>إدارة قطع الغيار
  </h2>

  <?php
  if (isset($_POST['add_part'])) {
      $name = $conn->real_escape_string($_POST['name']);
      $price = floatval($_POST['price']);
      $brand = $conn->real_escape_string($_POST['brand']);
      $stmt = $conn->prepare("INSERT INTO spare_part (name,price,brand) VALUES (?,?,?)");
      $stmt->bind_param("sds",$name,$price,$brand);
      $stmt->execute(); $stmt->close();
  }
  if (isset($_GET['delete'])) {
      $id = intval($_GET['delete']);
      $conn->query("DELETE FROM spare_part WHERE part_id=$id");
  }
  $res = $conn->query("SELECT * FROM spare_part");
  ?>

  <div class="card">
    <div class="card-header">
      <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>قائمة قطع الغيار</h5>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover table-bordered">
          <thead>
            <tr>
              <th>#</th>
              <th><i class="bi bi-tag me-1"></i>الاسم</th>
              <th><i class="bi bi-currency-pound me-1"></i>السعر</th>
              <th><i class="bi bi-building me-1"></i>العلامة التجارية</th>
              <th><i class="bi bi-gear me-1"></i>الإجراءات</th>
            </tr>
          </thead>
          <tbody>
            <?php while($r = $res->fetch_assoc()): ?>
              <tr>
                <td><?=$r['part_id']?></td>
                <td><?=htmlspecialchars($r['name'])?></td>
                <td><span class="badge bg-success"><?=number_format($r['price'], 2)?> جنيه</span></td>
                <td><?=htmlspecialchars($r['brand'] ?: '-')?></td>
                <td>
                  <a class="btn btn-sm btn-danger" href="?delete=<?=$r['part_id']?>" onclick="return confirm('هل أنت متأكد من الحذف؟')">
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
      <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>إضافة قطعة غيار جديدة</h5>
    </div>
    <div class="card-body">
      <form method="POST" class="row g-3">
        <div class="col-md-4">
          <label class="form-label">الاسم</label>
          <input name="name" class="form-control" placeholder="اسم القطعة" required>
        </div>
        <div class="col-md-2">
          <label class="form-label">السعر</label>
          <input name="price" type="number" step="0.01" class="form-control" placeholder="0.00" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">العلامة التجارية</label>
          <input name="brand" class="form-control" placeholder="العلامة التجارية">
        </div>
        <div class="col-md-3 d-flex align-items-end">
          <button name="add_part" class="btn btn-success w-100">
            <i class="bi bi-plus-circle me-2"></i>إضافة
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include "../partials/footer.php"; ?>
