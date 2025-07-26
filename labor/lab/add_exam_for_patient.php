<?php
session_start();
include 'auth_employee.php';
include '../includes/config.php';

$lab_id = $_SESSION['lab_id'];

// المرضى
$patients = $conn->query("SELECT id, code, name, gender, age_value, age_unit FROM patients WHERE lab_id = $lab_id");
$patients_data = $patients->fetch_all(MYSQLI_ASSOC);

// الفحوصات
$exams = $conn->query("SELECT id, name_en, code_exam, price FROM exam_catalog WHERE lab_id = $lab_id AND is_active = 1");
$exams_data = $exams->fetch_all(MYSQLI_ASSOC);

// شركات التأمين المفعلة فقط
$insurance_companies = $conn->query("SELECT id, name FROM insurance_companies WHERE lab_id = $lab_id AND is_active = 1");
$insurance_data = $insurance_companies->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>إضافة فاتورة فحوص</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <style>
    .exam-group { margin-bottom: 10px; border: 1px solid #ccc; padding: 10px; border-radius: 8px; background: #f9f9f9; }
    .select2-container--default .select2-selection--single { height: 38px; padding-top: 5px; }
    label { font-weight: bold; }
  </style>
</head>
<body>
<div class="container py-4">
  <h4 class="mb-4 text-center">🧾 إضافة فاتورة فحوصات</h4>

  <form method="post" action="save_exam_for_patient.php">
    <div class="mb-3">
      <label class="form-label">المريض</label>
      <select name="patient_id" class="form-select select2" required>
        <option value="">-- اختر المريض --</option>
        <?php foreach ($patients_data as $p): ?>
          <option value="<?= $p['id'] ?>"><?= $p['code'] . ' - ' . $p['name'] . " (" . $p['age_value'] . " " . $p['age_unit'] . ")" ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">التحاليل</label>
      <div id="exams-wrapper">
        <div class="row exam-group">
          <div class="col-md-6">
            <select name="exam_ids[]" class="form-select select2" required>
              <option value="">-- اختر التحليل --</option>
              <?php foreach ($exams_data as $e): ?>
                <option value="<?= $e['id'] ?>"><?= $e['code_exam'] ?> - <?= $e['name_en'] ?> (<?= $e['price'] ?> ج)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-5">
            <input type="text" name="notes[]" class="form-control" placeholder="ملاحظات (اختياري)">
          </div>
          <div class="col-md-1 d-flex align-items-center justify-content-center">
            <button type="button" class="btn btn-danger remove-exam">✖</button>
          </div>
        </div>
      </div>
      <button type="button" class="btn btn-secondary mt-2" id="add-exam">➕ تحليل إضافي</button>
    </div>

    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label class="form-label">تاريخ التحليل</label>
        <input type="date" name="exam_date" class="form-control" value="<?= date('Y-m-d') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">الجهة المحوّلة</label>
        <input type="text" name="referred_by" class="form-control" placeholder="اسم الجهة أو الطبيب">
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">شركة التأمين</label>
      <select name="insurance_company_id" class="form-select select2">
        <option value="">— لا يوجد —</option>
        <?php foreach ($insurance_data as $company): ?>
          <option value="<?= $company['id'] ?>"><?= $company['name'] ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">الخصم (جنيه)</label>
      <input type="number" name="discount" step="0.01" class="form-control" value="0">
    </div>

    <div class="mb-3">
      <label class="form-label">ملاحظات إضافية</label>
      <textarea name="notes" rows="3" class="form-control" placeholder="أدخل أي ملاحظات إضافية"></textarea>
    </div>

    <div class="text-center">
      <button type="submit" class="btn btn-success">💾 حفظ الفاتورة</button>
    </div>
  </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
  $(document).ready(function() {
    $('.select2').select2({ width: '100%', dir: 'rtl', placeholder: "بحث..." });

    $('#add-exam').on('click', function() {
      const wrapper = $('#exams-wrapper');
      const group = wrapper.find('.exam-group').first();
      const clone = group.clone();
      clone.find('select, input').val('');
      wrapper.append(clone);
      clone.find('.select2').select2({ width: '100%', dir: 'rtl', placeholder: "بحث..." });
    });

    $(document).on('click', '.remove-exam', function() {
      const groups = $('.exam-group');
      if (groups.length > 1) $(this).closest('.exam-group').remove();
    });
  });
</script>
</body>
</html>
