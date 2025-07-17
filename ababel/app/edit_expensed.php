<?php
include 'config.php';
include 'auth.php';

$id = intval($_GET['id'] ?? 0);
$exp = $conn->query("SELECT * FROM daily_expenses WHERE id = $id")->fetch_assoc();
if (!$exp) die('الرابط غير صالح');

// استخراج بيانات افتراضية من اليومية
$client_id = intval($exp['client_id'] ?? 0);
$container_id = intval($exp['container_id'] ?? 0);
$user_id = intval($exp['user_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $items = $_POST['items_json'] ?? '';
  $decoded = json_decode($items, true);

  if (!is_array($decoded) || count($decoded) === 0) {
    die('⚠️ البيانات غير صالحة أو فاضية');
  }

  // حذف البنود القديمة من جدول cashbox المرتبطة بنفس اليومية
  $conn->query("DELETE FROM cashbox WHERE daily_expense_id = $id");

  $total_amount = 0;

  foreach ($decoded as $item) {
    $desc = mysqli_real_escape_string($conn, $item['desc']);
    $amount = floatval($item['amount']);
    $usd = floatval($item['usd']);

    $total_amount += $amount;

    // إضافة كل بند كسطر منفصل
    $conn->query("
      INSERT INTO cashbox (
        client_id, container_id, user_id,
        type, source, category, description, method,
        amount, usd, created_at, synced, daily_expense_id
      ) VALUES (
        $client_id, $container_id, $user_id,
        'صرف', 'صرف موانئ', NULL, '$desc', NULL,
        $amount, $usd, NOW(), 1, $id
      )
    ");
  }

  // حفظ البنود المعدلة في جدول اليومية
  $safe_items = mysqli_real_escape_string($conn, json_encode($decoded, JSON_UNESCAPED_UNICODE));
  $conn->query("UPDATE daily_expenses SET items_json = '$safe_items' WHERE id = $id");

  header("Location: view_expense.php?id=$id");
  exit;
}

$items = json_decode($exp['items_json'], true);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>تعديل يومية</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <style>
    body { font-family: 'Cairo', sans-serif; background: #f4f4f4; padding: 20px; }
    td, th { text-align: center; }
  </style>
</head>
<body>
<div class="container">
  <h4 class="mb-3">✏️ تعديل المبالغ فقط</h4>
  <form method="POST" onsubmit="beforeSubmit()">
    <input type="hidden" name="items_json" id="items_json">
    <table class="table table-bordered">
      <thead class="table-dark">
        <tr><th>البيان</th><th>المبلغ (جنيه)</th><th>المبلغ ($)</th><th>إزالة</th></tr>
      </thead>
      <tbody id="itemBody"></tbody>
    </table>
    <button type="submit" class="btn btn-success">💾 حفظ</button>
    <a href="daily_expense_list.php" class="btn btn-secondary">إلغاء</a>
  </form>
</div>

<script>
let items = <?= json_encode($items, JSON_UNESCAPED_UNICODE) ?>;
const body = document.getElementById('itemBody');
const hiddenInput = document.getElementById('items_json');

function render() {
  body.innerHTML = '';
  items.forEach((item, i) => {
    body.innerHTML += `
      <tr>
        <td><input class='form-control' value='${item.desc}' disabled></td>
        <td><input class='form-control' type='number' value='${item.amount}' onchange='items[${i}].amount=parseFloat(this.value)'></td>
        <td><input class='form-control' type='number' value='${item.usd}' disabled></td>
        <td><button type='button' class='btn btn-sm btn-danger' onclick='removeItem(${i})'>🗑️</button></td>
      </tr>
    `;
  });
}

function beforeSubmit() {
  hiddenInput.value = JSON.stringify(items);
}

function removeItem(index) {
  items.splice(index, 1);
  render();
}

render();
</script>
</body>
</html>
