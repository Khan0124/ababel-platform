
<?php
include 'config.php';
$client_code = $_GET['code'] ?? '';
$containers = [];

if ($client_code) {
  $stmt = $conn->prepare("SELECT id, container_number, loading_number, category FROM containers WHERE code = ?");
  $stmt->bind_param("s", $client_code);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_assoc()) {
    $containers[] = $row;
  }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>🧾 تسجيل معاملة مالية</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
  <div class="card shadow p-4">
    <h4 class="mb-4 text-center">🧾 تسجيل معاملة مالية</h4>
    <form method="post" action="insert_transaction.php">
      <input type="hidden" name="client_code" value="<?= htmlspecialchars($client_code) ?>">

      <div class="mb-3">
        <label for="amount" class="form-label">المبلغ</label>
        <input type="number" name="amount" id="amount" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="description" class="form-label">البيان</label>
        <select name="description" class="form-select" required>
          <option value="">اختر البيان</option>
          <option value="تختيم">تختيم</option>
          <option value="سجل">سجل</option>
          <option value="موانئ">موانئ</option>
          <option value="أرضيات">أرضيات</option>
        </select>
      </div>

      <div class="mb-3">
        <label for="container_id" class="form-label">رقم الحاوية المرتبطة</label>
        <select name="container_id" class="form-select" required>
          <option value="">اختر الحاوية</option>
          <?php foreach ($containers as $c): ?>
            <option value="<?= $c['id'] ?>">
              <?= $c['container_number'] ?> | <?= $c['loading_number'] ?> | <?= $c['category'] ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="text-center">
        <button type="submit" class="btn btn-primary px-4">💾 حفظ المعاملة</button>
      </div>
    </form>
  </div>
</div>
</body>
</html>
