<?php
include 'config.php';
include 'auth.php';

$requests = $conn->query("SELECT rr.*, r.name AS register_name
                          FROM register_requests rr
                          LEFT JOIN registers r ON rr.register_id = r.id
                          ORDER BY rr.created_at DESC");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>قائمة مطالبات السجلات</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Cairo', sans-serif;
      background: #f4f4f4;
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
      white-space: nowrap;
    }

    .btn-sm {
      padding: 6px 8px;
      font-size: 13px;
    }

    @media (max-width: 576px) {
      h4 {
        font-size: 18px;
      }

      .btn-sm {
        display: block;
        width: 100%;
        margin-bottom: 5px;
      }

      td, th {
        font-size: 13px;
      }
    }
  </style>
</head>
<body>

<div class="container">
  <h4 class="mb-4 text-center">📋 قائمة مطالبات السجلات</h4>

  <div class="table-responsive">
    <table class="table table-bordered table-striped text-center">
      <thead class="table-dark">
        <tr>
          <th>#</th>
          <th>السجل</th>
          <th>العميل</th>
          <th>رقم اللودنق</th>
          <th>الحاوية</th>
          <th>المطالبة</th>
          <th>المنفستو</th>
          <th>تاريخ الطلب</th>
          <th>الإجراءات</th>
        </tr>
      </thead>
      <tbody>
        <?php $i = 1; while($r = $requests->fetch_assoc()): ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><?= htmlspecialchars($r['register_name']) ?></td>
          <td><?= htmlspecialchars($r['client_name']) ?> (<?= $r['client_code'] ?>)</td>
          <td><?= htmlspecialchars($r['loading_number']) ?></td>
          <td><?= htmlspecialchars($r['container_number']) ?></td>
          <td><?= number_format($r['claim_amount'], 2) ?></td>
          <td><?= htmlspecialchars($r['manifesto_number']) ?></td>
          <td><?= date('Y-m-d H:i', strtotime($r['created_at'])) ?></td>
          <td>
            <a href="view_register_request.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-info">🔍</a>
            <a href="edit_register_request.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-warning">✏️</a>
            <a href="delete_register_request.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من الحذف؟')">🗑️</a>
            <a href="print_register_request.php?id=<?= $r['id'] ?>" target="_blank" class="btn btn-sm btn-secondary">🖨️</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <div class="text-center mt-4">
    <a href="register_requests.php" class="btn btn-primary">➕ طلب جديد</a>
  </div>
</div>

</body>
</html>
