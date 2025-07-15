<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include 'config.php';
session_start();

$user_id = $_SESSION['user_id'];

if (!isset($_POST['client_id'], $_POST['type'], $_POST['description'], $_POST['amount'], $_POST['exchange_rate'], $_POST['payment_method'], $_POST['container_id'])) {
    die("⚠️ بيانات ناقصة.");
}

$client_id = intval($_POST['client_id']);
$type = $_POST['type'];
$description = $_POST['description'];
$amount = floatval($_POST['amount']);
$exchange_rate = floatval($_POST['exchange_rate']);
$payment_method = $_POST['payment_method'];
$container_id = intval($_POST['container_id']);
$reference_number = $_POST['reference_number'] ?? '';
$register_id = isset($_POST['register_id']) ? intval($_POST['register_id']) : null;
$actual_cost = isset($_POST['actual_cost']) ? floatval($_POST['actual_cost']) : null;
$related_claim_id = isset($_POST['related_claim_id']) ? intval($_POST['related_claim_id']) : null;

if ($amount <= 0) {
    die("⚠️ لا يمكن أن يكون المبلغ صفر أو أقل.");
}

if ($description === 'سجل' && !$register_id) {
    die("⚠️ يجب اختيار سجل عند اختيار البيان 'سجل'.");
}

// بدء المعاملة
$conn->begin_transaction();

