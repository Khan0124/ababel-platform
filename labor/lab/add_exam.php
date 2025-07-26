<?php
session_start();
include 'auth_employee.php';
include '../includes/config.php';

$lab_id = $_SESSION['lab_id'];

// Get categories from exam_categories table
$categories = $conn->query("SELECT id, name_ar, name_en FROM exam_categories WHERE lab_id = $lab_id AND is_active = 1 ORDER BY name_ar ASC");

// Get stock items
$stock_items = $conn->query("SELECT id, name FROM stock_items ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>إضافة تحليل</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #3a7ca5;
      --secondary: #2c3e50;
      --light: #f8f9fa;
      --dark: #343a40;
      --success: #28a745;
      --danger: #dc3545;
    }
    
    body {
      background-color: #f5f7fb;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    .card {
      border-radius: 12px;
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
      border: none;
      margin-bottom: 25px;
    }
    
    .card-header {
      background: linear-gradient(120deg, var(--primary), #2c5d8a);
      color: white;
      border-radius: 12px 12px 0 0 !important;
      font-weight: 600;
      padding: 15px 20px;
    }
    
    .form-control, .form-select {
      border-radius: 8px;
      padding: 10px 15px;
      border: 1px solid #dee2e6;
      transition: all 0.3s;
    }
    
    .form-control:focus, .form-select:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 0.25rem rgba(58, 124, 165, 0.25);
    }
    
    .btn-primary {
      background: linear-gradient(120deg, var(--primary), #2c5d8a);
      border: none;
      padding: 10px 25px;
      border-radius: 8px;
      font-weight: 600;
      transition: all 0.3s;
    }
    
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(58, 124, 165, 0.3);
    }
    
    .btn-success {
      background: linear-gradient(120deg, var(--success), #1e7e34);
      border: none;
    }
    
    .section-title {
      color: var(--primary);
      border-bottom: 2px solid var(--primary);
      padding-bottom: 8px;
      margin-top: 25px;
      margin-bottom: 20px;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .table th {
      background-color: #e9ecef;
      color: var(--dark);
      font-weight: 600;
    }
    
    .alert {
      border-radius: 8px;
    }
    
    .badge-category {
      background-color: #e3f2fd;
      color: var(--primary);
      padding: 5px 12px;
      border-radius: 20px;
      font-weight: 500;
    }
  </style>
</head>
<body>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-dark"><i class="fas fa-flask me-2"></i>إضافة تحليل جديد</h2>
    <a href="exam_list.php" class="btn btn-outline-primary">
      <i class="fas fa-arrow-left me-2"></i>العودة للقائمة
    </a>
  </div>

  <!-- Alerts -->
  <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success d-flex align-items-center">
      <i class="fas fa-check-circle fa-2x me-3"></i>
      <div class="fw-bold">تم إضافة التحليل بنجاح!</div>
    </div>
  <?php elseif (isset($_GET['error']) && $_GET['error'] === 'duplicate'): ?>
    <div class="alert alert-danger d-flex align-items-center">
      <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
      <div class="fw-bold">الكود مستخدم من قبل. الرجاء اختيار كود تحليل مختلف.</div>
    </div>
  <?php endif; ?>

  <div class="card">
    <div class="card-header">
      <i class="fas fa-plus-circle me-2"></i>تفاصيل التحليل
    </div>
    
    <div class="card-body">
      <form id="examForm" method="POST" action="save_exam.php">
        <div class="row g-3 mb-4">
          <div class="col-md-4">
            <label class="form-label fw-bold">اسم التحليل (عربي)</label>
            <input name="name" class="form-control form-control-lg" placeholder="اسم التحليل باللغة العربية" required>
          </div>
          
          <div class="col-md-4">
            <label class="form-label fw-bold">اسم التحليل (إنجليزي)</label>
            <input name="name_en" class="form-control form-control-lg" placeholder="اسم التحليل باللغة الإنجليزية" required>
          </div>
          
          <div class="col-md-4">
            <label class="form-label fw-bold">كود التحليل</label>
            <input name="code_exam" class="form-control form-control-lg" placeholder="كود فريد للتحليل" required>
          </div>
        </div>
        
        <div class="row g-3 mb-4">
          <div class="col-md-4">
            <label class="form-label fw-bold">التصنيف</label>
            <select name="category_id" class="form-select form-select-lg" required>
              <option value="">-- اختر التصنيف --</option>
              <?php while ($cat = $categories->fetch_assoc()): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name_ar']) . ' (' . htmlspecialchars($cat['name_en']) . ')' ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          
          <div class="col-md-4">
            <label class="form-label fw-bold">السعر (جنية)</label>
            <input name="price" type="number" step="0.01" class="form-control form-control-lg" placeholder="السعر بالجنيه" required>
          </div>
          
          <div class="col-md-4">
            <label class="form-label fw-bold">الوحدة</label>
            <input name="unit" class="form-control form-control-lg" placeholder="الوحدة (مثلاً: mg/dL)" required>
          </div>
        </div>
        
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label fw-bold">القيم المرجعية</label>
            <input name="ref_value" class="form-control form-control-lg" placeholder="القيم المرجعية الطبيعية">
          </div>
          
          <div class="col-md-4">
            <label class="form-label fw-bold">نوع العينة</label>
            <input name="sample_type" class="form-control form-control-lg" placeholder="نوع العينة المطلوبة">
          </div>
          
          <div class="col-md-4">
            <label class="form-label fw-bold">زمن التنفيذ</label>
            <input name="duration" class="form-control form-control-lg" placeholder="الوقت المتوقع للإنجاز">
          </div>
        </div>
        
        <hr class="my-4">
        
        <h5 class="section-title">
          <i class="fas fa-vial"></i> المكونات المطلوبة للفحص
        </h5>
        <p class="text-muted mb-4">يمكنك إضافة المواد المستهلكة اللازمة لإجراء هذا التحليل</p>

        <div class="row g-3 mb-4">
          <div class="col-md-5">
            <select id="componentSelect" class="form-select form-select-lg">
              <option value="">اختر مكونًا من المخزن</option>
              <?php while ($item = $stock_items->fetch_assoc()): ?>
                <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['name']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          
          <div class="col-md-3">
            <input id="componentQty" type="number" min="1" class="form-control form-control-lg" placeholder="الكمية المطلوبة">
          </div>
          
          <div class="col-md-2">
            <button type="button" onclick="addComponent()" class="btn btn-success w-100 btn-lg">
              <i class="fas fa-plus me-2"></i>إضافة
            </button>
          </div>
          
          <div class="col-md-2">
            <button type="button" class="btn btn-outline-secondary w-100 btn-lg" data-bs-toggle="tooltip" 
                    title="الرجاء اختيار مكون وإدخال الكمية">
              <i class="fas fa-info-circle"></i>
            </button>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle" id="componentsTable">
            <thead class="table-light">
              <tr>
                <th>المكون</th>
                <th width="150">الكمية</th>
                <th width="100">الإجراءات</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
        
        <div class="d-flex justify-content-end mt-5">
          <button type="submit" class="btn btn-primary btn-lg px-5">
            <i class="fas fa-save me-2"></i>حفظ التحليل
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
let components = [];

function addComponent() {
  const select = document.getElementById('componentSelect');
  const qty = document.getElementById('componentQty').value;
  const id = select.value;
  const name = select.options[select.selectedIndex].text;

  if (!id || qty <= 0) {
    alert("الرجاء اختيار مكون وإدخال كمية صحيحة");
    return;
  }

  components.push({ id, name, qty });
  updateComponentTable();
  select.value = "";
  document.getElementById('componentQty').value = "";
}

function updateComponentTable() {
  const tbody = document.querySelector("#componentsTable tbody");
  tbody.innerHTML = "";
  
  if (components.length === 0) {
    tbody.innerHTML = `<tr><td colspan="3" class="text-center py-4 text-muted">لم يتم إضافة أي مكونات بعد</td></tr>`;
    return;
  }
  
  components.forEach((c, index) => {
    const row = `
      <tr>
        <td>
          <div class="d-flex align-items-center">
            <i class="fas fa-cube me-3 text-primary"></i>
            <div>${c.name}</div>
          </div>
          <input type="hidden" name="components[${index}][item_id]" value="${c.id}">
        </td>
        <td>
          <input type="number" name="components[${index}][quantity]" 
                 value="${c.qty}" class="form-control" min="1" readonly>
        </td>
        <td>
          <button type="button" class="btn btn-sm btn-danger" onclick="removeComponent(${index})">
            <i class="fas fa-trash-alt"></i>
          </button>
        </td>
      </tr>
    `;
    tbody.innerHTML += row;
  });
}

function removeComponent(index) {
  if (confirm("هل أنت متأكد من إزالة هذا المكون؟")) {
    components.splice(index, 1);
    updateComponentTable();
  }
}

document.getElementById('examForm').addEventListener('submit', function(e) {
  if (components.length === 0) {
    const confirmSave = confirm("لم تقم بإضافة أي مكونات. هل تريد المتابعة دون إضافة مكونات؟");
    if (!confirmSave) {
      e.preventDefault();
    }
  }
});

// Initialize empty table
updateComponentTable();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Initialize Bootstrap tooltips
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
</script>
</body>
</html>