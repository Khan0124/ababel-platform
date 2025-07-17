<?php
include 'config.php';
include 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['description'])) {
  $descriptions = $_POST['description'];
  $methods = $_POST['method'];
  $amounts = $_POST['amount'];
  $notes = $_POST['notes'];
  $user_id = $_SESSION['user_id'] ?? null;

  for ($i = 0; $i < count($descriptions); $i++) {
    $desc = $conn->real_escape_string($descriptions[$i]);
    $method = $conn->real_escape_string($methods[$i]);
    $amount = floatval($amounts[$i]);
    $note = $conn->real_escape_string($notes[$i]);

    $conn->query("INSERT INTO cashbox (type, source, description, method, amount, notes, client_id, user_id)
                  VALUES ('صرف', 'مصروفات مكتب', '$desc', '$method', $amount, '$note', NULL, $user_id)");
  }

  header("Location: office_expense.php");
  exit;
}

$expenses = $conn->query("SELECT * FROM cashbox WHERE type='صرف' AND source='مصروفات مكتب' ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>نظام الخزنة - مصروفات المكتب</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary-color: #711739;
      --secondary-color: #198754;
      --light-bg: #f8f9fa;
      --dark-bg: #2c3e50;
      --warning-light: #fff3cd;
      --danger-light: #f8d7da;
      --table-header: #4a6572;
    }
    
    body {
      font-family: 'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
      padding: 25px;
      color: #333;
      min-height: 100vh;
    }
    
    .page-header {
      background: linear-gradient(90deg, var(--primary-color) 0%, #8a2e4d 100%);
      color: white;
      padding: 20px;
      border-radius: 15px;
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
      margin-bottom: 25px;
      position: relative;
      overflow: hidden;
    }
    
    .page-header::before {
      content: "";
      position: absolute;
      top: 0;
      right: 0;
      width: 100%;
      height: 100%;
      background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1440 320'%3E%3Cpath fill='%23ffffff' fill-opacity='0.1' d='M0,192L48,197.3C96,203,192,213,288,229.3C384,245,480,267,576,250.7C672,235,768,181,864,181.3C960,181,1056,235,1152,234.7C1248,235,1344,181,1392,154.7L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z'%3E%3C/path%3E%3C/svg%3E");
      background-size: cover;
      background-position: center;
    }
    
    .card {
      border-radius: 15px;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
      background: white;
      margin-bottom: 30px;
      border: none;
      overflow: hidden;
    }
    
    .card-header {
      background: linear-gradient(90deg, var(--primary-color) 0%, #8a2e4d 100%);
      color: white;
      padding: 15px 25px;
      border-radius: 15px 15px 0 0 !important;
      font-weight: 600;
      border: none;
    }
    
    .card-title {
      margin: 0;
      font-size: 1.3rem;
      display: flex;
      align-items: center;
    }
    
    .card-title i {
      margin-left: 10px;
      font-size: 1.5rem;
    }
    
    .btn-custom {
      border-radius: 10px;
      padding: 10px 15px;
      font-weight: 600;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .btn-primary {
      background: linear-gradient(135deg, var(--primary-color) 0%, #8a2e4d 100%);
      border: none;
    }
    
    .btn-primary:hover {
      background: linear-gradient(135deg, #5a0f2a 0%, #711739 100%);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(113, 23, 57, 0.3);
    }
    
    .btn-outline-primary {
      border: 1px solid var(--primary-color);
      color: var(--primary-color);
    }
    
    .btn-outline-primary:hover {
      background: var(--primary-color);
      color: white;
    }
    
    .btn-success {
      background: linear-gradient(135deg, var(--secondary-color) 0%, #1da65d 100%);
      border: none;
    }
    
    .btn-success:hover {
      background: linear-gradient(135deg, #0d6e3a 0%, #147a47 100%);
      transform: translateY(-2px);
    }
    
    .btn-danger {
      background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
      border: none;
    }
    
    .btn-danger:hover {
      background: linear-gradient(135deg, #bd2130 0%, #a71e2c 100%);
      transform: translateY(-2px);
    }
    
    .form-control, .form-select {
      border-radius: 10px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
      border: 1px solid #e0e0e0;
      padding: 10px 15px;
      height: 45px;
      transition: all 0.3s ease;
    }
    
    .form-control:focus, .form-select:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 0.25rem rgba(113, 23, 57, 0.15);
      transform: translateY(-2px);
    }
    
    textarea.form-control {
      height: auto;
      min-height: 45px;
    }
    
    .table-responsive {
      border-radius: 0 0 15px 15px;
      overflow: hidden;
    }
    
    .table {
      margin-bottom: 0;
      border-collapse: separate;
      border-spacing: 0;
    }
    
    .table thead th {
      background-color: var(--table-header);
      color: white;
      font-weight: 600;
      padding: 15px;
      border: none;
      position: sticky;
      top: 0;
      z-index: 10;
    }
    
    .table tbody tr {
      transition: all 0.2s ease;
    }
    
    .table tbody tr:hover {
      background-color: rgba(113, 23, 57, 0.05);
      transform: scale(1.005);
    }
    
    .table td {
      padding: 12px 15px;
      border-top: 1px solid #f0f0f0;
      vertical-align: middle;
    }
    
    .table-striped>tbody>tr:nth-of-type(odd)>* {
      background-color: rgba(249, 249, 249, 0.5);
    }
    
    .filters {
      background-color: #f8f9fa;
      border-radius: 12px;
      padding: 15px;
      margin-bottom: 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    .filters label {
      font-weight: 600;
      color: var(--primary-color);
      margin-left: 8px;
      margin-right: 5px;
    }
    
    .action-buttons .btn {
      border-radius: 8px;
      margin: 0 3px;
      min-width: 80px;
      font-size: 0.9rem;
      padding: 8px 12px;
    }
    
    .action-buttons a {
      text-decoration: none;
    }
    
    .expense-row {
      transition: all 0.3s ease;
    }
    
    .expense-row:hover {
      background-color: rgba(255, 243, 205, 0.3);
    }
    
    .total-amount {
      background-color: rgba(113, 23, 57, 0.1);
      font-weight: 700;
      color: var(--primary-color);
      border-radius: 8px;
      padding: 5px 15px;
      margin-top: 10px;
      display: inline-block;
    }
    
    .badge-type {
      padding: 6px 12px;
      border-radius: 20px;
      font-weight: 500;
      font-size: 0.85rem;
    }
    
    .badge-in {
      background-color: rgba(25, 135, 84, 0.15);
      color: var(--secondary-color);
    }
    
    .badge-out {
      background-color: rgba(220, 53, 69, 0.15);
      color: #dc3545;
    }
    
    .no-data {
      text-align: center;
      padding: 30px;
      color: #6c757d;
      font-size: 1.1rem;
    }
    
    .no-data i {
      font-size: 3rem;
      margin-bottom: 15px;
      display: block;
      color: #adb5bd;
      opacity: 0.6;
    }
    
    .footer {
      text-align: center;
      padding: 20px;
      color: #6c757d;
      font-size: 0.9rem;
      border-top: 1px solid #eaeaea;
      margin-top: 20px;
    }
    
    .input-group-icon {
      position: relative;
    }
    
    .input-group-icon i {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #6c757d;
      z-index: 5;
    }
    
    .input-group-icon input, .input-group-icon select {
      padding-left: 40px;
    }
    
    @media (max-width: 768px) {
      .filters .col {
        margin-bottom: 10px;
      }
      
      .action-buttons .btn {
        margin-bottom: 5px;
      }
      
      .table-responsive {
        font-size: 0.85rem;
      }
      
      .page-header {
        padding: 15px;
      }
      
      .page-header h1 {
        font-size: 1.5rem;
      }
      
      .btn-lg {
        padding: 8px 12px;
        font-size: 0.9rem;
      }
    }
  </style>
</head>
<body>

<div class="container">
  <div class="page-header">
    <div class="row align-items-center">
      <div class="col-md-8">
        <h1 class="mb-0"><i class="fas fa-file-invoice-dollar me-3"></i>  إدارة مصروفات المكتب</h1>
        <p class="mb-0 opacity-75">تسجيل وتتبع جميع المصروفات الإدارية للمكتب</p>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <div class="card-title"><i class="fas fa-file-medical"></i> تسجيل مصروفات جديدة</div>
    </div>
    <div class="card-body">
      <form method="POST">
        <div class="table-responsive">
          <table class="table table-hover align-middle" id="expenseTable">
            <thead class="table-light">
              <tr>
                <th>نوع المصروف</th>
                <th>طريقة الدفع</th>
                <th>المبلغ</th>
                <th>ملاحظات</th>
                <th width="90">حذف</th>
              </tr>
            </thead>
            <tbody>
              <tr class="expense-row">
                <td>
                  <div class="input-group-icon">
                    <i class="fas fa-tag"></i>
                    <select name="description[]" class="form-select">
                      <option value="حركة">حركة</option>
                      <option value="أدوات مكتبية">أدوات مكتبية</option>
                      <option value="كهرباء">كهرباء</option>
                      <option value="مياه">مياه</option>
                      <option value="صيانة">صيانة</option>
                      <option value="اتصالات">اتصالات</option>
                      <option value="ضيافة">ضيافة</option>
                      <option value="مرتبات">مرتبات</option>
                      <option value="حوافز">حوافز</option>
                      <option value="إكراميات">إكراميات</option>
                      <option value="إيجارات">إيجارات</option>
                      <option value="أصول">أصول</option>
                      <option value="أخرى">أخرى</option>
                    </select>
                  </div>
                </td>
                <td>
                  <div class="input-group-icon">
                    <i class="fas fa-credit-card"></i>
                    <select name="method[]" class="form-select">
                      <option value="كاش">كاش</option>
                      <option value="بنكك">بنكك</option>
                      <option value="أوكاش">أوكاش</option>
                      <option value="فوري">فوري</option>
                      <option value="شيك">شيك</option>
                    </select>
                  </div>
                </td>
                <td>
                  <div class="input-group-icon">
                    <i class="fas fa-money-bill-wave"></i>
                    <input type="number" name="amount[]" step="0.01" class="form-control" required placeholder="0.00">
                  </div>
                </td>
                <td>
                  <textarea name="notes[]" rows="1" class="form-control" placeholder="ملاحظات إضافية"></textarea>
                </td>
                <td class="text-center">
                  <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">
                    <i class="fas fa-trash-alt"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <div class="d-flex justify-content-between mt-4">
          <button type="button" class="btn btn-outline-primary" onclick="addRow()">
            <i class="fas fa-plus-circle me-2"></i>إضافة سطر جديد
          </button>
          <button type="submit" class="btn btn-success">
            <i class="fas fa-save me-2"></i>حفظ المصروفات
          </button>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <div class="card-title"><i class="fas fa-list"></i> المنصرفات الإدارية المسجلة</div>
    </div>
    <div class="card-body">
      <div class="filters">
        <div class="row g-3">
          <div class="col-md-3">
            <label>نوع المصروف:</label>
            <div class="input-group-icon">
              <i class="fas fa-filter"></i>
              <select id="filterType" class="form-select" onchange="filterTable()">
                <option value="">الكل</option>
                <option value="حركة">حركة</option>
                <option value="أدوات مكتبية">أدوات مكتبية</option>
                <option value="كهرباء">كهرباء</option>
                <option value="مياه">مياه</option>
                <option value="صيانة">صيانة</option>
                <option value="اتصالات">اتصالات</option>
                <option value="ضيافة">ضيافة</option>
                <option value="مرتبات">مرتبات</option>
                <option value="حوافز">حوافز</option>
                <option value="إكراميات">إكراميات</option>
                <option value="إيجارات">إيجارات</option>
                <option value="أصول">أصول</option>
                <option value="أخرى">أخرى</option>
              </select>
            </div>
          </div>
          <div class="col-md-3">
            <label>طريقة الدفع:</label>
            <div class="input-group-icon">
              <i class="fas fa-credit-card"></i>
              <select id="filterMethod" class="form-select" onchange="filterTable()">
                <option value="">الكل</option>
                <option value="كاش">كاش</option>
                <option value="بنكك">بنكك</option>
                <option value="أوكاش">أوكاش</option>
                <option value="فوري">فوري</option>
                <option value="شيك">شيك</option>
              </select>
            </div>
          </div>
          <div class="col-md-4">
            <div class="row g-2">
              <div class="col">
                <label>من:</label>
                <div class="input-group-icon">
                  <i class="fas fa-calendar"></i>
                  <input type="date" id="fromDate" class="form-control" onchange="filterTable()">
                </div>
              </div>
              <div class="col">
                <label>إلى:</label>
                <div class="input-group-icon">
                  <i class="fas fa-calendar"></i>
                  <input type="date" id="toDate" class="form-control" onchange="filterTable()">
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-outline-secondary" onclick="resetFilters()">
              <i class="fas fa-sync-alt me-2"></i>إعادة الضبط
            </button>
          </div>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-striped table-hover align-middle" id="adminTable">
          <thead>
            <tr>
              <th>التاريخ</th>
              <th>نوع المصروف</th>
              <th>طريقة الدفع</th>
              <th>المبلغ</th>
              <th>ملاحظات</th>
              <th>الإجراءات</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            $totalAmount = 0;
            if ($expenses && $expenses->num_rows > 0):
              while($row = $expenses->fetch_assoc()): 
                $totalAmount += $row['amount'];
            ?>
            <tr data-description="<?= htmlspecialchars($row['description']) ?>" 
                data-method="<?= htmlspecialchars($row['method']) ?>" 
                data-date="<?= date('Y-m-d', strtotime($row['created_at'])) ?>">
              <td><?= date("Y-m-d", strtotime($row['created_at'])) ?></td>
              <td>
                <span class="badge-type badge-out">
                  <i class="fas fa-file-invoice me-1"></i>
                  <?= htmlspecialchars($row['description']) ?>
                </span>
              </td>
              <td><?= htmlspecialchars($row['method']) ?></td>
              <td class="fw-bold text-danger"><?= number_format($row['amount'], 2) ?></td>
              <td><?= htmlspecialchars($row['notes'] ?? '-') ?></td>
              <td class="action-buttons">
                <a href="expense_view.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary" title="عرض">
                  <i class="fas fa-eye"></i>
                </a>
                <a href="edit_expense.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-warning" title="تعديل">
                  <i class="fas fa-edit"></i>
                </a>
                <a href="delete_expense.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" title="حذف" onclick="return confirm('هل أنت متأكد من حذف هذا المصروف؟')">
                  <i class="fas fa-trash-alt"></i>
                </a>
                <a href="print_expense.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-success" title="طباعة">
                  <i class="fas fa-print"></i>
                </a>
              </td>
            </tr>
            <?php endwhile; ?>
            <?php else: ?>
            <tr>
              <td colspan="6">
                <div class="no-data">
                  <i class="fas fa-inbox"></i>
                  <h5>لا توجد مصروفات مسجلة</h5>
                  <p class="mt-2">قم بإضافة مصروفات جديدة باستخدام النموذج أعلاه</p>
                </div>
              </td>
            </tr>
            <?php endif; ?>
          </tbody>
          <?php if ($expenses && $expenses->num_rows > 0): ?>
          <tfoot id="tableFooter">
            <tr>
              <td colspan="3" class="text-end fw-bold">المجموع:</td>
              <td class="fw-bold text-danger"><?= number_format($totalAmount, 2) ?></td>
              <td colspan="2"></td>
            </tr>
          </tfoot>
          <?php endif; ?>
        </table>
      </div>
    </div>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function addRow() {
  const table = document.getElementById("expenseTable").getElementsByTagName('tbody')[0];
  const newRow = table.rows[0].cloneNode(true);
  
  // Clear inputs
  newRow.querySelectorAll("input").forEach(el => el.value = "");
  newRow.querySelectorAll("textarea").forEach(el => el.value = "");
  
  // Reset selects to first option
  newRow.querySelectorAll("select").forEach(el => el.selectedIndex = 0);
  
  table.appendChild(newRow);
}

function removeRow(btn) {
  const row = btn.closest("tr");
  const table = row.closest("tbody");
  if (table.rows.length > 1) {
    row.style.opacity = "0";
    setTimeout(() => row.remove(), 300);
  }
}

function filterTable() {
  const type = document.getElementById("filterType").value;
  const method = document.getElementById("filterMethod").value;
  const from = document.getElementById("fromDate").value;
  const to = document.getElementById("toDate").value;
  const rows = document.querySelectorAll("#adminTable tbody tr");
  
  let totalAmount = 0;
  let visibleRows = 0;
  
  rows.forEach(row => {
    if (row.querySelector('.no-data')) {
      row.style.display = "none";
      return;
    }
    
    const rowType = row.getAttribute('data-description');
    const rowMethod = row.getAttribute('data-method');
    const rowDate = row.getAttribute('data-date');
    
    const matchType = !type || rowType === type;
    const matchMethod = !method || rowMethod === method;
    const matchFrom = !from || rowDate >= from;
    const matchTo = !to || rowDate <= to;
    
    if (matchType && matchMethod && matchFrom && matchTo) {
      row.style.display = "";
      visibleRows++;
      
      // Update total for visible rows
      const amountCell = row.children[3];
      if (amountCell) {
        const amountText = amountCell.textContent.replace(/,/g, '');
        totalAmount += parseFloat(amountText);
      }
    } else {
      row.style.display = "none";
    }
  });
  
  // Update total amount in footer
  const totalCell = document.querySelector("#tableFooter td:nth-child(2)");
  if (totalCell) {
    totalCell.textContent = totalAmount.toFixed(2);
  }
  
  // Show/hide no data message
  const noDataRow = document.querySelector("#adminTable .no-data")?.closest('tr');
  if (noDataRow) {
    noDataRow.style.display = visibleRows > 0 ? "none" : "";
  }
  
  // Show/hide table footer
  const tableFooter = document.getElementById("tableFooter");
  if (tableFooter) {
    tableFooter.style.display = visibleRows > 0 ? "" : "none";
  }
}

function resetFilters() {
  document.getElementById("filterType").value = "";
  document.getElementById("filterMethod").value = "";
  document.getElementById("fromDate").value = "";
  document.getElementById("toDate").value = "";
  filterTable();
}

// Initialize filters
document.addEventListener('DOMContentLoaded', function() {
  filterTable();
});
</script>
</body>
</html>