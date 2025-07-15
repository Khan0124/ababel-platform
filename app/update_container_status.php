<?php
include 'config.php';
include 'auth.php';

$id = $_GET['container_id'] ?? $_GET['id'] ?? 0;
if (!$id) {
  die("رقم الحاوية غير صالح.");
}

// Get container data
$stmt = $conn->prepare("SELECT * FROM containers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$container = $stmt->get_result()->fetch_assoc();
$stmt->close();

// دالة للتحقق من وجود حالة إجرائية
function hasOperationalStatus($conn, $container_id, $status) {
    $stmt = $conn->prepare("SELECT id FROM container_operational_status WHERE container_id = ? AND status = ?");
    $stmt->bind_param("is", $container_id, $status);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

// معالجة إرسال النموذج
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $error = '';
    $success = false;

    // Handle Bill of Lading
    if (isset($_POST['bill_of_lading_status'])) {
        $status = $_POST['bill_of_lading_status'];
        
        // Update container status
        $updateStmt = $conn->prepare("UPDATE containers SET bill_of_lading_status = ? WHERE id = ?");
        $updateStmt->bind_param("si", $status, $id);
        $updateStmt->execute();
        
        // Handle file upload if issued
        if ($status === 'issued') {
            if (isset($_FILES['bill_file']) && $_FILES['bill_file']['error'] == UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/bills/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                $fileName = uniqid() . '_' . basename($_FILES['bill_file']['name']);
                $filePath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['bill_file']['tmp_name'], $filePath)) {
                    // Insert or update operational status
                    if (hasOperationalStatus($conn, $id, 'Bill of Lading Issued')) {
                        $stmt = $conn->prepare("UPDATE container_operational_status SET file_path = ? WHERE container_id = ? AND status = 'Bill of Lading Issued'");
                        $stmt->bind_param("si", $filePath, $id);
                    } else {
                        $stmt = $conn->prepare("INSERT INTO container_operational_status (container_id, status, file_path) VALUES (?, 'Bill of Lading Issued', ?)");
                        $stmt->bind_param("is", $id, $filePath);
                    }
                    $stmt->execute();
                } else {
                    $error = "فشل رفع ملف البوليصة.";
                }
            } else {
                $error = "يجب رفع ملف البوليصة عند تحديد حالة 'تم الإصدار'.";
            }
        }
    }

    // Handle Tashitim
    if (isset($_POST['tashitim_status'])) {
        $status = $_POST['tashitim_status'];
        
        // Update container status
        $updateStmt = $conn->prepare("UPDATE containers SET tashitim_status = ? WHERE id = ?");
        $updateStmt->bind_param("si", $status, $id);
        $updateStmt->execute();
        
        // Handle date if done
        if ($status === 'done') {
            if (!empty($_POST['customs_date'])) {
                $date = $_POST['customs_date'];
                // Insert or update operational status
                if (hasOperationalStatus($conn, $id, 'Customs Cleared')) {
                    $stmt = $conn->prepare("UPDATE container_operational_status SET date = ? WHERE container_id = ? AND status = 'Customs Cleared'");
                    $stmt->bind_param("si", $date, $id);
                } else {
                    $stmt = $conn->prepare("INSERT INTO container_operational_status (container_id, status, date) VALUES (?, 'Customs Cleared', ?)");
                    $stmt->bind_param("is", $id, $date);
                }
                $stmt->execute();
            } else {
                $error = "يجب إدخال تاريخ التختيم عند تحديد حالة 'تم التختيم'.";
            }
        }
    }
    
    // Handle Positional Status
    if (isset($_POST['position_status']) && !empty($_POST['position_status'])) {
        $position_status = $_POST['position_status'];
        
        // Insert new position history
        $stmt = $conn->prepare("INSERT INTO container_position_history (container_id, status) VALUES (?, ?)");
        $stmt->bind_param("is", $id, $position_status);
        $stmt->execute();
    }

    if (empty($error)) {
        $success = true;
        // Refresh container data
        $stmt = $conn->prepare("SELECT * FROM containers WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $container = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}

// جلب الحالات الإجرائية
$operational_statuses = [];
$stmt = $conn->prepare("SELECT status, file_path, date FROM container_operational_status WHERE container_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $operational_statuses[$row['status']] = $row;
}

// جلب سجل أحداث الحاوية
$events = [];

// جلب سجل الحالات الزمنية
$position_history = [];
$stmt = $conn->prepare("SELECT status, created_at FROM container_position_history WHERE container_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $events[] = [
        'type' => 'position',
        'status' => $row['status'],
        'date' => $row['created_at']
    ];
}

// جلب الأحداث الإجرائية
$stmt = $conn->prepare("SELECT * FROM container_operational_status WHERE container_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $events[] = [
        'type' => 'operational',
        'status' => $row['status'],
        'date' => $row['date'] ? $row['date'] : $row['created_at'],
        'file_path' => $row['file_path']
    ];
}

