<?php
include 'config.php';
include 'auth.php';

$whereClauses = [];
$params = [];

if (!empty($_GET['loading_number'])) {
  $whereClauses[] = "loading_number LIKE ?";
  $params[] = "%" . $_GET['loading_number'] . "%";
}

if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
  $whereClauses[] = "DATE(created_at) BETWEEN ? AND ?";
  $params[] = $_GET['start_date'];
  $params[] = $_GET['end_date'];
}

$whereSql = '';
if (count($whereClauses) > 0) {
  $whereSql = 'WHERE ' . implode(' AND ', $whereClauses);
}

$sql = "SELECT * FROM purchase_expenses $whereSql ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
if ($params) {
  $stmt->bind_param(str_repeat('s', count($params)), ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>تقرير مصروفات المشتريات</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <style>
    body {
      font-family: 'Cairo', sans-serif;
      background: #f1f1f1;
      padding: 20px;
    }

    h4 {
      color: #711739;
    }

    table {
      background: white;
      border-radius: 10px;
      overflow: hidden;
      font-size: 14px;
    }

    th, td {
      vertical-align: middle;
      text-align: center;
      white-space: nowrap;
    }

    tfoot td {
      font-weight: bold;
      background-color: #e9ecef;
    }

    .high-expense {
      background-color: #f8d7da !important;
    }

    @media (max-width: 576px) {
      table {
        font-size: 12px;
      }

      .btn-group .btn {
        display: block;
        width: 100%;
        margin-bottom: 5px;
      }

      .form-label {
        font-size: 14px;
      }
    }
  </style>
</head>
<body>

<div class="container">
  <h4 class="mb-4 text-center">📊 تقرير مصروفات المشتريات</h4>

  <!-- فلاتر التصفية -->
  <form method="GET" action="">
    <div class="row mb-4">
      <div class="col-md-3 col-sm-12 mb-2">
        <label for="loading_number" class="form-label">رقم اللودنق</label>
        <input type="text" name="loading_number" id="loading_number" class="form-control"
               value="<?= isset($_GET['loading_number']) ? htmlspecialchars($_GET['loading_number']) : '' ?>">
      </div>
      <div class="col-md-3 col-sm-12 mb-2">
        <label for="start_date" class="form-label">من تاريخ</label>
        <input type="text" name="start_date" id="start_date" class="form-control datepicker"
               value="<?= isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : '' ?>">
      </div>
      <div class="col-md-3 col-sm-12 mb-2">
        <label for="end_date" class="form-label">إلى تاريخ</label>
        <input type="text" name="end_date" id="end_date" class="form-control datepicker"
               value="<?= isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : '' ?>">
      </div>
      <div class="col-md-3 col-sm-12 d-flex align-items-end">
        <button type="submit" class="btn btn-primary w-100">🔍 تصفية</button>
      </div>
    </div>
  </form>

  <!-- جدول البيانات -->
  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead class="table-dark">
        <tr>
          <th>العميل</th>
          <th>رقم اللودنق</th>
          <th>رقم الحاوية</th>
          <th>الجمارك</th>
          <th>المنفستو</th>
          <th>الموانئ</th>
          <th>الإذن</th>
          <th>الأرضيات</th>
          <th>الإجمالي</th>
          <th>الإجراءات</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $total_customs = $total_manifesto = $total_ports = $total_permission = $total_yard = $grand_total = 0;
        while($row = $result->fetch_assoc()): 
          $customs = $row['customs_amount'] + $row['customs_additional'];
          $manifesto = $row['manifesto_amount'] + $row['manifesto_additional'];
          $ports = $row['ports_amount'] + $row['ports_additional'];
          $permission = $row['permission_amount'] + $row['permission_additional'];
          $yard = $row['yard_amount'] + $row['yard_additional'];
          $total = $customs + $manifesto + $ports + $permission + $yard;

          $total_customs += $customs;
          $total_manifesto += $manifesto;
          $total_ports += $ports;
          $total_permission += $permission;
          $total_yard += $yard;
          $grand_total += $total;

          $row_class = ($total > 50000) ? 'high-expense' : '';
        ?>
          <tr class="<?= $row_class ?>">
            <td><?= htmlspecialchars($row['client_name']) ?> (<?= $row['client_code'] ?>)</td>
            <td><?= htmlspecialchars($row['loading_number']) ?></td>
            <td><?= htmlspecialchars($row['container_number']) ?></td>
            <td><?= number_format($customs, 2) ?></td>
            <td><?= number_format($manifesto, 2) ?></td>
            <td><?= number_format($ports, 2) ?></td>
            <td><?= number_format($permission, 2) ?></td>
            <td><?= number_format($yard, 2) ?></td>
            <td><?= number_format($total, 2) ?></td>
            <td>
              <div class="btn-group" role="group">
                <a href="edit_purchase_expense.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-warning" title="تعديل">✏️</a>
                <a href="delete_purchase_expense.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" title="حذف" onclick="return confirm('هل أنت متأكد من الحذف؟')">🗑️</a>
              </div>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="3">الإجمالي الكلي</td>
          <td><?= number_format($total_customs, 2) ?></td>
          <td><?= number_format($total_manifesto, 2) ?></td>
          <td><?= number_format($total_ports, 2) ?></td>
          <td><?= number_format($total_permission, 2) ?></td>
          <td><?= number_format($total_yard, 2) ?></td>
          <td><?= number_format($grand_total, 2) ?></td>
          <td>—</td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script>
  $(function() {
    $(".datepicker").datepicker({ dateFormat: "yy-mm-dd" });
  });
</script>

</body>
</html>
