<?php
// app/Views/transactions/create.php
include __DIR__ . '/../layouts/header.php';

// Get transaction types
$db = \App\Core\Database::getInstance();
$stmt = $db->query("SELECT * FROM transaction_types ORDER BY name");
$transactionTypes = $stmt->fetchAll();

// Get exchange rates
$stmt = $db->query("SELECT * FROM settings WHERE setting_key LIKE 'exchange_rate_%'");
$ratesRaw = $stmt->fetchAll();
$exchangeRates = [];
foreach ($ratesRaw as $rate) {
    $exchangeRates[$rate['setting_key']] = $rate['setting_value'];
}

// Calculate USD to SDG rate
$usdToRmb = floatval($exchangeRates['exchange_rate_usd_rmb'] ?? 7.20);
$sdgToRmb = floatval($exchangeRates['exchange_rate_sdg_rmb'] ?? 0.012);
$usdToSdg = $sdgToRmb > 0 ? $usdToRmb / $sdgToRmb : 600;
?>

<div class="col-md-12 p-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><?= __('transactions.add_new') ?></h4>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= $error ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="/transactions/create" id="transaction-form">
                        <!-- Basic Information -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="transaction_date" class="form-label">
                                    <?= __('transactions.transaction_date') ?> *
                                </label>
                                <input type="date" 
                                       class="form-control" 
                                       id="transaction_date" 
                                       name="transaction_date" 
                                       value="<?= $data['transaction_date'] ?? date('Y-m-d') ?>" 
                                       required>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="client_id" class="form-label">
                                    <?= __('transactions.client') ?> *
                                </label>
                                <select class="form-select" id="client_id" name="client_id" required>
                                    <option value=""><?= __('search') ?>...</option>
                                    <?php foreach ($clients as $client): ?>
                                    <option value="<?= $client['id'] ?>" <?= ($data['client_id'] ?? '') == $client['id'] ? 'selected' : '' ?>>
                                        <?= lang() == 'ar' ? ($client['name_ar'] ?? $client['name']) : $client['name'] ?> 
                                        (<?= $client['client_code'] ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="transaction_type_id" class="form-label">
                                    <?= __('transactions.type') ?> *
                                </label>
                                <select class="form-select" id="transaction_type_id" name="transaction_type_id" required>
                                    <option value=""><?= __('search') ?>...</option>
                                    <?php foreach ($transactionTypes as $type): ?>
                                    <option value="<?= $type['id'] ?>" <?= ($data['transaction_type_id'] ?? '') == $type['id'] ? 'selected' : '' ?>>
                                        <?= lang() == 'ar' ? ($type['name_ar'] ?? $type['name']) : $type['name'] ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="invoice_no" class="form-label">
                                    <?= __('transactions.invoice_no') ?>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="invoice_no" 
                                       name="invoice_no" 
                                       value="<?= $data['invoice_no'] ?? '' ?>">
                            </div>
                            
                            <div class="col-md-3">
                                <label for="loading_no" class="form-label">
                                    <?= __('transactions.loading_no') ?>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="loading_no" 
                                       name="loading_no" 
                                       value="<?= $data['loading_no'] ?? '' ?>">
                            </div>
                            
                            <div class="col-md-3">
                                <label for="bank_name" class="form-label">
                                    <?= __('cashbox.bank_name') ?>
                                </label>
                                <select class="form-select" id="bank_name" name="bank_name">
                                    <option value=""><?= __('search') ?>...</option>
                                    <?php 
                                    // Get banks list from settings
                                    $banksStmt = $db->query("SELECT setting_value FROM settings WHERE setting_key = 'banks_list'");
                                    $banksResult = $banksStmt->fetch();
                                    $banks = explode(',', $banksResult['setting_value'] ?? 'Bank of Khartoum,Faisal Islamic Bank');
                                    foreach ($banks as $bank):
                                    ?>
                                    <option value="<?= trim($bank) ?>" <?= ($data['bank_name'] ?? '') == trim($bank) ? 'selected' : '' ?>>
                                        <?= trim($bank) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="description" class="form-label">
                                    <?= __('transactions.description') ?>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="description" 
                                       name="description" 
                                       value="<?= $data['description'] ?? '' ?>">
                            </div>
                        </div>
                        
                        <!-- Amounts Section -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="mb-0"><?= __('amount') ?> (RMB)</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="goods_amount_rmb" class="form-label">
                                            <?= __('transactions.goods_amount') ?>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">¥</span>
                                            <input type="number" 
                                                   class="form-control calculate-total" 
                                                   id="goods_amount_rmb" 
                                                   name="goods_amount_rmb" 
                                                   step="0.01" 
                                                   min="0"
                                                   value="<?= $data['goods_amount_rmb'] ?? '0' ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label for="commission_rmb" class="form-label">
                                            <?= __('transactions.commission') ?>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">¥</span>
                                            <input type="number" 
                                                   class="form-control calculate-total" 
                                                   id="commission_rmb" 
                                                   name="commission_rmb" 
                                                   step="0.01" 
                                                   min="0"
                                                   value="<?= $data['commission_rmb'] ?? '0' ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label for="total_amount_rmb" class="form-label">
                                            <?= __('transactions.total_amount') ?>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">¥</span>
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="total_amount_rmb" 
                                                   name="total_amount_rmb" 
                                                   step="0.01" 
                                                   readonly
                                                   value="<?= $data['total_amount_rmb'] ?? '0' ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label for="payment_rmb" class="form-label">
                                            <?= __('transactions.payment') ?>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">¥</span>
                                            <input type="number" 
                                                   class="form-control calculate-balance" 
                                                   id="payment_rmb" 
                                                   name="payment_rmb" 
                                                   step="0.01" 
                                                   min="0"
                                                   value="<?= $data['payment_rmb'] ?? '0' ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-md-3 offset-md-9">
                                        <label for="balance_rmb" class="form-label">
                                            <?= __('balance') ?>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">¥</span>
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="balance_rmb" 
                                                   name="balance_rmb" 
                                                   step="0.01" 
                                                   readonly
                                                   value="<?= $data['balance_rmb'] ?? '0' ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-md-3">
                                        <label for="payment_sdg" class="form-label">
                                            <?= __('transactions.payment') ?> (SDG)
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">SDG</span>
                                            <input type="number" 
                                                   class="form-control currency-convert" 
                                                   id="payment_sdg" 
                                                   name="payment_sdg" 
                                                   data-currency="SDG"
                                                   step="0.01" 
                                                   min="0"
                                                   value="<?= $data['payment_sdg'] ?? '0' ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label for="exchange_rate" class="form-label">
                                            <?= __('settings.exchange_rates') ?>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">1 USD =</span>
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="exchange_rate_usd_sdg" 
                                                   step="0.01" 
                                                   value="<?= $exchangeRate ?? '600' ?>"
                                                   readonly>
                                            <span class="input-group-text">SDG</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- USD Section -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="mb-0"><?= __('transactions.shipping') ?> (USD)</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label for="shipping_usd" class="form-label">
                                            <?= __('transactions.shipping') ?>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="shipping_usd" 
                                                   name="shipping_usd" 
                                                   step="0.01" 
                                                   min="0"
                                                   value="<?= $data['shipping_usd'] ?? '0' ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label for="payment_usd" class="form-label">
                                            <?= __('transactions.payment') ?>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="payment_usd" 
                                                   name="payment_usd" 
                                                   step="0.01" 
                                                   min="0"
                                                   value="<?= $data['payment_usd'] ?? '0' ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label for="balance_usd" class="form-label">
                                            <?= __('balance') ?>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="balance_usd" 
                                                   name="balance_usd" 
                                                   step="0.01" 
                                                   readonly
                                                   value="<?= $data['balance_usd'] ?? '0' ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Cashbox Effect -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="affects_cashbox" 
                                               name="affects_cashbox" 
                                               value="1"
                                               <?= ($data['affects_cashbox'] ?? false) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="affects_cashbox">
                                            <?= __('transactions.affects_cashbox') ?>
                                        </label>
                                    </div>
                                </h5>
                            </div>
                            <div class="card-body" id="cashbox-section" style="display: none;">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="cashbox_type" class="form-label">
                                            <?= __('cashbox.movement_type') ?>
                                        </label>
                                        <select class="form-select" id="cashbox_type" name="cashbox_type">
                                            <option value="in"><?= __('cashbox.in') ?></option>
                                            <option value="out"><?= __('cashbox.out') ?></option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label for="cashbox_category" class="form-label">
                                            <?= __('cashbox.category') ?>
                                        </label>
                                        <select class="form-select" id="cashbox_category" name="cashbox_category">
                                            <option value="customer_transfer"><?= __('cashbox.customer_transfer') ?></option>
                                            <option value="office_transfer"><?= __('cashbox.office_transfer') ?></option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label for="bank_name" class="form-label">
                                            <?= __('cashbox.bank_name') ?>
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="bank_name" 
                                               name="bank_name">
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label for="receipt_no" class="form-label">
                                            <?= __('cashbox.receipt_no') ?>
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="receipt_no" 
                                               name="receipt_no">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info" role="alert">
                            <i class="bi bi-info-circle"></i>
                            <?= __('validation.required') ?> *
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="/transactions" class="btn btn-secondary">
                                <i class="bi bi-arrow-<?= isRTL() ? 'right' : 'left' ?>"></i> <?= __('back') ?>
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> <?= __('save') ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Exchange rates from settings
const exchangeRates = {
    USD_SDG: <?= $usdToSdg ?>,
    SDG_USD: <?= 1 / $usdToSdg ?>
};

// Calculate totals
document.querySelectorAll('.calculate-total').forEach(input => {
    input.addEventListener('input', calculateTotal);
});

document.querySelectorAll('.calculate-balance').forEach(input => {
    input.addEventListener('input', calculateBalance);
});

function calculateTotal() {
    const goods = parseFloat(document.getElementById('goods_amount_rmb').value) || 0;
    const commission = parseFloat(document.getElementById('commission_rmb').value) || 0;
    document.getElementById('total_amount_rmb').value = (goods + commission).toFixed(2);
    calculateBalance();
}

function calculateBalance() {
    // RMB balance
    const total = parseFloat(document.getElementById('total_amount_rmb').value) || 0;
    const payment = parseFloat(document.getElementById('payment_rmb').value) || 0;
    document.getElementById('balance_rmb').value = (total - payment).toFixed(2);
    
    // USD balance
    const shipping = parseFloat(document.getElementById('shipping_usd').value) || 0;
    const paymentUsd = parseFloat(document.getElementById('payment_usd').value) || 0;
    document.getElementById('balance_usd').value = (shipping - paymentUsd).toFixed(2);
}

// Currency conversion between USD and SDG
document.getElementById('payment_usd').addEventListener('input', function() {
    const usdAmount = parseFloat(this.value) || 0;
    const sdgAmount = usdAmount * exchangeRates.USD_SDG;
    document.getElementById('payment_sdg').value = sdgAmount.toFixed(2);
    calculateBalance();
});

document.getElementById('payment_sdg').addEventListener('input', function() {
    const sdgAmount = parseFloat(this.value) || 0;
    const usdAmount = sdgAmount * exchangeRates.SDG_USD;
    document.getElementById('payment_usd').value = usdAmount.toFixed(2);
    calculateBalance();
});

// Shipping USD balance
document.getElementById('shipping_usd').addEventListener('input', calculateBalance);

// Cashbox section toggle
document.getElementById('affects_cashbox').addEventListener('change', function() {
    document.getElementById('cashbox-section').style.display = this.checked ? 'block' : 'none';
    
    // Auto-fill cashbox amounts
    if (this.checked) {
        // Hidden fields for cashbox amounts - they'll be set when submitting
    }
});

// Initialize
calculateTotal();
if (document.getElementById('affects_cashbox').checked) {
    document.getElementById('cashbox-section').style.display = 'block';
}

// Before form submission, set cashbox amounts
document.getElementById('transaction-form').addEventListener('submit', function(e) {
    if (document.getElementById('affects_cashbox').checked) {
        // Create hidden inputs for cashbox amounts
        const cashboxRmb = document.createElement('input');
        cashboxRmb.type = 'hidden';
        cashboxRmb.name = 'cashbox_rmb';
        cashboxRmb.value = document.getElementById('payment_rmb').value;
        this.appendChild(cashboxRmb);
        
        const cashboxUsd = document.createElement('input');
        cashboxUsd.type = 'hidden';
        cashboxUsd.name = 'cashbox_usd';
        cashboxUsd.value = document.getElementById('payment_usd').value;
        this.appendChild(cashboxUsd);
        
        const cashboxSdg = document.createElement('input');
        cashboxSdg.type = 'hidden';
        cashboxSdg.name = 'cashbox_sdg';
        cashboxSdg.value = document.getElementById('payment_sdg').value;
        this.appendChild(cashboxSdg);
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>