<?php
include 'auth.php';
include 'config.php';
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <title>إضافة حاوية</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <style>
    :root {
      --primary-color: #711739;
      --light-bg: #f9f9f9;
    }
    
    body { 
      font-family: 'Cairo', sans-serif; 
      background: var(--light-bg); 
      padding: 20px;
    }
    
    .header { 
      display: flex; 
      align-items: center; 
      justify-content: space-between; 
      padding: 15px 30px; 
      background: white; 
      border-bottom: 2px solid #ddd;
      border-radius: 10px;
      margin-bottom: 20px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .header img { height: 50px; }
    .company-name { text-align: center; flex-grow: 1; }
    
    .container-form { 
      background: white; 
      padding: 30px; 
      border-radius: 10px; 
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .form-section {
      background: #f8f9fa;
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 25px;
      border-left: 4px solid var(--primary-color);
    }
    
    .form-section-title {
      color: var(--primary-color);
      border-bottom: 1px solid #ddd;
      padding-bottom: 10px;
      margin-bottom: 15px;
      font-weight: bold;
    }
    
    @media (max-width: 768px) {
      .row > div { margin-bottom: 15px; }
      .header { flex-direction: column; gap: 15px; }
    }
  </style>
</head>
<body>

<div class="header">
  <img src="logo.png" alt="Logo" />
  <div class="company-name">
    <h5 class="mb-0">شركة أبابيل للتنمية والاستثمار المحدودة</h5>
    <small>ABABEL FOR DEVELOPMENT AND INVESTMENT CO. LTD</small>
  </div>
</div>

<div class="container-form">
  <h4 class="text-center mb-4"><i class="bi bi-plus-circle"></i> إضافة حاوية جديدة</h4>

  <?php if (isset($_GET['error'])): ?>
    <?php if ($_GET['error'] === 'duplicate_container'): ?>
      <div class="alert alert-danger text-center">⚠️ رقم الحاوية مستخدم مسبقًا.</div>
    <?php elseif ($_GET['error'] === 'duplicate_loading'): ?>
      <div class="alert alert-warning text-center">⚠️ رقم اللودنق مستخدم مسبقًا.</div>
    <?php elseif ($_GET['error'] === 'required_fields'): ?>
      <div class="alert alert-danger text-center">⚠️ يرجى ملء جميع الحقول المطلوبة.</div>
    <?php endif; ?>
  <?php elseif (isset($_GET['added'])): ?>
    <div class="alert alert-success text-center">✅ تم حفظ الحاوية بنجاح!</div>
  <?php endif; ?>

  <form action="insert_container.php" method="POST" id="containerForm">
    <!-- معلومات العميل -->
    <div class="form-section">
      <h5 class="form-section-title"><i class="bi bi-person"></i> معلومات العميل</h5>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">رقم العميل *</label>
          <input type="text" id="client_code" name="client_code" class="form-control" 
                 oninput="fetchClientName()" required />
        </div>
        <div class="col-md-6">
          <label class="form-label">اسم العميل</label>
          <input type="text" id="client_name" name="client_name" class="form-control" readonly />
        </div>
      </div>
    </div>

    <!-- المعلومات الأساسية -->
    <div class="form-section">
      <h5 class="form-section-title"><i class="bi bi-box"></i> المعلومات الأساسية</h5>
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">تاريخ الشحن *</label>
          <input type="date" name="entry_date" class="form-control" required />
        </div>
        <div class="col-md-4">
          <label class="form-label">رقم اللودنق</label>
          <input type="text" name="loading_number" class="form-control" />
        </div>
        <div class="col-md-4">
          <label class="form-label">عدد الكراتين</label>
          <input type="number" name="carton_count" class="form-control" min="0" />
        </div>
      </div>
    </div>

    <!-- تفاصيل الحاوية -->
    <div class="form-section">
      <h5 class="form-section-title"><i class="bi bi-box-seam"></i> تفاصيل الحاوية</h5>
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">رقم الحاوية *</label>
          <input type="text" name="container_number" class="form-control" required />
        </div>
        <div class="col-md-4">
          <label class="form-label">رقم البوليصة</label>
          <input type="text" name="bill_number" class="form-control" />
        </div>
        <div class="col-md-4">
          <label class="form-label">الصنف</label>
          <input type="text" name="category" class="form-control" />
        </div>
      </div>
    </div>

    <!-- معلومات الشحن -->
    <div class="form-section">
      <h5 class="form-section-title"><i class="bi bi-truck"></i> معلومات الشحن</h5>
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">الشركة الناقلة</label>
          <input type="text" name="carrier" class="form-control" />
        </div>
        <div class="col-md-4">
          <label class="form-label">السجل</label>
          <select name="registry" class="form-select">
            <option value="">اختر سجل</option>
            <?php
            $res = $conn->query("SELECT id, name FROM registers");
            while ($row = $res->fetch_assoc()) {
              echo "<option value='" . (int)$row['id'] . "'>" . htmlspecialchars($row['name']) . "</option>";
            }
            ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">الوزن (طن)</label>
          <input type="number" step="0.01" name="weight" class="form-control" />
        </div>

        <div class="col-md-6">
          <label class="form-label">اسم الباخرة</label>
          <input type="text" name="ship_name" class="form-control" />
        </div>
        <div class="col-md-6">
          <label class="form-label">المحطة الجمركية</label>
          <input type="text" name="custom_station" class="form-control" />
        </div>

        <div class="col-md-6">
          <label class="form-label">مكان التفريغ</label>
          <input type="text" name="unloading_place" class="form-control" />
        </div>
        <div class="col-md-6">
          <label class="form-label">تاريخ الوصول المتوقع</label>
          <input type="date" name="expected_arrival" class="form-control" />
        </div>
      </div>
    </div>

    <!-- معلومات إضافية -->
    <div class="form-section">
      <h5 class="form-section-title"><i class="bi bi-card-text"></i> معلومات إضافية</h5>
      <div class="row g-3">
        <div class="col-md-12">
          <label class="form-label">ملاحظات</label>
          <textarea name="notes" class="form-control" rows="2"></textarea>
        </div>
        
        <div class="col-md-6">
          <label class="form-label">تم الإفراج</label>
          <select name="release_status" class="form-select">
            <option value="No" selected>لا</option>
            <option value="Yes">نعم</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">تم الإفراج من الشركة</label>
          <select name="company_release" class="form-select">
            <option value="No" selected>لا</option>
            <option value="Yes">نعم</option>
          </select>
        </div>
      </div>
    </div>

    <div class="text-center mt-4">
      <button type="submit" class="btn btn-primary px-5"><i class="bi bi-save"></i> حفظ</button>
      <button type="reset" class="btn btn-outline-secondary px-5"><i class="bi bi-arrow-counterclockwise"></i> إعادة تعيين</button>
    </div>
  </form>
</div>

<script>
function fetchClientName() {
  const code = document.getElementById('client_code').value.trim();
  if (!code) {
    document.getElementById('client_name').value = '';
    return;
  }
  
  fetch('fetch_client_name.php?code=' + encodeURIComponent(code))
    .then(res => res.text())
    .then(name => {
      document.getElementById('client_name').value = name || '';
    })
    .catch(() => {
      document.getElementById('client_name').value = '';
    });
}

// إضافة رسالة تحذير إذا لم يتم العثور على العميل
document.getElementById('client_code').addEventListener('blur', function() {
  if (this.value.trim() && !document.getElementById('client_name').value) {
    alert('لم يتم العثور على عميل بهذا الرقم');
  }
});
</script>

</body>
</html>