<?php
// app/Views/clients/statement.php
include __DIR__ . '/../layouts/header.php';

// Calculate totals
$totalTransactions = 0;
$totalPayments = 0;
$totalBalance = 0;

foreach ($transactions as $transaction) {
    $totalTransactions += $transaction['total_amount_rmb'];
    $totalPayments += $transaction['payment_rmb'];
    $totalBalance += $transaction['balance_rmb'];
}
?>

<div class="col-md-12 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h1><?= __('clients.client_statement') ?></h1>
        <div>
            <button class="btn btn-danger" onclick="window.print()">
                <i class="bi bi-printer"></i> <?= __('print') ?>
            </button>
            <button class="btn btn-success" onclick="exportToExcel('statement-table', 'statement-<?= $client['client_code'] ?>')">
                <i class="bi bi-file-excel"></i> <?= __('export') ?> Excel
            </button>
            <a href="/clients" class="btn btn-secondary">
                <i class="bi bi-arrow-<?= isRTL() ? 'right' : 'left' ?>"></i> <?= __('back') ?>
            </a>
        </div>
    </div>
    
    <!-- Client Info -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h4><?= lang() == 'ar' ? ($client['name_ar'] ?? $client['name']) : $client['name'] ?></h4>
                    <p class="mb-1"><strong><?= __('clients.client_code') ?>:</strong> <?= $client['client_code'] ?></p>
                    <p class="mb-1"><strong><?= __('clients.phone') ?>:</strong> <?= $client['phone'] ?></p>
                    <?php if ($client['email']): ?>
                    <p class="mb-1"><strong><?= __('clients.email') ?>:</strong> <?= $client['email'] ?></p>
                    <?php endif; ?>
                </div>
                <div class="col-md-6 text-end">
                    <p class="mb-1"><strong><?= __('reports.date_range') ?>:</strong></p>
                    <form method="GET" action="/clients/statement/<?= $client['id'] ?>" class="d-inline-flex gap-2">
                        <input type="date" name="start_date" class="form-control form-control-sm" value="<?= $startDate ?>" onchange="this.form.submit()">
                        <span>-</span>
                        <input type="date" name="end_date" class="form-control form-control-sm" value="<?= $endDate ?>" onchange="this.form.submit()">
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6><?= __('transactions.total_amount') ?></h6>
                    <h4>¥<?= number_format($totalTransactions, 2) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6><?= __('transactions.payment') ?></h6>
                    <h4>¥<?= number_format($totalPayments, 2) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6><?= __('balance') ?></h6>
                    <h4>¥<?= number_format($totalBalance, 2) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6><?= __('clients.transaction_count') ?></h6>
                    <h4><?= count($transactions) ?></h4>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Transactions Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><?= __('transactions.title') ?></h5>
        </div>
        <div class="card-body">
            <?php if (empty($transactions)): ?>
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle"></i> <?= __('messages.no_data_found') ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped" id="statement-table">
                        <thead>
                            <tr>
                                <th><?= __('date') ?></th>
                                <th><?= __('transactions.transaction_no') ?></th>
                                <th><?= __('transactions.type') ?></th>
                                <th><?= __('transactions.description') ?></th>
                                <th><?= __('transactions.invoice_no') ?></th>
                                <th><?= __('transactions.goods_amount') ?></th>
                                <th><?= __('transactions.commission') ?></th>
                                <th><?= __('total') ?></th>
                                <th><?= __('transactions.payment') ?></th>
                                <th><?= __('balance') ?></th>
                                <th><?= __('status') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $runningBalance = 0;
                            foreach ($transactions as $transaction): 
                                $runningBalance += $transaction['balance_rmb'];
                            ?>
                            <tr>
                                <td><?= date('Y-m-d', strtotime($transaction['transaction_date'])) ?></td>
                                <td>
                                    <a href="/transactions/view/<?= $transaction['id'] ?>">
                                        <?= $transaction['transaction_no'] ?>
                                    </a>
                                </td>
                                <td><?= $transaction['transaction_type_name'] ?></td>
                                <td><?= $transaction['description'] ?? '-' ?></td>
                                <td><?= $transaction['invoice_no'] ?? '-' ?></td>
                                <td class="text-end">¥<?= number_format($transaction['goods_amount_rmb'], 2) ?></td>
                                <td class="text-end">¥<?= number_format($transaction['commission_rmb'], 2) ?></td>
                                <td class="text-end">¥<?= number_format($transaction['total_amount_rmb'], 2) ?></td>
                                <td class="text-end text-success">¥<?= number_format($transaction['payment_rmb'], 2) ?></td>
                                <td class="text-end <?= $transaction['balance_rmb'] > 0 ? 'text-danger' : 'text-success' ?>">
                                    ¥<?= number_format($transaction['balance_rmb'], 2) ?>
                                </td>
                                <td>
                                    <?php if ($transaction['status'] == 'approved'): ?>
                                        <span class="badge bg-success"><?= __('transactions.approved') ?></span>
                                    <?php elseif ($transaction['status'] == 'pending'): ?>
                                        <span class="badge bg-warning"><?= __('transactions.pending') ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><?= __('transactions.cancelled') ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-secondary">
                                <th colspan="5"><?= __('total') ?></th>
                                <th class="text-end">¥<?= number_format(array_sum(array_column($transactions, 'goods_amount_rmb')), 2) ?></th>
                                <th class="text-end">¥<?= number_format(array_sum(array_column($transactions, 'commission_rmb')), 2) ?></th>
                                <th class="text-end">¥<?= number_format($totalTransactions, 2) ?></th>
                                <th class="text-end">¥<?= number_format($totalPayments, 2) ?></th>
                                <th class="text-end <?= $totalBalance > 0 ? 'text-danger' : 'text-success' ?>">
                                    ¥<?= number_format($totalBalance, 2) ?>
                                </th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <!-- USD Section if any -->
                <?php 
                $hasUsd = false;
                foreach ($transactions as $t) {
                    if ($t['shipping_usd'] > 0 || $t['payment_usd'] > 0) {
                        $hasUsd = true;
                        break;
                    }
                }
                
                if ($hasUsd): 
                ?>
                <h6 class="mt-4"><?= __('transactions.shipping') ?> (USD)</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th><?= __('date') ?></th>
                                <th><?= __('transactions.transaction_no') ?></th>
                                <th><?= __('transactions.shipping') ?></th>
                                <th><?= __('transactions.payment') ?></th>
                                <th><?= __('balance') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $transaction): 
                                if ($transaction['shipping_usd'] > 0 || $transaction['payment_usd'] > 0):
                            ?>
                            <tr>
                                <td><?= date('Y-m-d', strtotime($transaction['transaction_date'])) ?></td>
                                <td><?= $transaction['transaction_no'] ?></td>
                                <td class="text-end">$<?= number_format($transaction['shipping_usd'], 2) ?></td>
                                <td class="text-end text-success">$<?= number_format($transaction['payment_usd'], 2) ?></td>
                                <td class="text-end">$<?= number_format($transaction['balance_usd'], 2) ?></td>
                            </tr>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-secondary">
                                <th colspan="2"><?= __('total') ?></th>
                                <th class="text-end">$<?= number_format(array_sum(array_column($transactions, 'shipping_usd')), 2) ?></th>
                                <th class="text-end">$<?= number_format(array_sum(array_column($transactions, 'payment_usd')), 2) ?></th>
                                <th class="text-end">$<?= number_format(array_sum(array_column($transactions, 'balance_usd')), 2) ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Print Header -->
    <div class="d-none d-print-block text-center mb-4">
        <img src="/assets/images/logo.png" alt="Logo" style="max-height: 80px;" class="mb-3">
        <h2><?= __('company_name') ?></h2>
        <h4><?= __('clients.client_statement') ?></h4>
        <p><?= __('date') ?>: <?= date('Y-m-d') ?></p>
    </div>
</div>

<style>
@media print {
    .no-print {
        display: none !important;
    }
    
    .card {
        border: 1px solid #000 !important;
        page-break-inside: avoid;
    }
    
    body {
        font-size: 12px;
    }
    
    .table {
        font-size: 11px;
    }
}
</style>

<?php include __DIR__ . '/../layouts/footer.php'; ?>