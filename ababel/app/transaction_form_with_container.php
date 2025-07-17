
<?php
include 'config.php';

// إذا تم تمرير كود العميل عبر GET
$client_code = $_GET['code'] ?? '';

// جلب الحاويات المرتبطة بالعميل
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

<form method="post" action="insert_transaction.php">
  <!-- حقول بيانات الإيصال العادية -->
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
    <label for="container_id" class="form-label">ربط الحاوية</label>
    <select name="container_id" class="form-select">
      <option value="">اختياري - اختر الحاوية</option>
      <?php foreach ($containers as $c): ?>
        <option value="<?= $c['id'] ?>">
          <?= $c['container_number'] ?> | <?= $c['loading_number'] ?> | <?= $c['category'] ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <button type="submit" class="btn btn-primary">💾 حفظ المعاملة</button>
</form>
