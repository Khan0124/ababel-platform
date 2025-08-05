<?php
$pageTitle = __('Dashboard');
$currentPage = 'dashboard';
include __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard">
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon stat-primary">💰</div>
            <div class="stat-content">
                <h3 class="stat-value"><?= number_format($totalRevenue ?? 0, 2) ?></h3>
                <p class="stat-label"><?= __('Total Revenue') ?></p>
                <div class="stat-change stat-positive">
                    <span>+<?= $revenueChange ?? 0 ?>%</span>
                    <span class="change-period"><?= __('from last month') ?></span>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon stat-success">👥</div>
            <div class="stat-content">
                <h3 class="stat-value"><?= $totalClients ?? 0 ?></h3>
                <p class="stat-label"><?= __('Active Clients') ?></p>
                <div class="stat-change stat-positive">
                    <span>+<?= $clientsChange ?? 0 ?></span>
                    <span class="change-period"><?= __('new this month') ?></span>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon stat-warning">📦</div>
            <div class="stat-content">
                <h3 class="stat-value"><?= $totalLoadings ?? 0 ?></h3>
                <p class="stat-label"><?= __('Total Loadings') ?></p>
                <div class="stat-change stat-positive">
                    <span>+<?= $loadingsChange ?? 0 ?></span>
                    <span class="change-period"><?= __('this month') ?></span>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon stat-info">🏦</div>
            <div class="stat-content">
                <h3 class="stat-value"><?= number_format($cashboxBalance ?? 0, 2) ?></h3>
                <p class="stat-label"><?= __('Cashbox Balance') ?></p>
                <div class="stat-change stat-neutral">
                    <span><?= $balanceChange ?? 0 ?>%</span>
                    <span class="change-period"><?= __('change') ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts Section -->
    <div class="charts-section">
        <div class="chart-card">
            <div class="chart-header">
                <h3><?= __('Revenue Trend') ?></h3>
                <div class="chart-actions">
                    <button class="btn btn-sm btn-outline" onclick="updateChart('revenue', 'week')"><?= __('Week') ?></button>
                    <button class="btn btn-sm btn-outline active" onclick="updateChart('revenue', 'month')"><?= __('Month') ?></button>
                    <button class="btn btn-sm btn-outline" onclick="updateChart('revenue', 'year')"><?= __('Year') ?></button>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="revenueChart" width="400" height="200"></canvas>
            </div>
        </div>
        
        <div class="chart-card">
            <div class="chart-header">
                <h3><?= __('Top Clients') ?></h3>
                <a href="/clients" class="btn btn-sm btn-primary"><?= __('View All') ?></a>
            </div>
            <div class="top-clients">
                <?php if (!empty($topClients)): ?>
                    <?php foreach ($topClients as $index => $client): ?>
                        <div class="client-item">
                            <div class="client-rank">#<?= $index + 1 ?></div>
                            <div class="client-info">
                                <div class="client-name"><?= htmlspecialchars($client['name']) ?></div>
                                <div class="client-balance"><?= number_format($client['balance'], 2) ?></div>
                            </div>
                            <div class="client-actions">
                                <a href="/clients/<?= $client['id'] ?>" class="btn btn-sm btn-outline"><?= __('View') ?></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <span class="empty-icon">👥</span>
                        <p><?= __('No clients found') ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity -->
    <div class="activity-section">
        <div class="activity-card">
            <div class="activity-header">
                <h3><?= __('Recent Transactions') ?></h3>
                <a href="/transactions" class="btn btn-sm btn-primary"><?= __('View All') ?></a>
            </div>
            <div class="activity-list">
                <?php if (!empty($recentTransactions)): ?>
                    <?php foreach ($recentTransactions as $transaction): ?>
                        <div class="activity-item">
                            <div class="activity-icon <?= $transaction['type'] === 'income' ? 'income' : 'expense' ?>">
                                <?= $transaction['type'] === 'income' ? '💰' : '💸' ?>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title"><?= htmlspecialchars($transaction['description']) ?></div>
                                <div class="activity-meta">
                                    <span class="activity-client"><?= htmlspecialchars($transaction['client_name']) ?></span>
                                    <span class="activity-date"><?= date('M j, Y', strtotime($transaction['created_at'])) ?></span>
                                </div>
                            </div>
                            <div class="activity-amount <?= $transaction['type'] === 'income' ? 'positive' : 'negative' ?>">
                                <?= $transaction['type'] === 'income' ? '+' : '-' ?><?= number_format($transaction['amount'], 2) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <span class="empty-icon">📊</span>
                        <p><?= __('No recent transactions') ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="activity-card">
            <div class="activity-header">
                <h3><?= __('Recent Loadings') ?></h3>
                <a href="/loadings" class="btn btn-sm btn-primary"><?= __('View All') ?></a>
            </div>
            <div class="activity-list">
                <?php if (!empty($recentLoadings)): ?>
                    <?php foreach ($recentLoadings as $loading): ?>
                        <div class="activity-item">
                            <div class="activity-icon loading">
                                📦
                            </div>
                            <div class="activity-content">
                                <div class="activity-title"><?= htmlspecialchars($loading['description']) ?></div>
                                <div class="activity-meta">
                                    <span class="activity-client"><?= htmlspecialchars($loading['client_name']) ?></span>
                                    <span class="activity-date"><?= date('M j, Y', strtotime($loading['created_at'])) ?></span>
                                </div>
                            </div>
                            <div class="activity-status">
                                <span class="status-badge status-<?= $loading['status'] ?>">
                                    <?= ucfirst($loading['status']) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <span class="empty-icon">📦</span>
                        <p><?= __('No recent loadings') ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard {
    max-width: 1200px;
    margin: 0 auto;
}