try {
    // إدراج المعاملة الجديدة
    $stmt = $conn->prepare("INSERT INTO transactions (
        client_id, type, description, amount, exchange_rate, payment_method,
        container_id, reference_number, actual_cost, register_id, related_claim_id, serial, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, NOW())");

    $stmt->bind_param(
        "issdssissii",
        $client_id, $type, $description, $amount, $exchange_rate,
        $payment_method, $container_id, $reference_number, $actual_cost, $register_id, $related_claim_id
    );

    if (!$stmt->execute()) {
        throw new Exception("❌ فشل في حفظ المعاملة: " . $conn->error);
    }

    $transaction_id = $stmt->insert_id;
    $serial = date('Ymd') . '-' . $transaction_id;
    $conn->query("UPDATE transactions SET serial = '$serial' WHERE id = $transaction_id");

    // إذا كانت معاملة قبض مرتبطة بمطالبة
    if ($type === 'قبض' && $related_claim_id) {
        // حساب المبلغ المدفوع سابقاً لهذه المطالبة
        $paid_amount_result = $conn->query("
            SELECT COALESCE(SUM(amount), 0) as paid_amount 
            FROM transactions 
            WHERE related_claim_id = $related_claim_id AND type = 'قبض'
        ");
        $paid_amount = $paid_amount_result->fetch_assoc()['paid_amount'];
        
        // حساب المبلغ الكلي للمطالبة
        $claim_result = $conn->query("SELECT amount FROM transactions WHERE id = $related_claim_id");
        $claim_amount = $claim_result->fetch_assoc()['amount'];
        
        // حساب المبلغ المتبقي
        $remaining_amount = $claim_amount - $paid_amount;
        
        // تحديد حالة المطالبة بناءً على المبلغ المدفوع
        $new_status = 'open';
        if ($remaining_amount <= 0) {
            $new_status = 'paid';
        } elseif ($paid_amount > 0) {
            $new_status = 'partial';
        }
        
        // تحديث حالة المطالبة
        $conn->query("UPDATE transactions SET status = '$new_status' WHERE id = $related_claim_id");
    }

    // معالجة رصيد التأمين
    if ($description === 'تأمين' && $type === 'قبض') {
        $conn->query("UPDATE clients SET insurance_balance = insurance_balance + $amount WHERE id = $client_id");

        $stmt2 = $conn->prepare("INSERT INTO cashbox (type, source, description, method, amount, client_id, user_id, created_at)
            VALUES ('قبض', 'رصيد التأمين', ?, ?, ?, ?, ?, NOW())");
        $stmt2->bind_param("ssdii", $description, $payment_method, $amount, $client_id, $user_id);
        $stmt2->execute();
    } 
    // معالجة التحويل من التأمين
    elseif ($payment_method === 'من التأمين') {
        $check = $conn->query("SELECT insurance_balance FROM clients WHERE id = $client_id")->fetch_assoc();
        if ($check['insurance_balance'] < $amount) {
            throw new Exception("⚠️ رصيد التأمين غير كافٍ.");
        }
        $conn->query("UPDATE clients SET insurance_balance = insurance_balance - $amount, balance = balance + $amount WHERE id = $client_id");

        $stmt3 = $conn->prepare("INSERT INTO cashbox (type, source, description, method, amount, client_id, user_id, created_at)
            VALUES ('قبض', 'تحويل من التأمين', ?, ?, ?, ?, ?, NOW())");
        $stmt3->bind_param("ssdii", $description, $payment_method, $amount, $client_id, $user_id);
        $stmt3->execute();
    } 
    // معالجة استرداد التأمين
    elseif ($description === 'تأمين' && $type === 'استرداد') {
        $conn->query("UPDATE clients SET insurance_balance = insurance_balance - $amount WHERE id = $client_id");

        $stmt4 = $conn->prepare("INSERT INTO cashbox (type, source, description, method, amount, client_id, user_id, created_at)
            VALUES ('صرف', 'استرداد التأمين', ?, ?, ?, ?, ?, NOW())");
        $stmt4->bind_param("ssdii", $description, $payment_method, $amount, $client_id, $user_id);
        $stmt4->execute();
    } 
    // معالجة المعاملات العادية
    elseif ($description !== 'تأمين') {
        if ($type === 'قبض') {
            $conn->query("UPDATE clients SET balance = balance + $amount WHERE id = $client_id");

            // تحديد المصدر بناءً على الوصف - التصحيح المهم هنا
            $cash_source = 'دخل خارجي'; // القيمة الافتراضية
            
            // تحديد الخزنة المناسبة حسب الوصف
            if (strpos($description, 'سجل') !== false) {
                $cash_source = 'سجل';
            } elseif (strpos($description, 'تختيم') !== false) {
                $cash_source = 'تختيم';
            } elseif (strpos($description, 'موانئ') !== false) {
                $cash_source = 'موانئ';
            } elseif (strpos($description, 'عمولات شحن') !== false || strpos($description, 'منفستو') !== false) {
                $cash_source = 'منفستو';
            }

            $cash = $conn->prepare("INSERT INTO cashbox (transaction_id, client_id, amount, type, source, description, method, user_id, created_at)
                VALUES (?, ?, ?, 'قبض', ?, ?, ?, ?, NOW())");
            $cash->bind_param("iissssi", $transaction_id, $client_id, $amount, $cash_source, $description, $payment_method, $user_id);
            $cash->execute();
        } elseif ($type === 'مطالبة') {
            $conn->query("UPDATE clients SET balance = balance - $amount WHERE id = $client_id");
            
            // تحديد المصدر للمطالبات
            $cash_source = 'دخل خارجي';
            
            if (strpos($description, 'سجل') !== false) {
                $cash_source = 'سجل';
            } elseif (strpos($description, 'تختيم') !== false) {
                $cash_source = 'تختيم';
            } elseif (strpos($description, 'موانئ') !== false) {
                $cash_source = 'موانئ';
            } elseif (strpos($description, 'عمولات شحن') !== false || strpos($description, 'منفستو') !== false) {
                $cash_source = 'منفستو';
            }
            
            $claim = $conn->prepare("INSERT INTO cashbox (transaction_id, client_id, amount, type, source, description, method, user_id, created_at)
                VALUES (?, ?, ?, 'صرف', ?, ?, ?, ?, NOW())");
            $claim->bind_param("iissssi", $transaction_id, $client_id, $amount, $cash_source, $description, $payment_method, $user_id);
            $claim->execute();
        }
    }

    // تحديث وقت آخر تغيير
    file_put_contents('last_change.txt', time());
    
    // تأكيد المعاملة
    $conn->commit();
    // إرسال إشعار للعميل
    if ($type === 'مطالبة') {
        // استدعاء ملف إرسال الإشعارات
        require 'send_fcm.php';
        
        // تحديث بيانات الرسالة
        $message['message']['topic'] = 'client_' . $client_id;
        $message['message']['notification']['title'] = 'تمت إضافة مطالبة جديدة';
        $message['message']['notification']['body'] = 'المبلغ: ' . $amount . ' - الوصف: ' . $description;
        $message['message']['data'] = [
            'type' => 'new_claim',
            'client_id' => $client_id
        ];

        // إرسال الإشعار
        $client = new Client();
        $response = $client->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
            'headers' => [
                'Authorization' => "Bearer $accessToken",
                'Content-Type' => 'application/json',
            ],
            'json' => $message,
        ]);
    }
    
    header("Location: profile.php?id=$client_id");
    exit;
} catch (Exception $e) {
    // التراجع عن المعاملة في حالة حدوث خطأ
    $conn->rollback();
    die($e->getMessage());
}
?>