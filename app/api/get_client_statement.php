<?php
header("Content-Type: application/json; charset=UTF-8");
require_once 'db_connect.php';

if (!isset($_GET['code'])) {
    echo json_encode(['status' => 'error', 'message' => 'كود العميل مطلوب']);
    exit;
}

$clientCode = $_GET['code'];

try {
    // الحصول على معرف العميل
    $clientQuery = "SELECT id FROM clients WHERE code = ?";
    $stmt = $conn->prepare($clientQuery);
    $stmt->bind_param("s", $clientCode);
    $stmt->execute();
    $clientResult = $stmt->get_result();
    
    if ($clientResult->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'العميل غير موجود']);
        exit;
    }
    
    $clientRow = $clientResult->fetch_assoc();
    $clientId = $clientRow['id'];
    
    // حساب الإجماليات
    $claimsQuery = "SELECT COALESCE(SUM(amount), 0) AS total_claims 
                    FROM transactions 
                    WHERE client_id = ? AND type = 'مطالبة'";
    $stmt = $conn->prepare($claimsQuery);
    $stmt->bind_param("i", $clientId);
    $stmt->execute();
    $claimsResult = $stmt->get_result()->fetch_assoc();
    $totalClaims = (float)$claimsResult['total_claims'];

    $paymentsQuery = "SELECT COALESCE(SUM(amount), 0) AS total_payments 
                      FROM transactions 
                      WHERE client_id = ? AND type = 'قبض'";
    $stmt = $conn->prepare($paymentsQuery);
    $stmt->bind_param("i", $clientId);
    $stmt->execute();
    $paymentsResult = $stmt->get_result()->fetch_assoc();
    $totalPayments = (float)$paymentsResult['total_payments'];

    $totalBalance = $totalClaims - $totalPayments;

    // جلب الحركات مع تفاصيل الحاوية
    $transactionsQuery = "
        SELECT 
            t.id,
            t.type,
            t.amount,
            t.description,
            t.container_id,
            t.created_at,
            c.container_number,
            t.related_claim_id,
            t.approval_status
        FROM transactions t
        LEFT JOIN containers c ON t.container_id = c.id
        WHERE t.client_id = ?
        ORDER BY t.created_at DESC
    ";

    $stmt = $conn->prepare($transactionsQuery);
    $stmt->bind_param("i", $clientId);
    $stmt->execute();
    $transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode([
        'status' => 'success',
        'total_balance' => $totalBalance,
        'total_claims' => $totalClaims,
        'total_payments' => $totalPayments,
        'transactions' => $transactions
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'حدث خطأ في الخادم: ' . $e->getMessage()
    ]);
}
?>