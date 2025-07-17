<?php
include 'config.php';
include 'auth.php';

header('Content-Type: text/plain; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = intval($_POST['client_id']);
    $container_id = intval($_POST['container_id']);
    $created_at = mysqli_real_escape_string($conn, $_POST['created_at']);
    $type = 'صرف';
    $user_id = $_SESSION['user_id'];
    $source = $_POST['type'] ?? ''; // مثل: صرف سجل، صرف موانئ، إلخ

    // إزالة كلمة "صرف" من المصدر للحصول على اسم الخزنة
    $source = str_replace('صرف ', '', $source);

    $items = json_decode($_POST['items_json'], true);

    if (!is_array($items) || count($items) == 0) {
        echo "لم يتم إرسال بنود صحيحة.";
        exit;
    }

    // حفظ اليومية في الجدول الرئيسي
    $items_json = mysqli_real_escape_string($conn, json_encode($items));
    $daily_sql = "INSERT INTO daily_expenses (type, client_id, container_id, items_json, created_at, user_id)
                  VALUES ('$source', $client_id, $container_id, '$items_json', '$created_at', $user_id)";

    if ($conn->query($daily_sql)) {
        $daily_id = $conn->insert_id;
        $success_count = 0;
        $error_log = [];

        foreach ($items as $item) {
            $desc = mysqli_real_escape_string($conn, $item['desc']); // البيان الحقيقي مثل: أبابيل، كشف، طرناطة...
            $amount = floatval($item['amount']);
            $usd = floatval($item['usd']);

            if ($amount <= 0) continue;

            // تحديد المصدر بناءً على نوع اليومية
            $cash_source = $source;
            
            // إذا كان المصدر غير محدد، نستخدم وصف البند
            if (empty($cash_source)) {
                if (strpos($desc, 'سجل') !== false) {
                    $cash_source = 'سجل';
                } elseif (strpos($desc, 'تختيم') !== false) {
                    $cash_source = 'تختيم';
                } elseif (strpos($desc, 'موانئ') !== false) {
                    $cash_source = 'موانئ';
                } elseif (strpos($desc, 'منفستو') !== false) {
                    $cash_source = 'منفستو';
                } else {
                    $cash_source = 'مصروفات مكتب';
                }
            }

            $stmt = $conn->prepare("INSERT INTO cashbox (client_id, container_id, type, description, amount, usd, created_at, source, user_id, daily_expense_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iissddssii", $client_id, $container_id, $type, $desc, $amount, $usd, $created_at, $cash_source, $user_id, $daily_id);

            if ($stmt->execute()) {
                $success_count++;
            } else {
                $error_log[] = $stmt->error;
            }
        }
        file_put_contents('last_change.txt', time());
        echo "✅ تم حفظ اليومية ($daily_id) وعدد ($success_count) عملية مالية.";
    } else {
        echo "⚠️ فشل في حفظ اليومية: " . $conn->error;
    }

    exit;
}

echo "طلب غير صالح.";
exit;
?>