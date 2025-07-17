<?php
// app/Views/loadings/add_loading.php
include __DIR__ . '/../layouts/header.php';

// Get clients for dropdown
$db = \App\Core\Database::getInstance();
$stmt = $db->query("SELECT id, client_code, name, name_ar, balance_rmb, balance_usd FROM clients WHERE status = 'active' ORDER BY name");
$clients = $stmt->fetchAll();

// Get exchange rates
$stmt = $db->query("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'exchange_rate_%'");
$rates = $stmt->fetchAll();
$exchangeRates = [];
foreach ($rates as $rate) {
    $exchangeRates[$rate['setting_key']] = $rate['setting_value'];
}

// Office list
$offices = [
    'main' => __('offices.main'),
    'port_sudan' => __('offices.port_sudan'),
    'khartoum' => __('offices.khartoum'),
    'kassala' => __('offices.kassala'),
];

// Payment methods
$paymentMethods = [
    'cash' => __('payment.cash'),
    'transfer' => __('payment.transfer'),
    'check' => __('payment.check'),
    'credit' => __('payment.credit'),
];
?>

<div class="col-md-12 p-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="bi bi-box-seam"></i> <?= __('loadings.add_new') ?>
                    </h4>
                    <a href="/loadings" class="btn btn-sm btn-secondary">
                        <i class="bi bi-list"></i> <?= __('loadings.view_list') ?>
                    </a>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= $_SESSION['error'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= $_SESSION['success'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>
                    
                    <form method="POST" action="/loadings/create" id="loading-form">
                        <!-- Row 1: Basic Information -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="shipping_date" class="form-label">
                                    <?= __('loadings.shipping_date') ?> <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       class="form-control" 
                                       id="shipping_date" 
                                       name="shipping_date" 
                                       value="<?= date('Y-m-d') ?>" 
                                       required>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="payment_method" class="form-label">
                                    <?= __('loadings.payment_method') ?> <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="payment_method" name="payment_method" required>
                                    <option value=""><?= __('select') ?>...</option>
                                    <?php foreach ($paymentMethods as $key => $method): ?>
                                        <option value="<?= $key ?>"><?= $method ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="claim_number" class="form-label">
                                    <?= __('loadings.claim_number') ?>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="claim_number" 
                                       name="claim_number" 
                                       placeholder="<?= __('loadings.internal_reference') ?>">
                            </div>
                            
                            <div class="col-md-3">
                                <label for="container_no" class="form-label">
                                    <?= __('loadings.container_no') ?> <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control text-uppercase" 
                                       id="container_no" 
                                       name="container_no" 
                                       placeholder="CMAU7702683"
                                       pattern="[A-Z]{4}[0-9]{7}"
                                       title="<?= __('loadings.container_format') ?>"
                                       required>
                                <small class="text-muted"><?= __('loadings.container_format_hint') ?></small>
                            </div>
                        </div>
                        
                        <!-- Row 2: Client Information -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="client_code" class="form-label">
                                    <?= __('loadings.client_code') ?> <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="client_code" 
                                       name="client_code" 
                                       placeholder="<?= __('loadings.enter_client_code') ?>"
                                       list="client-codes"
                                       required>
                                <datalist id="client-codes">
                                    <?php foreach ($clients as $client): ?>
                                        <option value="<?= $client['client_code'] ?>"><?= $client['name'] ?></option>
                                    <?php endforeach; ?>
                                </datalist>
                            </div>
                            
                            <div class="col-md-5">
                                <label for="client_name" class="form-label">
                                    <?= __('loadings.client_name') ?>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="client_name" 
                                       name="client_name" 
                                       readonly>
                                <input type="hidden" id="client_id" name="client_id">
                                <div id="client-info" class="mt-1 small text-muted"></div>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="item_description" class="form-label">
                                    <?= __('loadings.item_description') ?>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="item_description" 
                                       name="item_description" 
                                       placeholder="<?= __('loadings.cargo_type') ?>">
                            </div>
                        </div>
                        
                        <!-- Row 3: Financial Information -->
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><?= __('loadings.financial_details') ?></h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="cartons_count" class="form-label">
                                            <?= __('loadings.cartons_count') ?>
                                        </label>
                                        <input type="number" 
                                               class="form-control" 
                                               id="cartons_count" 
                                               name="cartons_count" 
                                               min="0"
                                               value="0">
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label for="purchase_amount" class="form-label">
                                            <?= __('loadings.purchase') ?> (¥)
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">¥</span>
                                            <input type="number" 
                                                   class="form-control calculate-total" 
                                                   id="purchase_amount" 
                                                   name="purchase_amount" 
                                                   step="0.01" 
                                                   min="0"
                                                   value="0">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label for="commission_amount" class="form-label">
                                            <?= __('loadings.commission') ?> (¥)
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">¥</span>
                                            <input type="number" 
                                                   class="form-control calculate-total" 
                                                   id="commission_amount" 
                                                   name="commission_amount" 
                                                   step="0.01" 
                                                   min="0"
                                                   value="0">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label for="total_amount" class="form-label">
                                            <?= __('loadings.total') ?> (¥)
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">¥</span>
                                            <input type="number" 
                                                   class="form-control bg-light" 
                                                   id="total_amount" 
                                                   name="total_amount" 
                                                   step="0.01" 
                                                   readonly
                                                   value="0">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-md-3">
                                        <label for="shipping_usd" class="form-label">
                                            <?= __('loadings.shipping') ?> ($)
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" 
                                                   class="form-control calculate-total" 
                                                   id="shipping_usd" 
                                                   name="shipping_usd" 
                                                   step="0.01" 
                                                   min="0"
                                                   value="0">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label class="form-label text-muted">
                                            <?= __('loadings.shipping_rmb') ?>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">¥</span>
                                            <input type="text" 
                                                   class="form-control bg-light" 
                                                   id="shipping_rmb_display" 
                                                   readonly
                                                   value="0">
                                        </div>
                                        <small class="text-muted">1 USD = <?= $exchangeRates['exchange_rate_usd_rmb'] ?? '7.20' ?> RMB</small>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label for="total_with_shipping" class="form-label">
                                            <?= __('loadings.total_with_shipping') ?> (¥)
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">¥</span>
                                            <input type="number" 
                                                   class="form-control bg-warning bg-opacity-10 fw-bold" 
                                                   id="total_with_shipping" 
                                                   name="total_with_shipping" 
                                                   step="0.01" 
                                                   readonly
                                                   value="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Row 4: Office Assignment -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="office" class="form-label">
                                    <?= __('loadings.office') ?>
                                </label>
                                <select class="form-select" id="office" name="office">
                                    <option value=""><?= __('loadings.no_office') ?></option>
                                    <?php foreach ($offices as $key => $office): ?>
                                        <option value="<?= $key ?>"><?= $office ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted"><?= __('loadings.office_notification_hint') ?></small>
                            </div>
                            
                            <div class="col-md-8">
                                <label for="notes" class="form-label">
                                    <?= __('notes') ?>
                                </label>
                                <textarea class="form-control" 
                                          id="notes" 
                                          name="notes" 
                                          rows="2"></textarea>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between">
                            <a href="/loadings" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> <?= __('cancel') ?>
                            </a>
                            <div>
                                <button type="reset" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise"></i> <?= __('reset') ?>
                                </button>
                                <button type="submit" class="btn btn-primary ms-2">
                                    <i class="bi bi-check-circle"></i> <?= __('save') ?>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Exchange rate
const usdToRmb = <?= $exchangeRates['exchange_rate_usd_rmb'] ?? 7.20 ?>;

// Clients data
const clientsData = <?= json_encode($clients) ?>;

// Client code input handler
document.getElementById('client_code').addEventListener('input', function() {
    const code = this.value.trim();
    const client = clientsData.find(c => c.client_code === code);
    
    if (client) {
        document.getElementById('client_id').value = client.id;
        document.getElementById('client_name').value = client.name;
        
        // Show client balance info
        let balanceInfo = '<?= __("balance") ?>: ';
        if (client.balance_rmb != 0) {
            balanceInfo += `¥${parseFloat(client.balance_rmb).toLocaleString()}`;
        }
        if (client.balance_usd != 0) {
            if (client.balance_rmb != 0) balanceInfo += ' | ';
            balanceInfo += `${parseFloat(client.balance_usd).toLocaleString()}`;
        }
        
        document.getElementById('client-info').innerHTML = balanceInfo;
        document.getElementById('client-info').className = 
            (client.balance_rmb > 0 || client.balance_usd > 0) 
            ? 'mt-1 small text-danger' 
            : 'mt-1 small text-success';
    } else {
        document.getElementById('client_id').value = '';
        document.getElementById('client_name').value = '';
        document.getElementById('client-info').innerHTML = '';
    }
});

// Calculate totals
function calculateTotals() {
    const purchase = parseFloat(document.getElementById('purchase_amount').value) || 0;
    const commission = parseFloat(document.getElementById('commission_amount').value) || 0;
    const shippingUsd = parseFloat(document.getElementById('shipping_usd').value) || 0;
    
    // Calculate total (purchase + commission)
    const total = purchase + commission;
    document.getElementById('total_amount').value = total.toFixed(2);
    
    // Convert shipping to RMB
    const shippingRmb = shippingUsd * usdToRmb;
    document.getElementById('shipping_rmb_display').value = shippingRmb.toFixed(2);
    
    // Calculate total with shipping
    const totalWithShipping = total + shippingRmb;
    document.getElementById('total_with_shipping').value = totalWithShipping.toFixed(2);
}

// Add event listeners for calculation
document.querySelectorAll('.calculate-total').forEach(input => {
    input.addEventListener('input', calculateTotals);
});

// Container number format enforcement
document.getElementById('container_no').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});

// Form validation
document.getElementById('loading-form').addEventListener('submit', function(e) {
    // Validate client
    if (!document.getElementById('client_id').value) {
        e.preventDefault();
        alert('<?= __("messages.invalid_client_code") ?>');
        document.getElementById('client_code').focus();
        return false;
    }
    
    // Validate container format
    const containerNo = document.getElementById('container_no').value;
    if (!/^[A-Z]{4}[0-9]{7}$/.test(containerNo)) {
        e.preventDefault();
        alert('<?= __("messages.invalid_container_format") ?>');
        document.getElementById('container_no').focus();
        return false;
    }
});

// Initialize
calculateTotals();
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>