// ترجمة الحالات للعربية
function translateStatus($status) {
    $translations = [
        'Loaded' => 'تم التحميل',
        'At Port' => 'في الميناء',
        'At Sea' => 'في البحر',
        'Arrived' => 'وصلت',
        'Transported by Land' => 'تم الشحن البري',
        'Delivered' => 'تم التسليم',
        'Empty Returned' => 'تم تسليم الفارغ',
        'Bill of Lading Issued' => 'تم إصدار البوليصة',
        'Customs Cleared' => 'تم التختيم الجمركي'
    ];
    
    return $translations[$status] ?? $status;
}

// دالة لترتيب الأحداث حسب التاريخ
usort($events, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>تحديث حالة الحاوية</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <style>
    body { padding: 30px; background: #f9f9f9; }
    .form-control, select { margin-bottom: 15px; }
    .hidden { display: none; }
    .status-card { 
      background: #fff; 
      border-radius: 8px; 
      padding: 20px; 
      margin-bottom: 20px; 
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .status-badge { 
      padding: 5px 10px; 
      border-radius: 4px; 
      font-weight: normal;
      display: inline-block;
      margin-right: 10px;
    }
    .status-not { background: #e9ecef; color: #495057; }
    .status-done { background: #d4edda; color: #155724; }
    .status-delayed { background: #f8d7da; color: #721c24; }
    .event-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    .event-table th, .event-table td {
      border: 1px solid #ddd;
      padding: 10px;
      text-align: center;
    }
    .event-table th {
      background-color: #711739;
      color: white;
    }
    .event-row:hover {
      background-color: #f5f5f5;
    }
    .event-type {
      font-weight: bold;
    }
    .event-details {
      text-align: right;
    }
  </style>
  <script>
    function toggleFields() {
      const billStatus = document.querySelector("select[name='bill_of_lading_status']").value;
      const tashitimStatus = document.querySelector("select[name='tashitim_status']").value;
      
      document.getElementById("billFields").style.display = billStatus === 'issued' ? "block" : "none";
      document.getElementById("customsFields").style.display = tashitimStatus === 'done' ? "block" : "none";
    }
  </script>
</head>
<body>
<div class="container">
  <h3 class="mb-4">🔄 تحديث حالة الحاوية #<?= $id ?></h3>
  
  <?php if (isset($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
  <?php endif; ?>
  
  <?php if (isset($success) && $success): ?>
    <div class="alert alert-success">تم تحديث الحالة بنجاح!</div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    <!-- Positional Status Section -->
    <div class="status-card">
      <h5>الموقع الحالي للحاوية</h5>
      
      <div class="mb-3">
        <label class="form-label">تحديث الموقع:</label>
        <select name="position_status" class="form-control">
          <option value="">-- اختر الموقع --</option>
          <option value="Loaded">تم التحميل</option>
          <option value="At Port">في الميناء</option>
          <option value="At Sea">في البحر</option>
          <option value="Arrived">وصلت</option>
          <option value="Transported by Land">تم الشحن البري</option>
          <option value="Delivered">تم التسليم</option>
          <option value="Empty Returned">تم تسليم الفارغ</option>
        </select>
      </div>
    </div>
    
    <!-- Existing procedural status sections -->
    <div class="status-card">
      <h5>الحالات الإجرائية</h5>
      
      <div class="mb-4">
        <h6>حالة البوليصة:</h6>
        <span class="status-badge 
          <?= $container['bill_of_lading_status'] === 'not_issued' ? 'status-not' : '' ?>
          <?= $container['bill_of_lading_status'] === 'issued' ? 'status-done' : '' ?>
          <?= $container['bill_of_lading_status'] === 'delayed' ? 'status-delayed' : '' ?>">
          <?php 
            $statusLabels = [
              'not_issued' => 'لم يتم الإصدار',
              'issued' => 'تم الإصدار',
              'delayed' => 'متأخر'
            ];
            echo $statusLabels[$container['bill_of_lading_status']] ?? $container['bill_of_lading_status'];
          ?>
        </span>
      </div>
      
      <div class="mb-3">
        <label class="form-label">تحديث حالة البوليصة:</label>
        <select name="bill_of_lading_status" class="form-control" onchange="toggleFields()">
          <option value="not_issued" <?= $container['bill_of_lading_status'] === 'not_issued' ? 'selected' : '' ?>>لم يتم الإصدار</option>
          <option value="issued" <?= $container['bill_of_lading_status'] === 'issued' ? 'selected' : '' ?>>تم الإصدار</option>
          <option value="delayed" <?= $container['bill_of_lading_status'] === 'delayed' ? 'selected' : '' ?>>متأخر</option>
        </select>
      </div>
      
      <div id="billFields" class="<?= $container['bill_of_lading_status'] === 'issued' ? '' : 'hidden' ?>">
        <label>رفع صورة البوليصة:</label>
        <input type="file" name="bill_file" class="form-control" accept=".pdf,.jpg,.png">
        <?php if (isset($operational_statuses['Bill of Lading Issued']['file_path'])): ?>
          <div class="mt-2">
            <span>الملف الحالي: </span>
            <a href="<?= $operational_statuses['Bill of Lading Issued']['file_path'] ?>" target="_blank">
              <?= basename($operational_statuses['Bill of Lading Issued']['file_path']) ?>
            </a>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="status-card">
      <div class="mb-4">
        <h6>حالة التختيم:</h6>
        <span class="status-badge 
          <?= $container['tashitim_status'] === 'not_done' ? 'status-not' : '' ?>
          <?= $container['tashitim_status'] === 'done' ? 'status-done' : '' ?>
          <?= $container['tashitim_status'] === 'delayed' ? 'status-delayed' : '' ?>">
          <?php 
            $statusLabels = [
              'not_done' => 'لم يتم التختيم',
              'done' => 'تم التختيم',
              'delayed' => 'متأخر'
            ];
            echo $statusLabels[$container['tashitim_status']] ?? $container['tashitim_status'];
          ?>
        </span>
      </div>
      
      <div class="mb-3">
        <label class="form-label">تحديث حالة التختيم:</label>
        <select name="tashitim_status" class="form-control" onchange="toggleFields()">
          <option value="not_done" <?= $container['tashitim_status'] === 'not_done' ? 'selected' : '' ?>>لم يتم التختيم</option>
          <option value="done" <?= $container['tashitim_status'] === 'done' ? 'selected' : '' ?>>تم التختيم</option>
          <option value="delayed" <?= $container['tashitim_status'] === 'delayed' ? 'selected' : '' ?>>متأخر</option>
        </select>
      </div>
      
      <div id="customsFields" class="<?= $container['tashitim_status'] === 'done' ? '' : 'hidden' ?>">
        <label>تاريخ التختيم:</label>
        <input type="date" name="customs_date" class="form-control" 
               value="<?= isset($operational_statuses['Customs Cleared']['date']) ? $operational_statuses['Customs Cleared']['date'] : date('Y-m-d') ?>">
      </div>
    </div>

    <button class="btn btn-primary">💾 حفظ التحديثات</button>
    <a href="containers.php" class="btn btn-secondary">العودة للقائمة</a>
  </form>

  <!-- سجل أحداث الحاوية -->
  <div class="status-card mt-4">
    <h5>📜 سجل أحداث الحاوية</h5>
    <?php if (empty($events)): ?>
      <p class="text-center py-3">لا توجد أحداث مسجلة لهذه الحاوية</p>
    <?php else: ?>
      <table class="event-table">
        <thead>
          <tr>
            <th width="20%">التاريخ</th>
            <th width="20%">نوع الحدث</th>
            <th width="60%">الحدث</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($events as $event): ?>
            <tr class="event-row">
              <td><?= date('Y-m-d H:i', strtotime($event['date'])) ?></td>
              <td class="event-type">
                <?= $event['type'] === 'position' ? 'تغيير الموقع' : 'إجراء' ?>
              </td>
              <td class="event-details">
                <?= translateStatus($event['status']) ?>
                <?php if ($event['type'] === 'operational' && $event['status'] === 'Bill of Lading Issued' && !empty($event['file_path'])): ?>
                  <br>
                  <a href="<?= $event['file_path'] ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                    <i class="bi bi-file-earmark"></i> عرض البوليصة
                  </a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>
</body>
</html>