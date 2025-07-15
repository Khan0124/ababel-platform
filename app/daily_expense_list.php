<?php
include 'config.php';
include 'auth.php';

$type = $_GET['type'] ?? '';
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$where = "1";

if (!empty($type)) {
  $where .= " AND d.type = '" . mysqli_real_escape_string($conn, $type) . "'";
}
if (!empty($from) && !empty($to)) {
  $where .= " AND DATE(d.created_at) BETWEEN '$from' AND '$to'";
}

$res = $conn->query("
  SELECT d.*, c.name AS client_name, ct.container_number
  FROM daily_expenses d
  LEFT JOIN clients c ON d.client_id = c.id
  LEFT JOIN containers ct ON d.container_id = ct.id
  WHERE $where
  ORDER BY d.id DESC
");


$types = ['صرف سجل', 'صرف موانئ', 'صرف تختيم', 'صرف أرضيات'];
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>يوميات الصرف</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <style>
    body { font-family: 'Cairo', sans-serif; background: #f4f4f4; padding: 20px; }
    .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .badge-sgl { background: #28a745; }
    .badge-port { background: #007bff; }
    .badge-custom { background: #6f42c1; }
    .badge-yard { background: #6c757d; }
  </style>
</head>
<body>
<div class="container">
  <div class="header">
    <h4>يوميات الصرف</h4>
    <a href="daily_expense.php" class="btn btn-dark">➕ إضافة يومية جديدة</a>
  </div>

  <form class="row g-2 mb-3" method="GET">
    <div class="col-md-3">
      <select name="type" class="form-select">
        <option value="">كل الأنواع</option>
        <?php foreach ($types as $t): ?>
          <option value="<?= $t ?>" <?= $type === $t ? 'selected' : '' ?>><?= $t ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3">
      <input type="date" name="from" class="form-control" value="<?= htmlspecialchars($from) ?>">
    </div>
    <div class="col-md-3">
      <input type="date" name="to" class="form-control" value="<?= htmlspecialchars($to) ?>">
    </div>
    <div class="col-md-3">
      <button class="btn btn-primary w-100">🔍 تطبيق الفلتر</button>
    </div>
  </form>

  <table class="table table-bordered text-center align-middle">
    <thead class="table-dark">
      <tr>
        <th>#</th>
        <th>التاريخ</th>
        <th>النوع</th>
        <th>العميل</th>
        <th>الحاوية</th>
        <th>عدد البنود</th>
        <th>الإجمالي (جنيه)</th>
        <th>الإجراءات</th>
      </tr>
    </thead>
    <tbody>
      <?php $total = 0; $i = 1; while($row = $res->fetch_assoc()): 
        $badge = 'bg-secondary';
        if ($row['type'] === 'صرف سجل') $badge = 'badge-sgl';
        elseif ($row['type'] === 'صرف موانئ') $badge = 'badge-port';
        elseif ($row['type'] === 'صرف تختيم') $badge = 'badge-custom';
        elseif ($row['type'] === 'صرف أرضيات') $badge = 'badge-yard';

        $items = json_decode($row['items_json'], true);
        $count = count($items);
        $sum = array_sum(array_column($items, 'amount'));
        $total += $sum;
      ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><?= date('Y-m-d', strtotime($row['created_at'])) ?></td>
          <td><span class="badge <?= $badge ?>"><?= $row['type'] ?></span></td>
          <td><?= $row['client_name'] ?? 'غير معروف' ?></td>
          <td><?= $row['container_number'] ?? '-' ?></td>
          <td><?= $count ?></td>
          <td><?= number_format($sum, 2) ?></td>
          <td>
            <a href="view_expense.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info">👁️</a>
            <a href="edit_expensed.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">✏️</a>
            <a href="print_expensed.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-secondary">🖨️</a>
            <a href="delete_expensed.php?id=<?= $row['id'] ?>" onclick="return confirm('تأكيد الحذف؟')" class="btn btn-sm btn-danger">🗑️</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
    <tfoot class="table-light">
      <tr>
        <th colspan="6">الإجمالي</th>
        <th colspan="2"><?= number_format($total, 2) ?> جنيه</th>
      </tr>
    </tfoot>
  </table>
</div>
</body>
</html>
