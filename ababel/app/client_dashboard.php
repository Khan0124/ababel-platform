<?php
session_start();
include 'config.php';

$client_id = $_SESSION['client_id'] ?? 0;
$client = $conn->query("SELECT * FROM clients WHERE id = $client_id")->fetch_assoc();

if (!$client) {
  die("⚠️ غير مصرح بالدخول");
}

$containers = $conn->query("SELECT c.*, cs.status, cs.driver_name, cs.driver_phone
  FROM containers c
  LEFT JOIN (
    SELECT * FROM container_status WHERE id IN (
      SELECT MAX(id) FROM container_status GROUP BY container_id
    )
  ) cs ON cs.container_id = c.id
  WHERE c.code = '{$client['code']}'
  ORDER BY c.entry_date DESC");

$transactions = $conn->query("SELECT t.*, c.container_number FROM transactions t
  LEFT JOIN containers c ON t.container_id = c.id
  WHERE t.client_id = $client_id ORDER BY t.id DESC");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>لوحة تحكم العميل</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="bg-dark text-white p-3 text-center">
    <h4>مرحبًا <?= $client['name'] ?> | <a href="client_logout.php" class="text-warning">تسجيل الخروج</a></h4><div class="text-center mt-2">
  <button class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('password-form').style.display='block'">🔒 تغيير كلمة المرور</button>
</div>

    
  </div>
  
  <?php
$client_id = $_SESSION['client_id'];
$client = $conn->query("SELECT name, balance, insurance_balance FROM clients WHERE id = $client_id")->fetch_assoc();
?>

<div class="container mt-4">
  <div class="row g-3 mb-4">
    <div class="col-md-6">
      <div class="card text-white bg-success shadow">
        <div class="card-body">
          <h5 class="card-title">الرصيد الحالي</h5>
          <p class="card-text fs-4"><?= number_format($client['balance'], 2) ?> جنيه</p>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card text-white bg-info shadow">
        <div class="card-body">
          <h5 class="card-title">رصيد التأمين</h5>
          <p class="card-text fs-4"><?= number_format($client['insurance_balance'], 2) ?> جنيه</p>
        </div>
      </div>
    </div>
  </div>
</div>

  <div class="container mt-4">
    <div class="card mb-3">
      <div class="card-header bg-primary text-white">📦 الحاويات</div>
      <div class="card-body table-responsive">
        <table class="table table-bordered text-center">
          <thead><tr><th>رقم</th><th>رقم الحاوية</th><th>الحالة</th><th>السائق</th><th>الهاتف</th></tr></thead>
          <tbody>
          <?php $i=1; while($c = $containers->fetch_assoc()): ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= $c['container_number'] ?></td>
              <td><?= $c['status'] ?? '-' ?></td>
              <td><?= $c['status'] == 'في الترحيل' ? $c['driver_name'] : '-' ?></td>
              <td><?= $c['status'] == 'في الترحيل' ? $c['driver_phone'] : '-' ?></td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="card">
      <div class="card-header bg-success text-white">💳 كشف الحساب</div>
      <div class="card-body table-responsive">
        <table class="table table-bordered text-center">
          <thead><tr><th>التاريخ</th><th>البيان</th><th>النوع</th><th>المبلغ</th><th>الحاوية</th></tr></thead>
          <tbody>
          <?php while($t = $transactions->fetch_assoc()): ?>
            <tr>
              <td><?= $t['created_at'] ?></td>
              <td><?= $t['description'] ?></td>
              <td><?= $t['type'] ?></td>
              <td><?= number_format($t['amount'], 2) ?></td>
              <td><?= $t['container_number'] ?? '-' ?></td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="container" id="password-form" style="display: none; max-width: 500px; margin-top: 30px;">
  <div class="card shadow-sm">
    <div class="card-header bg-dark text-white">🔒 تغيير كلمة المرور</div>
    <div class="card-body">
      <form method="POST" action="update_password.php">
        <div class="mb-3">
          <label>كلمة المرور الجديدة:</label>
          <input type="password" name="new_password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">تحديث</button>
      </form>
    </div>
  </div>
</div>

</body>
</html>