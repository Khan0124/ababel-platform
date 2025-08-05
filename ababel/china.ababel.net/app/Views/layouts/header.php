<?php
// app/Views/layouts/header.php
// Load Language class if not already loaded
if (!class_exists('App\Core\Language')) {
    require_once BASE_PATH . '/app/Core/Language.php';
}

// Load helpers if not already loaded
if (!function_exists('__')) {
    $helpersFile = BASE_PATH . '/app/Core/helpers.php';
    if (file_exists($helpersFile)) {
        require_once $helpersFile;
    } else {
        // Define minimal functions if helpers file doesn't exist
        function __($key, $params = []) { return $key; }
        function lang() { return $_SESSION['lang'] ?? 'ar'; }
        function isRTL() { return in_array(lang(), ['ar', 'fa', 'he', 'ur']); }
    }
}

$lang = \App\Core\Language::getInstance();
$isRTL = isRTL(); // Use the global function
$currentLang = lang(); // Use the global function
?>
<!DOCTYPE html>
<html lang="<?= $_SESSION['lang'] ?? 'ar' ?>" dir="<?= in_array($_SESSION['lang'] ?? 'ar', ['ar', 'fa', 'he', 'ur']) ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Dashboard' ?> - China Ababel</title>
    <link rel="stylesheet" href="/assets/css/modern.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="/assets/images/favicon.ico">
    <meta name="description" content="China Ababel Accounting System - Professional financial management">
    <?php if (isset($metaTags)): ?>
        <?= $metaTags ?>
    <?php endif; ?>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-brand">
                <img src="/assets/images/logo.png" alt="China Ababel" class="sidebar-logo">
                <h2 class="sidebar-title">China Ababel</h2>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="/dashboard" class="nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                        <span class="nav-icon">📊</span>
                        <span class="nav-text"><?= __('Dashboard') ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/clients" class="nav-link <?= $currentPage === 'clients' ? 'active' : '' ?>">
                        <span class="nav-icon">👥</span>
                        <span class="nav-text"><?= __('Clients') ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/transactions" class="nav-link <?= $currentPage === 'transactions' ? 'active' : '' ?>">
                        <span class="nav-icon">💰</span>
                        <span class="nav-text"><?= __('Transactions') ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/loadings" class="nav-link <?= $currentPage === 'loadings' ? 'active' : '' ?>">
                        <span class="nav-icon">📦</span>
                        <span class="nav-text"><?= __('Loadings') ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/cashbox" class="nav-link <?= $currentPage === 'cashbox' ? 'active' : '' ?>">
                        <span class="nav-icon">🏦</span>
                        <span class="nav-text"><?= __('Cashbox') ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/reports" class="nav-link <?= $currentPage === 'reports' ? 'active' : '' ?>">
                        <span class="nav-icon">📈</span>
                        <span class="nav-text"><?= __('Reports') ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/settings" class="nav-link <?= $currentPage === 'settings' ? 'active' : '' ?>">
                        <span class="nav-icon">⚙️</span>
                        <span class="nav-text"><?= __('Settings') ?></span>
                    </a>
                </li>
            </ul>
        </nav>
        
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar">
                    <span class="avatar-text"><?= strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)) ?></span>
                </div>
                <div class="user-details">
                    <div class="user-name"><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></div>
                    <div class="user-role"><?= htmlspecialchars($_SESSION['user_role'] ?? 'User') ?></div>
                </div>
            </div>
            <a href="/logout" class="logout-btn">
                <span class="logout-icon">🚪</span>
                <span><?= __('Logout') ?></span>
            </a>
        </div>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Navigation -->
        <header class="top-nav">
            <div class="nav-left">
                <button class="menu-toggle" id="menuToggle">☰</button>
                <h1 class="page-title"><?= $pageTitle ?? 'Dashboard' ?></h1>
            </div>
            
            <div class="nav-right">
                <div class="nav-actions">
                    <a href="/profile" class="user-btn">
                        <span class="user-avatar-small"><?= strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)) ?></span>
                        <span class="user-name"><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></span>
                    </a>
                </div>
            </div>
        </header>
        
        <!-- Page Content -->
        <div class="page-content">
            <?php if (isset($_SESSION['flash_messages'])): ?>
                <?php foreach ($_SESSION['flash_messages'] as $type => $message): ?>
                    <div class="alert alert-<?= $type ?>">
                        <div class="alert-icon">
                            <?= $type === 'success' ? '✅' : ($type === 'error' ? '❌' : 'ℹ️') ?>
                        </div>
                        <div class="alert-content">
                            <?= htmlspecialchars($message) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php unset($_SESSION['flash_messages']); ?>
            <?php endif; ?>

<style>
.sidebar {
    background: var(--white);
    box-shadow: var(--shadow-lg);
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    width: 280px;
    z-index: 1000;
    overflow-y: auto;
}

.sidebar-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--gray-200);
}

.sidebar-brand {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.sidebar-logo {
    width: 40px;
    height: 40px;
    border-radius: 50%;
}

.sidebar-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--gray-900);
    margin: 0;
}

.sidebar-nav {
    padding: 1rem;
}

.nav-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.nav-item {
    margin-bottom: 0.25rem;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    color: var(--gray-600);
    text-decoration: none;
    border-radius: 0.5rem;
    transition: all 0.15s ease;
    font-weight: 500;
}

.nav-link:hover,
.nav-link.active {
    background-color: var(--primary-color);
    color: var(--white);
}

.nav-icon {
    font-size: 1.125rem;
    width: 20px;
    text-align: center;
}

.sidebar-footer {
    padding: 1rem;
    border-top: 1px solid var(--gray-200);
    margin-top: auto;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.user-avatar {
    width: 40px;
    height: 40px;
    background-color: var(--primary-color);
    color: var(--white);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.user-name {
    font-weight: 600;
    color: var(--gray-900);
    font-size: 0.875rem;
}

.user-role {
    color: var(--gray-500);
    font-size: 0.75rem;
}

.logout-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    color: var(--danger-color);
    text-decoration: none;
    border-radius: 0.5rem;
    transition: all 0.15s ease;
    font-weight: 500;
}

.logout-btn:hover {
    background-color: var(--danger-color);
    color: var(--white);
}

.main-content {
    margin-left: 280px;
    min-height: 100vh;
    background-color: var(--gray-50);
}

.top-nav {
    background: var(--white);
    box-shadow: var(--shadow-sm);
    padding: 1rem 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 100;
}

.nav-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.menu-toggle {
    display: none;
    background: none;
    border: none;
    font-size: 1.25rem;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 0.25rem;
    color: var(--gray-600);
}

.page-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--gray-900);
    margin: 0;
}

.nav-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.user-btn {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    background: none;
    border: 1px solid var(--gray-300);
    padding: 0.5rem 0.75rem;
    border-radius: 0.375rem;
    text-decoration: none;
    color: var(--gray-700);
    transition: all 0.15s ease;
}

.user-btn:hover {
    background-color: var(--gray-50);
    border-color: var(--gray-400);
}

.user-avatar-small {
    width: 32px;
    height: 32px;
    background-color: var(--primary-color);
    color: var(--white);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.875rem;
}

.page-content {
    padding: 1.5rem;
}

@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }
    
    .sidebar.show {
        transform: translateX(0);
    }
    
    .main-content {
        margin-left: 0;
    }
    
    .menu-toggle {
        display: block;
    }
}
</style>

<script>
document.getElementById('menuToggle').addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('show');
});

// Auto-hide alerts
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        alert.style.opacity = '0';
        setTimeout(function() {
            alert.style.display = 'none';
        }, 300);
    });
}, 5000);
</script>