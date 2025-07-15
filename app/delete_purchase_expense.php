<?php
include 'config.php';
include 'auth.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // تحقق من وجود السجل
    $check = $conn->query("SELECT id FROM purchase_expenses WHERE id = $id");
    if ($check && $check->num_rows > 0) {
        $conn->query("DELETE FROM purchase_expenses WHERE id = $id");
        header("Location: purchase_expense_report.php?deleted=1");
        exit;
    }
}

// في حال لم يوجد السجل
header("Location: purchase_expense_report.php?error=1");
exit;
