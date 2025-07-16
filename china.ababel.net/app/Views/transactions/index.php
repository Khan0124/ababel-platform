<?php
// app/Views/transactions/index.php
include __DIR__ . '/../layouts/header.php';
?>

<div class="col-md-12 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= __('transactions.title') ?></h1>
        <a href="/transactions/create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> <?= __('transactions.add_new') ?>
        </a>
    </div>
    
    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/transactions" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label"><?= __('transactions.client') ?></label>
                    <select name="client_id" class="form-select">
                        <option value=""><?= __('all') ?></option>
                        <?php
                        // Get clients for filter
                        $clientModel = new \App\Models\Client();
                        $clients = $clientModel->all(['status' => 'active'], 'name');
                        foreach ($clients as $client):
                        ?>
                        <option value="<?= $client['id'] ?>" <?= ($_GET['client_id'] ?? '') == $client['id'] ? 'selected' : '' ?>>
                            <?= lang() == 'ar' ? ($client['name_ar'] ?? $client['name']) : $client['name'] ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label"><?= __('status') ?></label>
                    <select name="status" class="form-select">
                        <option value=""><?= __('all') ?></option>
                        <option value="pending" <?= ($_GET['status'] ?? '') == 'pending' ? 'selected' : '' ?>>
                            <?= __('transactions.pending') ?>
                        </option>
                        <option value="approved" <?= ($_GET['status'] ?? '') == 'approved' ? 'selected' : '' ?>>
                            <?= __('transactions.approved') ?>
                        </option>
                        <option value="cancelled" <?= ($_GET['status'] ?? '') == 'cancelled' ? 'selected' : '' ?>>
                            <?= __('transactions.cancelled') ?>
                        </option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label"><?= __('from') ?></label>
                    <input type="date" name="start_date" class="form-control" value="<?= $_GET['start_date'] ?? '' ?>">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label"><?= __('to') ?></label>
                    <input type="date" name="end_date" class="form-control" value="<?= $_GET['end_date'] ?? '' ?>">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> <?= __('search') ?>
                        </button>
                        <a href="/transactions" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Transactions Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><?= __('transactions.title') ?></h5>
            <button class="btn btn-success btn-sm" onclick="exportToExcel('transactions-table', 'transactions')">
                <i class="bi bi-file-excel"></i> <?= __('export') ?> Excel
            </button>
        </div>
        <div class="card-body">
            <?php if (empty($transactions)): ?>
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle"></i> <?= __('messages.no_data_found') ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="transactions-table">
                        <thead>
                            <tr>
                                <th><?= __('transactions.transaction_no') ?></th>
                                <th><?= __('date') ?></th>
                                <th><?= __('transactions.client') ?></th>
                                <th><?= __('transactions.type') ?></th>
                                <th><?= __('transactions.invoice_no') ?></th>
                                <th><?= __('transactions.total_amount') ?> (RMB)</th>
                                <th><?= __('transactions.payment') ?> (RMB)</th>
                                <th><?= __('balance') ?> (RMB)</th>
                                <th><?= __('status') ?></th>
                                <th><?= __('actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $transaction): ?>
                            <tr>
                                <td>
                                    <a href="/transactions/view/<?= $transaction['id'] ?>">
                                        <?= $transaction['transaction_no'] ?>
                                    </a>
                                </td>
                                <td><?= date('Y-m-d', strtotime($transaction['transaction_date'])) ?></td>
                                <td>
                                    <?php if ($transaction['client_id']): ?>
                                        <a href="/clients/statement/<?= $transaction['client_id'] ?>">
                                            <?= htmlspecialchars($transaction['client_name'] ?? '-') ?>
                                        </a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?= $transaction['transaction_type_name'] ?? '-' ?></td>
                                <td><?= $transaction['invoice_no'] ?? '-' ?></td>
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
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="/transactions/view/<?= $transaction['id'] ?>" 
                                           class="btn btn-sm btn-info" 
                                           title="<?= __('view') ?>">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <?php if ($transaction['status'] == 'pending' && ($_SESSION['user_role'] ?? '') != 'viewer'): ?>
                                        <a href="/transactions/approve/<?= $transaction['id'] ?>" 
                                           class="btn btn-sm btn-success" 
                                           title="<?= __('transactions.approve') ?>"
                                           onclick="return confirm('<?= __('messages.are_you_sure') ?>')">
                                            <i class="bi bi-check-circle"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-secondary">
                                <th colspan="5"><?= __('total') ?></th>
                                <th class="text-end">
                                    ¥<?= number_format(array_sum(array_column($transactions, 'total_amount_rmb')), 2) ?>
                                </th>
                                <th class="text-end">
                                    ¥<?= number_format(array_sum(array_column($transactions, 'payment_rmb')), 2) ?>
                                </th>
                                <th class="text-end">
                                    ¥<?= number_format(array_sum(array_column($transactions, 'balance_rmb')), 2) ?>
                                </th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>