<?php
require_once "config.php";

// الموافقة على عملية قبض مرتبطة بمطالبة
if (isset($_GET['approve']) && is_numeric($_GET['approve'])) {
    $id = intval($_GET['approve']);

    // بدء معاملة
    $conn->begin_transaction();

    try {
        // جلب بيانات عملية الدفع
        $stmt = $conn->prepare("SELECT t.amount, t.related_claim_id, t.client_id, t.description, t.payment_method, c.name AS client_name 
                                FROM transactions t
                                JOIN clients c ON t.client_id = c.id
                                WHERE t.id = ? AND t.type = 'قبض'");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($row = $res->fetch_assoc()) {
            $amount = $row['amount'];
            $claim_id = $row['related_claim_id'];
            $client_id = $row['client_id'];
            $description = $row['description'];
            $payment_method = $row['payment_method'];
            $client_name = $row['client_name'];

            if ($claim_id > 0) {
                // 1. تحديث حالة الموافقة لعملية الدفع
                $stmt1 = $conn->prepare("UPDATE transactions SET approval_status = 'approved' WHERE id = ?");
                $stmt1->bind_param("i", $id);
                $stmt1->execute();

                // 2. جلب بيانات المطالبة الأصلية
                $stmt2 = $conn->prepare("SELECT amount, paid_amount FROM transactions WHERE id = ?");
                $stmt2->bind_param("i", $claim_id);
                $stmt2->execute();
                $res2 = $stmt2->get_result();
                $claim = $res2->fetch_assoc();
                $claim_amount = $claim['amount'];
                $paid_amount = $claim['paid_amount'];
                $new_paid_amount = $paid_amount + $amount;

                // 3. تحديد الحالة الجديدة للمطالبة
                $status = ($new_paid_amount >= $claim_amount) ? 'paid' : 'partial';

                // 4. تحديث المطالبة الأصلية
                $stmt3 = $conn->prepare("UPDATE transactions SET paid_amount = ?, status = ? WHERE id = ?");
                $stmt3->bind_param("dss", $new_paid_amount, $status, $claim_id);
                $stmt3->execute();

                // 5. إضافة سجل في جدول الخزنة (cashbox)
                $notes = "سداد مطالبة: " . $description . " - العميل: " . $client_name;
                $stmt4 = $conn->prepare("INSERT INTO cashbox (transaction_id, client_id, type, source, description, method, amount, created_at, notes) 
                                         VALUES (?, ?, 'قبض', 'عميل', ?, ?, ?, NOW(), ?)");
                $stmt4->bind_param("iissss", $id, $client_id, $description, $payment_method, $amount, $notes);
                $stmt4->execute();

                // 6. تحديث رصيد العميل (تحديث مهم)
                // - المطالبة: تخصم من الرصيد (تزيد الدين) 
                // - القبض: يضاف إلى الرصيد (يقلل الدين)
                // لذلك نضيف مبلغ السداد إلى رصيد العميل
                $stmt5 = $conn->prepare("UPDATE clients SET balance = balance + ? WHERE id = ?");
                $stmt5->bind_param("di", $amount, $client_id);
                $stmt5->execute();

                // إتمام المعاملة
                $conn->commit();

                echo "<script>alert('تمت الموافقة بنجاح وتنفيذ جميع العمليات'); window.location='pending_transactions.php';</script>";
                exit;
            } else {
                $conn->rollback();
                echo "<script>alert('لا يوجد مطالبة مرتبطة'); window.location='pending_transactions.php';</script>";
                exit;
            }
        } else {
            $conn->rollback();
            echo "<script>alert('عملية غير موجودة'); window.location='pending_transactions.php';</script>";
            exit;
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('حدث خطأ: " . $e->getMessage() . "'); window.location='pending_transactions.php';</script>";
        exit;
    }
}

// جلب كل العمليات قيد المراجعة (جميع الأنواع)
$query = "SELECT t.id, c.name AS client_name, t.type, t.amount, t.amount_usd, t.description, 
          t.payment_method, t.reference_number, t.created_at, t.receipt_image, t.proof
          FROM transactions t
          LEFT JOIN clients c ON t.client_id = c.id
          WHERE t.approval_status = 'pending'
          ORDER BY t.created_at DESC";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>المعاملات قيد الموافقة</title>
    <style>
        body { 
            font-family: 'Segoe UI', sans-serif; 
            direction: rtl; 
            background: #f7f7f7; 
            margin: 0;
            padding: 20px;
        }
        .container {
            width: 95%;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 { 
            text-align: center; 
            margin-top: 10px; 
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 0.9em;
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 12px; 
            text-align: center; 
        }
        th { 
            background-color: #4a6faf; 
            color: white; 
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #e6f7ff;
        }
        a.button {
            background: #28a745; 
            color: white; 
            padding: 8px 14px;
            text-decoration: none; 
            border-radius: 5px;
            display: inline-block;
            margin: 2px;
        }
        a.button:hover {
            background: #218838;
        }
        .thumbnail {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .thumbnail:hover {
            transform: scale(1.5);
            z-index: 100;
            position: relative;
        }
        .no-image {
            color: #999;
            font-style: italic;
        }
        .note {
            text-align: center; 
            margin: 20px 0; 
            padding: 15px;
            background: #fff3cd; 
            border: 1px solid #ffeeba;
            border-radius: 5px;
            color: #856404;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>المعاملات قيد الموافقة</h2>
        </div>
        
        <div class="note">
            <strong>نظام الرصيد:</strong><br>
            - المطالبة: تخصم من رصيد العميل (تزيد الدين)<br>
            - القبض: يضاف إلى الرصيد (يقلل الدين)<br><br>
            
            <strong>العمليات التي سيتم تنفيذها عند الموافقة على القبض:</strong><br>
            1. تسجيل العملية في الخزنة<br>
            2. إضافة المبلغ إلى رصيد العميل (تخفيض الدين)<br>
            3. تحديث حالة المطالبة الأصلية<br>
            4. تغيير حالة الدفع إلى "موافق عليها"
        </div>

        <table>
            <thead>
                <tr>
                    <th>رقم</th>
                    <th>اسم العميل</th>
                    <th>نوع العملية</th>
                    <th>المبلغ (SDG)</th>
                    <th>المبلغ (USD)</th>
                    <th>البيان</th>
                    <th>طريقة الدفع</th>
                    <th>رقم المرجع</th>
                    <th>التاريخ</th>
                    <th>صورة الإيصال</th>
                    <th>إجراء</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): $i = 1; ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($row['client_name']) ?></td>
                            <td><?= htmlspecialchars($row['type']) ?></td>
                            <td><?= number_format($row['amount'], 2) ?></td>
                            <td><?= isset($row['amount_usd']) && $row['amount_usd'] > 0 ? number_format($row['amount_usd'], 2) : '—' ?></td>
                            <td><?= htmlspecialchars($row['description']) ?></td>
                            <td><?= htmlspecialchars($row['payment_method']) ?></td>
                            <td><?= htmlspecialchars($row['reference_number']) ?></td>
                            <td><?= $row['created_at'] ?></td>
                            <td>
                                <?php 
                                $image_path = !empty($row['receipt_image']) ? $row['receipt_image'] : $row['proof'];
                                if (!empty($image_path)): 
                                    $full_path = 'uploads/' . $image_path;
                                ?>
                                    <a href="<?= $full_path ?>" target="_blank">
                                        <img src="<?= $full_path ?>" class="thumbnail" alt="صورة الإيصال">
                                    </a>
                                <?php else: ?>
                                    <span class="no-image">لا يوجد إيصال</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($row['type'] === 'قبض'): ?>
                                    <a class="button" href="?approve=<?= $row['id'] ?>" onclick="return confirm('تأكيد الموافقة؟ سيتم تنفيذ جميع العمليات المرتبطة')">موافقة</a>
                                <?php else: ?>
                                    <span>غير متاح</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="11" style="text-align: center; padding: 20px;">
                            لا توجد معاملات قيد المراجعة حالياً
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>