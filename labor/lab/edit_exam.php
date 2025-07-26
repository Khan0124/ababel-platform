<?php
session_start();
include '../includes/config.php';

$id = $_GET['id'];
$lab_id = $_SESSION['lab_id'];

// استعلام التحليل
$stmt = $conn->prepare("SELECT * FROM exam_catalog WHERE id = ? AND lab_id = ?");
$stmt->bind_param("ii", $id, $lab_id);
$stmt->execute();
$result = $stmt->get_result();
$exam = $result->fetch_assoc();

if (!$exam) {
    die("<div style='color:red; text-align:center;'>⚠️ التحليل غير موجود أو لا يخص المعمل.</div>");
}

// Fetch all categories
$categories = $conn->query("SELECT id, name_ar FROM exam_categories WHERE lab_id = $lab_id AND is_active = 1 ORDER BY name_ar ASC");

// المكونات المرتبطة
$components = $conn->query("SELECT * FROM exam_components WHERE exam_id = $id");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>تعديل التحليل</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
  <h3 class="mb-4 text-primary">✏️ تعديل التحليل: <?= htmlspecialchars($exam['name']) ?></h3>

  <form method="POST" action="update_exam.php">
    <input type="hidden" name="exam_id" value="<?= $exam['id'] ?>">

    <div class="row mb-3">
      <div class="col">
        <label>الاسم</label>
        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($exam['name']) ?>" required>
      </div>
      <div class="col">
        <label>الاسم بالإنجليزية</label>
        <input type="text" name="name_en" class="form-control" value="<?= htmlspecialchars($exam['name_en']) ?>">
      </div>
    </div>

    <div class="row mb-3">
      <div class="col">
        <label>الكود</label>
        <input type="text" name="code_exam" class="form-control" value="<?= htmlspecialchars($exam['code_exam']) ?>" required>
      </div>
      <div class="col">
        <label>التصنيف</label>
        <select name="category_id" class="form-control" required>
            <option value="">-- اختر التصنيف --</option>
            <?php while ($cat = $categories->fetch_assoc()): ?>
                <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $exam['category_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name_ar']) ?>
                </option>
            <?php endwhile; ?>
        </select>
      </div>
      <div class="col">
        <label>السعر</label>
        <input type="number" name="price" class="form-control" value="<?= $exam['price'] ?>" step="0.01" required>
      </div>
      <div class="col">
        <label>الوحدة</label>
        <input type="text" name="unit" class="form-control" value="<?= htmlspecialchars($exam['unit']) ?>">
      </div>
    </div>

    <div class="mb-3">
      <label>القيمة المرجعية</label>
      <input type="text" name="ref_value" class="form-control" value="<?= htmlspecialchars($exam['normal_range']) ?>">
    </div>

    <div class="mb-3">
      <label>الوصف</label>
      <textarea name="description" class="form-control"><?= htmlspecialchars($exam['description']) ?></textarea>
    </div>

    <hr>
    <h5 class="text-info">🧬 المكونات المرتبطة:</h5>
    <div id="components-list">
      <?php while($c = $components->fetch_assoc()): ?>
        <div class="row mb-2 component-row">
          <div class="col">
            <input type="text" name="components_existing[<?= $c['id'] ?>]" class="form-control" value="<?= htmlspecialchars($c['name']) ?>">
          </div>
          <div class="col-auto">
            <button type="button" class="btn btn-danger btn-sm remove-component">حذف</button>
          </div>
        </div>
      <?php endwhile; ?>
    </div>

    <button type="button" class="btn btn-secondary btn-sm mb-3" id="add-component">➕ إضافة مكون</button>

    <hr>
    <button type="submit" class="btn btn-primary">💾 حفظ التعديلات</button>
    <a href="exam_list.php" class="btn btn-secondary">رجوع</a>
  </form>
</div>

<script>
document.getElementById('add-component').onclick = function() {
  const container = document.createElement('div');
  container.className = "row mb-2 component-row";
  container.innerHTML = `
    <div class="col">
      <input type="text" name="components_new[]" class="form-control" placeholder="اسم المكون الجديد">
    </div>
    <div class="col-auto">
      <button type="button" class="btn btn-danger btn-sm remove-component">حذف</button>
    </div>
  `;
  document.getElementById('components-list').appendChild(container);
};

document.addEventListener('click', function(e) {
  if (e.target.classList.contains('remove-component')) {
    e.target.closest('.component-row').remove();
  }
});
</script>

</body>
</html>