<?php
// app/Views/loadings/view_loading.php
include __DIR__ . '/../layouts/header.php';

// Office list
$offices = [
    'main' => __('offices.main'),
    'port_sudan' => __('offices.port_sudan'),
    'khartoum' => __('offices.khartoum'),
    'kassala' => __('offices.kassala'),
];

// Status list
$statuses = [
    'pending' => ['label' => __('loadings.status.pending'), 'class' => 'warning'],
    'shipped' => ['label' => __('loadings.status.shipped'), 'class' => 'info'],
    'arrived' => ['label' => __('loadings.status.arrived'), 'class' => 'primary'],
    'cleared' => ['label' => __('loadings.status.cleared'), 'class' => 'success'],
    'cancelled' => ['label' => __('loadings.status.cancelled'), 'class' => 'danger'],
];

// Payment methods
$paymentMethods = [
    'cash' => __('payment.cash'),
    'transfer' => __('payment.transfer'),
    'check' => __('payment.check'),
    'credit' => __('payment.credit'),
];

// Get exchange rate for display
$db = \App\Core\Database::getInstance();
$stmt = $db->query("SELECT setting_value FROM settings WHERE setting_key = 'exchange_rate_usd_rmb'");
$rate = $stmt->fetch();
$usdToRmb = $rate ? floatval($rate['setting_value']) : 7.20;
$shippingRmb = $loading['shipping_usd'] * $usdToRmb;
?>

