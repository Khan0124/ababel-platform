<?php
session_start();
include 'config.php';

// التحقق من تسجيل الدخول والصلاحيات
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'] ?? '';
$can_delete = in_array($role, ['مدير عام', 'مدير']);

// جلب بيانات العميل
$client_id = (int)($_GET['id'] ?? 0);
$client = $conn->query("SELECT * FROM clients WHERE id = $client_id")->fetch_assoc();

if (!$client) {
    die("<div class='alert alert-danger text-center mt-5'>⚠️ لا يوجد عميل بهذا المعرف</div>");
}

// تحسين الاستعلامات باستخدام JOIN مع إضافة display_description
$transactions = $conn->query("
    SELECT t.*, 
           c.container_number,
           COALESCE(SUM(p.amount), 0) as paid_amount,
           COUNT(p.id) as payment_count,
           IF(t.type = 'قبض' AND t.related_claim_id > 0, 
              (SELECT description FROM transactions WHERE id = t.related_claim_id), 
              t.description
           ) AS display_description
    FROM transactions t
    LEFT JOIN containers c ON t.container_id = c.id
    LEFT JOIN transactions p ON p.related_claim_id = t.id AND p.type = 'قبض'
    WHERE t.client_id = $client_id
    GROUP BY t.id
    ORDER BY t.id DESC
");

// جلب الحاويات الخاصة بالعميل
$containers = $conn->query("
    SELECT * FROM containers 
    WHERE code = '{$client['code']}' 
    ORDER BY entry_date DESC
");

// جلب سعر الصرف
$rate_result = $conn->query("SELECT exchange_rate FROM settings ORDER BY id DESC LIMIT 1");
$exchange_rate = $rate_result && $rate_result->num_rows > 0 ? (float)$rate_result->fetch_assoc()['exchange_rate'] : 1;

// جلب السجلات
$registers = $conn->query("SELECT id, name FROM registers");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بروفايل العميل - <?= htmlspecialchars($client['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .badge-مطالبة { background-color: #ffc107; color: #000; }
        .badge-قبض { background-color: #28a745; }
        .badge-استرداد { background-color: #dc3545; }
        .status-badge { font-size: 0.9rem; padding: 0.35em 0.65em; }
        .status-open { background-color: #6c757d; }
        .status-partial { background-color: #fd7e14; }
        .status-paid { background-color: #20c997; }
        .transaction-row { transition: all 0.2s ease; }
        .transaction-row:hover { background-color: #f8f9fa; }
        .hidden-field { display: none; }
        .card-header { font-weight: 600; }
        .form-label { font-weight: 500; }
    </style>
</head>
<body>
<div class="container py-4">
    <!-- معلومات العميل -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <span>👤 بيانات العميل</span>
            <a href="clients_list.php" class="btn btn-sm btn-light">← رجوع</a>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <strong>الاسم:</strong> <?= htmlspecialchars($client['name']) ?>
                </div>
                <div class="col-md-4 mb-3">
                    <strong>رقم الهاتف:</strong> <?= htmlspecialchars($client['phone']) ?>
                </div>
                <div class="col-md-4 mb-3">
                    <strong>الرمز:</strong> <?= htmlspecialchars($client['code']) ?>
                </div>
                <div class="col-md-4 mb-3">
                    <strong>تاريخ الإضافة:</strong> <?= $client['start_date'] ?>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label text-success">الرصيد الحالي:</label>
                    <input class="form-control bg-light" value="<?= number_format($client['balance'] ?? 0, 2) ?> جنيه" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-primary">رصيد التأمين:</label>
                    <input class="form-control bg-light" value="<?= number_format($client['insurance_balance'] ?? 0, 2) ?> جنيه" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted">💵 الرصيد بالدولار:</label>
                    <input class="form-control bg-light" value="<?= number_format(($client['balance'] ?? 0) / $exchange_rate, 2) ?> $" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted">💵 تأمين بالدولار:</label>
                    <input class="form-control bg-light" value="<?= number_format(($client['insurance_balance'] ?? 0) / $exchange_rate, 2) ?> $" readonly>
                </div>
            </div>
        </div>
    </div>

    <!-- كشف الحساب -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <span>📑 كشف حساب العميل</span>
            <button class="btn btn-sm btn-outline-light" onclick="printTransactions()">
                <i class="bi bi-printer"></i> طباعة
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered text-center align-middle mb-0" id="transactions-table">
                    <thead class="table-light">
                        <tr>
                            <th width="120">التاريخ</th>
                            <th>البيان</th>
                            <th width="120">المبلغ</th>
                            <th width="120">المبلغ $</th>
                            <th width="100">النوع</th>
                            <th width="120">الحاوية</th>
                            <th width="100">الحالة</th>
                            <th width="120">المدفوع</th>
                            <th width="150">خيارات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($transactions->num_rows > 0): ?>
                            <?php while($row = $transactions->fetch_assoc()): ?>
                                <tr class="transaction-row">
                                    <td><?= date('Y-m-d', strtotime($row['created_at'])) ?></td>
                                    <!-- استخدمنا display_description الجديدة -->
                                    <td><?= htmlspecialchars($row['display_description']) ?></td>
                                    <td><?= number_format($row['amount'], 2) ?> ج.س</td>
                                    <td><?= number_format($row['amount_usd'] ?? 0, 2) ?> $</td>
                                    <td>
                                        <span class="badge badge-<?= $row['type'] ?>"><?= $row['type'] ?></span>
                                    </td>
                                    <td><?= $row['container_number'] ?: '-' ?></td>
                                    <td>
                                        <?php if ($row['type'] === 'مطالبة'): ?>
                                            <?php
                                            $status = $row['status'] ?? 'open';
                                            $status_text = [
                                                'open' => 'مفتوحة',
                                                'partial' => 'جزئي',
                                                'paid' => 'مدفوعة'
                                            ][$status];
                                            ?>
                                            <span class="status-badge status-<?= $status ?>"><?= $status_text ?></span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= number_format($row['paid_amount'] ?? 0, 2) ?> ج.س
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="receipt_view.php?id=<?= $row['id'] ?>" class="btn btn-secondary" title="عرض">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="edit_receipt.php?id=<?= $row['id'] ?>" class="btn btn-warning" title="تعديل">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="print_receipt.php?id=<?= $row['id'] ?>" class="btn btn-info" title="طباعة">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                            <?php if ($can_delete): ?>
                                                <a href="delete_receipt.php?id=<?= $row['id'] ?>" class="btn btn-danger" title="حذف" onclick="return confirm('هل أنت متأكد من حذف هذه المعاملة؟')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-4">لا توجد معاملات مسجلة لهذا العميل</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- إضافة معاملة جديدة -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-success text-white">➕ إضافة معاملة جديدة</div>
        <div class="card-body">
            <form action="insert_transaction.php" method="POST" enctype="multipart/form-data" id="transaction-form">
                <input type="hidden" name="client_id" value="<?= $client['id'] ?>">
                <input type="hidden" name="client_code" value="<?= $client['code'] ?>">
                <input type="hidden" name="exchange_rate" value="<?= $exchange_rate ?>">

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">نوع المعاملة <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" id="type-select" required>
                            <option value="">اختر النوع</option>
                            <option value="مطالبة">مطالبة</option>
                            <option value="قبض">قبض</option>
                            <option value="استرداد">استرداد</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3" id="related-claim-group" style="display:none;">
                        <label class="form-label">ربط بمطالبة</label>
                        <select name="related_claim_id" class="form-select" id="related-claim-select">
                            <option value="">-- اختر مطالبة --</option>
                            <?php
                            $claims = $conn->query("
                                SELECT id, description, amount, status 
                                FROM transactions 
                                WHERE client_id = $client_id AND type = 'مطالبة'
                                ORDER BY created_at DESC
                            ");
                            while ($claim = $claims->fetch_assoc()):
                                $disabled = ($claim['status'] === 'paid') ? 'disabled' : '';
                                $desc = htmlspecialchars($claim['description']) . " - " . number_format($claim['amount'], 2) . " ج.س";
                            ?>
                                <option value="<?= $claim['id'] ?>" <?= $disabled ?> data-amount="<?= $claim['amount'] ?>">
                                    <?= $desc ?> (<?= $claim['status'] === 'paid' ? 'مدفوعة' : ($claim['status'] === 'partial' ? 'جزئي' : 'مفتوحة') ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3" id="description-group">
                        <label class="form-label">البيان <span class="text-danger">*</span></label>
                        <select name="description" class="form-select" id="description-select" required>
                            <option value="">اختر البيان</option>
                            <option value="سجل">سجل</option>
                            <option value="موانئ">موانئ</option>
                            <option value="أرضيات">أرضيات</option>
                            <option value="تختيم">تختيم</option>
                            <option value="تأمين">تأمين</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3" id="register-group" style="display:none;">
                        <label class="form-label">السجل</label>
                        <select name="register_id" class="form-select">
                            <option value="">اختر سجل</option>
                            <?php 
                            $registers->data_seek(0); // إعادة تعيين مؤشر النتائج
                            while($reg = $registers->fetch_assoc()): ?>
                                <option value="<?= $reg['id'] ?>"><?= htmlspecialchars($reg['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">المبلغ <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" id="amount-input" required min="0.01" step="0.01">
                    </div>
                    
                    <div class="col-md-3" id="payment-method-group">
                        <label class="form-label">طريقة الدفع</label>
                        <select name="payment_method" class="form-select">
                            <option value="">اختر طريقة</option>
                            <option value="كاش">كاش</option>
                            <option value="بنك">بنك</option>
                            <option value="أوكاش">أوكاش</option>
                            <option value="فوري">فوري</option>
                            <option value="شيك">شيك</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3" id="container-group">
                        <label class="form-label">رقم الحاوية <span class="text-danger">*</span></label>
                        <select name="container_id" class="form-select" required id="container-select">
                            <option value="">اختر الحاوية</option>
                            <?php 
                            $containers->data_seek(0); // إعادة تعيين مؤشر النتائج
                            while($c = $containers->fetch_assoc()): ?>
                                <option value="<?= $c['id'] ?>"><?= $c['container_number'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">سعر الصرف</label>
                        <input type="text" class="form-control" value="<?= number_format($exchange_rate, 2) ?>" readonly>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label">ملاحظات</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-success px-4">
                        <i class="bi bi-save"></i> حفظ المعاملة
                    </button>
                    <button type="reset" class="btn btn-outline-secondary">
                        <i class="bi bi-eraser"></i> مسح النموذج
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- الحاويات المرتبطة -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-info text-white">📦 الحاويات المرتبطة</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered text-center align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>تاريخ الشحن</th>
                            <th>رقم الحاوية</th>
                            <th>الكراتين</th>
                            <th>اللودنق</th>
                            <th>الناقلة</th>
                            <th>الصنف</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $containers->data_seek(0); // إعادة تعيين مؤشر النتائج
                        if ($containers->num_rows > 0): ?>
                            <?php while($c = $containers->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $c['entry_date'] ?></td>
                                    <td><?= $c['container_number'] ?></td>
                                    <td><?= $c['carton_count'] ?></td>
                                    <td><?= $c['loading_number'] ?></td>
                                    <td><?= $c['carrier'] ?></td>
                                    <td><?= $c['category'] ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">لا توجد حاويات مسجلة</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type-select');
    const descGroup = document.getElementById('description-group');
    const descSelect = document.getElementById('description-select');
    const relatedGroup = document.getElementById('related-claim-group');
    const relatedSelect = document.getElementById('related-claim-select');
    const registerGroup = document.getElementById('register-group');
    const paymentGroup = document.getElementById('payment-method-group');
    const containerGroup = document.getElementById('container-group');
    const containerSelect = document.getElementById('container-select');
    const amountInput = document.getElementById('amount-input');
    
    // تحديث الحقول حسب نوع المعاملة
    function updateFields() {
        const type = typeSelect.value;
        const relatedClaimSelected = relatedSelect.value !== '';
        
        // إدارة حقول طريقة الدفع: تظهر للمطالبة والقبض وتختفي للاسترداد
        if (type === 'استرداد') {
            paymentGroup.style.display = 'none';
        } else {
            paymentGroup.style.display = 'block';
        }

        // إدارة حقول المطالبة المرتبطة: تظهر فقط لمعاملة القبض
        if (type === 'قبض') {
            relatedGroup.style.display = 'block';
            // إخفاء حقل البيان فقط إذا تم اختيار مطالبة مرتبطة
            descGroup.style.display = relatedClaimSelected ? 'none' : 'block';
            descSelect.required = !relatedClaimSelected;
        } else {
            relatedGroup.style.display = 'none';
            descGroup.style.display = 'block';
            descSelect.required = true;
            relatedSelect.value = '';
        }
        
        // إدارة حقل الحاوية: يخفي عند معاملة قبض مرتبطة بمطالبة
        if (type === 'قبض' && relatedClaimSelected) {
            containerGroup.style.display = 'none';
            containerSelect.removeAttribute('required');
        } else {
            containerGroup.style.display = 'block';
            containerSelect.setAttribute('required', 'required');
        }
        
        // إدارة حقول السجل: تظهر فقط للمطالبة إذا كان البيان "سجل"
        if (type !== 'قبض' && descSelect.value === 'سجل') {
            registerGroup.style.display = 'block';
        } else {
            registerGroup.style.display = 'none';
        }
        
        // إدارة محتوى حقل البيان: للاسترداد يكون البيان "تأمين" فقط
        if (type === 'استرداد') {
            // نضع خيار واحد فقط وهو التأمين
            descSelect.innerHTML = '<option value="تأمين" selected>تأمين</option>';
            descSelect.readOnly = true;
        } else {
            // إذا لم يكن استرداد نعيد الخيارات العادية
            if (descSelect.innerHTML.indexOf('سجل') === -1) {
                descSelect.innerHTML = `
                    <option value="">اختر البيان</option>
                    <option value="سجل">سجل</option>
                    <option value="موانئ">موانئ</option>
                    <option value="أرضيات">أرضيات</option>
                    <option value="تختيم">تختيم</option>
                    <option value="تأمين">تأمين</option>
                `;
            }
            descSelect.readOnly = false;
        }
    }
    
    // عند تغيير نوع المعاملة
    typeSelect.addEventListener('change', updateFields);
    
    // عند تغيير البيان
    descSelect.addEventListener('change', function() {
        if (this.value === 'سجل' && typeSelect.value !== 'قبض') {
            registerGroup.style.display = 'block';
        } else {
            registerGroup.style.display = 'none';
        }
    });
    
    // عند اختيار مطالبة مرتبطة
    relatedSelect.addEventListener('change', function() {
        updateFields(); // نحدث الحقول بناء على التغيير
        if (this.value) {
            const selectedOption = this.options[this.selectedIndex];
            const claimAmount = parseFloat(selectedOption.getAttribute('data-amount'));
            
            // تعبئة المبلغ التلقائي (اختياري)
            // amountInput.value = claimAmount.toFixed(2);
        }
    });
    
    // وظيفة طباعة كشف الحساب
    function printTransactions() {
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>كشف حساب العميل - ${document.title}</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        h2 { text-align: center; margin-bottom: 20px; }
                        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
                        th { background-color: #f2f2f2; }
                        .text-center { text-align: center; }
                        .badge { padding: 3px 6px; border-radius: 3px; font-weight: bold; }
                        .badge-مطالبة { background-color: #ffc107; color: #000; }
                        .badge-قبض { background-color: #28a745; color: white; }
                        .badge-استرداد { background-color: #dc3545; color: white; }
                    </style>
                </head>
                <body>
                    <h2>كشف حساب العميل - ${document.title}</h2>
                    ${document.getElementById('transactions-table').outerHTML}
                    <script>
                        window.onload = function() {
                            window.print();
                            setTimeout(function() { window.close(); }, 1000);
                        };
                    <\/script>
                </body>
            </html>
        `);
        printWindow.document.close();
    }
    
    // التشغيل الأولي
    updateFields();
});
</script>
</body>
</html>