<?php
include 'auth.php';
include 'config.php';

// فلاتر
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$conditions = "WHERE cb.type = 'قبض' AND cb.source = 'يومية قبض'";
if ($from && $to) {
  $conditions .= " AND cb.created_at BETWEEN '$from 00:00:00' AND '$to 23:59:59'";
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>قائمة يوميات القبض</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    body { font-family: 'Cairo', sans-serif; padding: 20px; background-color: #f4f4f4; }
    table { background: white; }
  </style>
</head>
<body>
  <h3 class="mb-4">📋 قائمة يوميات القبض</h3>
  <a href="daily_income.php" class="btn btn-success mb-3">➕ إضافة يومية جديدة</a>

  <form class="row g-3 mb-4" method="get">
    <div class="col-md-4">
      <label>من تاريخ:</label>
      <input type="date" name="from" class="form-control" value="<?= htmlspecialchars($from) ?>">
    </div>
    <div class="col-md-4">
      <label>إلى تاريخ:</label>
      <input type="date" name="to" class="form-control" value="<?= htmlspecialchars($to) ?>">
    </div>
    <div class="col-md-4 align-self-end">
      <button class="btn btn-primary">🔍 فلترة</button>
      <a href="daily_income_list.php" class="btn btn-secondary">🔄 الكل</a>
    </div>
  </form>

  <div class="table-responsive">
    <table class="table table-bordered text-center">
      <thead class="table-dark">
        <tr>
          <th>#</th>
          <th>البيان</th>
          <th>المبلغ</th>
          <th>التاريخ</th>
          <th>الموظف</th>
          <th>إجراءات</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $q = $conn->query("SELECT cb.*, u.username FROM cashbox cb LEFT JOIN users u ON cb.user_id = u.id $conditions ORDER BY cb.id DESC");
        while ($row = $q->fetch_assoc()):
        ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['description']) ?></td>
            <td><?= number_format($row['amount'], 2) ?></td>
            <td><?= date('Y-m-d', strtotime($row['created_at'])) ?></td>
            <td><?= $row['username'] ?></td>
            <td>
              <a href="print_daily_income.php?id=<?= $row['id'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">🖨️ طباعة</a>
              <button class="btn btn-sm btn-outline-warning edit-btn"
                data-id="<?= $row['id'] ?>"
                data-description="<?= htmlspecialchars($row['description']) ?>"
                data-amount="<?= $row['amount'] ?>">✏️ تعديل</button>
              <button class="btn btn-sm btn-outline-danger delete-btn" data-id="<?= $row['id'] ?>">🗑️ حذف</button>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <!-- نافذة التعديل -->
  <div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
      <form id="editForm" class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">تعديل اليومية</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="edit-id">
          <div class="mb-3">
            <label>البيان:</label>
            <input type="text" name="description" id="edit-description" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>المبلغ:</label>
            <input type="number" name="amount" id="edit-amount" class="form-control" step="0.01" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">💾 حفظ</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    let editModal = new bootstrap.Modal(document.getElementById('editModal'));

    $(document).on('click', '.edit-btn', function () {
      $('#edit-id').val($(this).data('id'));
      $('#edit-description').val($(this).data('description'));
      $('#edit-amount').val($(this).data('amount'));
      editModal.show();
    });

    $('#editForm').submit(function (e) {
      e.preventDefault();
      $.post('update_income_ajax.php', $(this).serialize(), function (response) {
        if (response.trim() === 'success') {
          location.reload();
        } else {
          alert('❌ فشل التعديل: ' + response);
        }
      });
    });

    $(document).on('click', '.delete-btn', function () {
      if (!confirm('هل أنت متأكد من الحذف؟')) return;
      var row = $(this).closest('tr');
      var id = $(this).data('id');
      $.post('delete_income_ajax.php', { id: id }, function (response) {
        if (response.trim() === 'success') {
          row.fadeOut(300, () => row.remove());
        } else {
          alert('❌ فشل الحذف: ' + response);
        }
      });
    });
  </script>
</body>
</html>