<div class="col-md-12 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h2><?= __('loadings.view_details') ?></h2>
        <div>
            <a href="/loadings/edit/<?= $loading['id'] ?>" class="btn btn-warning">
                <i class="bi bi-pencil"></i> <?= __('edit') ?>
            </a>
            <button class="btn btn-primary" onclick="window.print()">
                <i class="bi bi-printer"></i> <?= __('print') ?>
            </button>
            <a href="/loadings" class="btn btn-secondary">
                <i class="bi bi-arrow-<?= isRTL() ? 'right' : 'left' ?>"></i> <?= __('back') ?>
            </a>
        </div>
    </div>
    
    <div class="row">
        <!-- Main Details -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <?= __('loadings.container_no') ?>: <?= $loading['container_no'] ?>
                        <span class="badge bg-<?= $statuses[$loading['status']]['class'] ?> float-end">
                            <?= $statuses[$loading['status']]['label'] ?>
                        </span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong><?= __('loadings.shipping_date') ?>:</strong>
                            <?= date('d/m/Y', strtotime($loading['shipping_date'])) ?>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong><?= __('loadings.claim_number') ?>:</strong>
                            <?= $loading['claim_number'] ?: '-' ?>
                        </div>
                        <div class="col-md-6">
                            <strong><?= __('loadings.office') ?>:</strong>
                            <?= $loading['office'] ? ($offices[$loading['office']] ?? $loading['office']) : '-' ?>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h6><?= __('loadings.client_info') ?></h6>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong><?= __('loadings.client_code') ?>:</strong>
                            <?= $loading['client_code'] ?>
                        </div>
                        <div class="col-md-8">
                            <strong><?= __('loadings.client_name') ?>:</strong>
                            <?= $loading['client_name'] ?? $loading['client_name_db'] ?>
                            <?php if ($loading['client_phone']): ?>
                                <br><small class="text-muted">
                                    <i class="bi bi-telephone"></i> <?= $loading['client_phone'] ?>
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($loading['item_description']): ?>
                    <div class="row mb-3">
                        <div class="col-12">
                            <strong><?= __('loadings.item_description') ?>:</strong>
                            <?= $loading['item_description'] ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong><?= __('loadings.cartons_count') ?>:</strong>
                            <?= number_format($loading['cartons_count']) ?>
                        </div>
                    </div>
                    
                    <?php if ($loading['notes']): ?>
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <strong><?= __('notes') ?>:</strong>
                            <p class="mb-0"><?= nl2br(htmlspecialchars($loading['notes'])) ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Financial Summary -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><?= __('loadings.financial_details') ?></h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><?= __('loadings.purchase') ?></td>
                            <td class="text-end">¥<?= number_format($loading['purchase_amount'], 2) ?></td>
                        </tr>
                        <tr>
                            <td><?= __('loadings.commission') ?></td>
                            <td class="text-end">¥<?= number_format($loading['commission_amount'], 2) ?></td>
                        </tr>
                        <tr class="border-top">
                            <th><?= __('loadings.total') ?></th>
                            <th class="text-end">¥<?= number_format($loading['total_amount'], 2) ?></th>
                        </tr>
                        <tr>
                            <td><?= __('loadings.shipping') ?> (USD)</td>
                            <td class="text-end">$<?= number_format($loading['shipping_usd'], 2) ?></td>
                        </tr>
                        <tr>
                            <td><?= __('loadings.shipping') ?> (RMB)</td>
                            <td class="text-end">¥<?= number_format($shippingRmb, 2) ?></td>
                        </tr>
                        <tr class="border-top border-bottom">
                            <th><?= __('loadings.grand_total') ?></th>
                            <th class="text-end text-primary">¥<?= number_format($loading['total_with_shipping'], 2) ?></th>
                        </tr>
                    </table>
                    
                    <small class="text-muted">
                        <?= __('settings.exchange_rates') ?>: 1 USD = <?= $usdToRmb ?> RMB
                    </small>
                </div>
            </div>
            
            <!-- Metadata -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><?= __('info') ?></h6>
                </div>
                <div class="card-body">
                    <small>
                        <p class="mb-1">
                            <strong><?= __('created_by') ?>:</strong><br>
                            <?= $loading['created_by_name'] ?? '-' ?>
                        </p>
                        <p class="mb-1">
                            <strong><?= __('created_at') ?>:</strong><br>
                            <?= date('Y-m-d H:i', strtotime($loading['created_at'])) ?>
                        </p>
                        <?php if ($loading['updated_by']): ?>
                        <p class="mb-0">
                            <strong><?= __('updated_by') ?>:</strong><br>
                            <?= $loading['updated_by_name'] ?><br>
                            <?= date('Y-m-d H:i', strtotime($loading['updated_at'])) ?>
                        </p>
                        <?php endif; ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="row mt-4 no-print">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h6><?= __('actions') ?></h6>
                    <div class="btn-group" role="group">
                        <?php if ($loading['status'] === 'pending'): ?>
                        <button class="btn btn-info" onclick="updateStatus('shipped')">
                            <i class="bi bi-truck"></i> <?= __('loadings.mark_shipped') ?>
                        </button>
                        <?php elseif ($loading['status'] === 'shipped'): ?>
                        <button class="btn btn-success" onclick="updateStatus('arrived')">
                            <i class="bi bi-check-circle"></i> <?= __('loadings.mark_arrived') ?>
                        </button>
                        <?php elseif ($loading['status'] === 'arrived'): ?>
                        <button class="btn btn-primary" onclick="updateStatus('cleared')">
                            <i class="bi bi-clipboard-check"></i> <?= __('loadings.mark_cleared') ?>
                        </button>
                        <?php endif; ?>
                        
                        <?php if ($loading['status'] !== 'cancelled' && $_SESSION['user_role'] === 'admin'): ?>
                        <button class="btn btn-danger" onclick="updateStatus('cancelled')">
                            <i class="bi bi-x-circle"></i> <?= __('cancel') ?>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print {
        display: none !important;
    }
    
    .card {
        border: 1px solid #000 !important;
    }
    
    body {
        font-size: 12px;
    }
}
</style>

<script>
function updateStatus(status) {
    if (confirm('<?= __("messages.confirm_status_change") ?>')) {
        fetch(`/loadings/update-status/<?= $loading['id'] ?>`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ status: status })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || '<?= __("messages.operation_failed") ?>');
            }
        });
    }
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>