/* Statistics Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: var(--white);
    border-radius: 0.75rem;
    padding: 1.5rem;
    box-shadow: var(--shadow-sm);
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.2s ease;
}

.stat-card:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.stat-primary {
    background-color: rgb(37 99 235 / 0.1);
    color: var(--primary-color);
}

.stat-success {
    background-color: rgb(16 185 129 / 0.1);
    color: var(--success-color);
}

.stat-warning {
    background-color: rgb(245 158 11 / 0.1);
    color: var(--warning-color);
}

.stat-info {
    background-color: rgb(6 182 212 / 0.1);
    color: var(--info-color);
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 1.875rem;
    font-weight: 700;
    color: var(--gray-900);
    margin: 0 0 0.25rem 0;
}

.stat-label {
    color: var(--gray-600);
    font-size: 0.875rem;
    margin: 0 0 0.5rem 0;
}

.stat-change {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem;
    font-weight: 500;
}

.stat-positive {
    color: var(--success-color);
}

.stat-negative {
    color: var(--danger-color);
}

.stat-neutral {
    color: var(--gray-500);
}

.change-period {
    color: var(--gray-500);
    font-weight: 400;
}

/* Charts Section */
.charts-section {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.chart-card {
    background: var(--white);
    border-radius: 0.75rem;
    padding: 1.5rem;
    box-shadow: var(--shadow-sm);
}

.chart-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
}

.chart-header h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--gray-900);
}

.chart-actions {
    display: flex;
    gap: 0.5rem;
}

.chart-container {
    height: 300px;
    position: relative;
}

/* Top Clients */
.top-clients {
    max-height: 300px;
    overflow-y: auto;
}

.client-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 0;
    border-bottom: 1px solid var(--gray-200);
}

.client-item:last-child {
    border-bottom: none;
}

.client-rank {
    width: 32px;
    height: 32px;
    background-color: var(--gray-100);
    color: var(--gray-600);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.875rem;
}

.client-info {
    flex: 1;
}

.client-name {
    font-weight: 500;
    color: var(--gray-900);
    margin-bottom: 0.25rem;
}

.client-balance {
    font-size: 0.875rem;
    color: var(--gray-600);
}

/* Activity Section */
.activity-section {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

.activity-card {
    background: var(--white);
    border-radius: 0.75rem;
    padding: 1.5rem;
    box-shadow: var(--shadow-sm);
}

.activity-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
}

.activity-header h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--gray-900);
}

.activity-list {
    max-height: 400px;
    overflow-y: auto;
}

.activity-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 0;
    border-bottom: 1px solid var(--gray-200);
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.125rem;
}

.activity-icon.income {
    background-color: rgb(16 185 129 / 0.1);
    color: var(--success-color);
}

.activity-icon.expense {
    background-color: rgb(239 68 68 / 0.1);
    color: var(--danger-color);
}

.activity-icon.loading {
    background-color: rgb(245 158 11 / 0.1);
    color: var(--warning-color);
}

.activity-content {
    flex: 1;
}

.activity-title {
    font-weight: 500;
    color: var(--gray-900);
    margin-bottom: 0.25rem;
}

.activity-meta {
    display: flex;
    gap: 1rem;
    font-size: 0.875rem;
    color: var(--gray-600);
}

.activity-amount {
    font-weight: 600;
    font-size: 1rem;
}

.activity-amount.positive {
    color: var(--success-color);
}

.activity-amount.negative {
    color: var(--danger-color);
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: uppercase;
}

.status-pending {
    background-color: rgb(245 158 11 / 0.1);
    color: var(--warning-color);
}

.status-completed {
    background-color: rgb(16 185 129 / 0.1);
    color: var(--success-color);
}

.status-cancelled {
    background-color: rgb(239 68 68 / 0.1);
    color: var(--danger-color);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 2rem;
    color: var(--gray-500);
}

.empty-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
    display: block;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .charts-section {
        grid-template-columns: 1fr;
    }
    
    .activity-section {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .stat-card {
        padding: 1rem;
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        font-size: 1.25rem;
    }
    
    .stat-value {
        font-size: 1.5rem;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Revenue Chart
const ctx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
            label: '<?= __('Revenue') ?>',
            data: [12000, 19000, 15000, 25000, 22000, 30000],
            borderColor: '#2563eb',
            backgroundColor: 'rgba(37, 99, 235, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0, 0, 0, 0.1)'
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});

function updateChart(type, period) {
    // Update chart data based on period
    console.log(`Updating ${type} chart for ${period} period`);
    
    // Update active button
    document.querySelectorAll('.chart-actions .btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
}
</script>