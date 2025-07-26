<?php
session_start();
include 'auth_employee.php';
include '../includes/config.php';

$lab_id = $_SESSION['lab_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name_ar = $_POST['name_ar'];
    $name_en = $_POST['name_en'];

    // Basic validation
    if (empty($name_ar) || empty($name_en)) {
        $error = "Both Arabic and English names are required.";
    } else {
        // Insert into database
        $stmt = $conn->prepare("INSERT INTO exam_categories (lab_id, name_ar, name_en) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $lab_id, $name_ar, $name_en);
        if ($stmt->execute()) {
            $success = "Category added successfully.";
        } else {
            $error = "Error adding category: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إضافة تصنيف جديد</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f7fb;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            max-width: 600px;
            margin-top: 50px;
        }
        .card {
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
            border: none;
        }
        .card-header {
            background: linear-gradient(120deg, #3a7ca5, #2c5d8a);
            color: white;
            border-radius: 12px 12px 0 0 !important;
            font-weight: 600;
            padding: 15px 20px;
        }
        .form-control {
            border-radius: 8px;
            padding: 10px 15px;
            border: 1px solid #dee2e6;
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: #3a7ca5;
            box-shadow: 0 0 0 0.25rem rgba(58, 124, 165, 0.25);
        }
        .btn-primary {
            background: linear-gradient(120deg, #3a7ca5, #2c5d8a);
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
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">إضافة تصنيف جديد</h4>
        </div>
        <div class="card-body">
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php elseif (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">اسم التصنيف (عربي)</label>
                    <input type="text" name="name_ar" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">اسم التصنيف (إنجليزي)</label>
                    <input type="text" name="name_en" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">حفظ التصنيف</button>
                <a href="exam_list.php" class="btn btn-secondary">رجوع</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>