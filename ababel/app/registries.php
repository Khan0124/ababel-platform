<?php
include 'config.php';
include 'auth.php';

// فلترة
$search = $_GET['search'] ?? '';
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$where = "1";

if ($search) {
  $safe = $conn->real_escape_string($search);
  $where .= " AND r.name LIKE '%$safe%'";
}
if ($from && $to) {
  $where .= " AND DATE(r.created_at) BETWEEN '$from' AND '$to'";
}

$res = $conn->query("
  SELECT r.*, 
    SUM(CASE WHEN t.type = 'مطالبة' THEN t.amount ELSE 0 END) AS total_debit,
    SUM(CASE WHEN t.type = 'قبض' THEN t.amount ELSE 0 END) AS total_credit
  FROM registers r
  LEFT JOIN transactions t ON t.register_id = r.id AND t.description = 'سجل'
  WHERE $where
  GROUP BY r.id
  ORDER BY r.created_at DESC
");

// إضافة سجل جديد
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $name = $_POST['name'];
    $stmt = $conn->prepare("INSERT INTO registers (name) VALUES (?)");
    $stmt->bind_param("s", $name);
    if ($stmt->execute()) {
        header("Location: registries.php");
        exit;
    } else {
        echo "❌ فشل في إضافة السجل: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>إدارة السجلات</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <style>
    body { font-family: 'Cairo', sans-serif; background: #f8f9fa; }
    .top-header {
      background-color: #711739;
      color: white;
      padding: 10px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .top-header img { height: 40px; }
    .top-header h1 { font-size: 20px; margin: 0; }
  </style>
</head>
<body>

<div class="top-header">
  <div><a href="dashboard.php"><img src="logo.png" alt="Logo"></a></div>
  <h1>شركة أبابيل للتنمية والاستثمار - إدارة السجلات</h1>
</div>

<div class="container mt-4">
  <div class="card shadow-sm p-3 mb-4">
    <form class="row g-2" method="GET">
      <div class="col-md-4">
        <input type="text" name="search" class="form-control" placeholder="🔍 اسم السجل..." value="<?= htmlspecialchars($search) ?>">
      </div>
      <div class="col-md-3">
        <input type="date" name="from" class="form-control" value="<?= htmlspecialchars($from) ?>">
      </div>
      <div class="col-md-3">
        <input type="date" name="to" class="form-control" value="<?= htmlspecialchars($to) ?>">
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-dark w-100">بحث</button>
      </div>
    </form>
  </div>

  <div class="card p-3 shadow-sm mb-4">
    <form method="POST" class="row g-2 align-items-center">
      <div class="col-md-6">
        <input type="text" name="name" class="form-control" placeholder="اسم السجل" required>
      </div>
      <div class="col-md-3">
        <button type="submit" class="btn btn-success w-100">➕ إضافة سجل</button>
      </div>
    </form>
  </div>

  <div class="card p-3 shadow-sm">
    <h5 class="mb-3">📒 قائمة السجلات</h5>
    <div class="table-responsive">
      <table class="table table-bordered text-center align-middle">
        <thead class="table-dark">
          <tr>
            <th>#</th><th>الاسم</th><th>المطالبات</th><th>المقبوضات</th><th>الرصيد</th><th>التاريخ</th><th>الإجراءات</th>
          </tr>
        </thead>
        <tbody>
          <?php $i = 1; while($row = $res->fetch_assoc()): ?>
            <?php
              $debit = floatval($row['total_debit']);
              $credit = floatval($row['total_credit']);
              $balance = $debit - $credit;
            ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= htmlspecialchars($row['name']) ?></td>
              <td><?= number_format($debit, 2) ?></td>
              <td><?= number_format($credit, 2) ?></td>
              <td><?= number_format($balance, 2) ?></td>
              <td><?= $row['created_at'] ?></td>
              <td>
                <a href="registry_view.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">👁️</a>
                <a href="edit_registry.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">✏️</a>
                <a href="delete_registry.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من الحذف؟')">🗑️</a>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

</body>
</html>