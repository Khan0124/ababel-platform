<?php
include 'auth.php';
include 'config.php';

$error = '';
$success = '';

// إضافة إجراء
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'];
  $amount = floatval($_POST['amount']);
  $method = $_POST['method'];
  $doc_no = $_POST['doc_no'] ?? '';

  if ($amount > 0) {
    $stmt = $conn->prepare("INSERT INTO cashbox (type, source, description, method, amount, notes, user_id, created_at)
                            VALUES ('صرف', 'إجراءات حكومية', ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssdsi", $name, $method, $amount, $doc_no, $_SESSION['user_id']);
    $stmt->execute();
    $success = "✅ تم تسجيل العملية.";
  } else {
    $error = "⚠️ يجب إدخال مبلغ صحيح.";
  }
}

// جلب العمليات
$res = $conn->query("
  SELECT cb.*, u.full_name 
  FROM cashbox cb 
  LEFT JOIN users u ON cb.user_id = u.id 
  WHERE cb.source = 'إجراءات حكومية' 
  ORDER BY cb.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>الإجراءات الحكومية</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
  <h3 class="mb-4 text-center">🏛️ الإجراءات الحكومية</h3>

  <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

  <form method="POST" class="row g-3 mb-4 bg-white p-3 shadow-sm rounded">
    <div class="col-md-4">
      <label>نوع الإجراء:</label>
      <select name="name" class="form-select" required>
        <option value="">اختر</option>
        <option value="ضرائب قيمة مضافة">ضرائب قيمة مضافة</option>
        <option value="ضريبة أرباح الأعمال">ضريبة أرباح الأعمال</option>
        <option value="زكاة">زكاة</option>
      </select>
    </div>

    <div class="col-md-4">
      <label>رقم المستند (اختياري):</label>
      <input type="text" name="doc_no" class="form-control">
    </div>

    <div class="col-md-2">
      <label>المبلغ:</label>
      <input type="number" step="0.01" name="amount" class="form-control" required>
    </div>

    <div class="col-md-2">
      <label>طريقة الدفع:</label>
      <select name="method" class="form-select" required>
        <option value="كاش">كاش</option>
        <option value="بنكك">بنكك</option>
        <option value="أوكاش">أوكاش</option>
        <option value="فوري">فوري</option>
        <option value="شيك">شيك</option>
      </select>
    </div>

    <div class="col-12 d-grid">
      <button type="submit" class="btn btn-primary">➖ خصم</button>
    </div>
  </form>

  <div class="card shadow-sm p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5>📋 آخر العمليات</h5>
      <a href="dashboard.php" class="btn btn-dark btn-sm">🏠 العودة للرئيسية</a>
    </div>
    <div class="table-responsive">
      <table class="table table-bordered text-center">
        <thead class="table-dark">
          <tr>
            <th>التاريخ</th>
            <th>الوصف</th>
            <th>رقم المستند</th>
            <th>المبلغ</th>
            <th>الطريقة</th>
            <th>الموظف</th>
            <th>الإجراءات</th>
          </tr>
        </thead>
        <tbody>
          <?php while($row = $res->fetch_assoc()): ?>
            <tr>
              <td><?= $row['created_at'] ?></td>
              <td><?= htmlspecialchars($row['description']) ?></td>
              <td><?= htmlspecialchars($row['notes'] ?? '-') ?></td>
              <td><?= number_format($row['amount'], 2) ?></td>
              <td><?= $row['method'] ?></td>
              <td><?= $row['full_name'] ?? 'غير معروف' ?></td>
              <td>
                <a href="print_cashbox.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info">🖨️</a>
                <a href="edit_cashbox.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">✏️</a>
                <a href="delete_cashbox.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد؟')">🗑️</a>
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
