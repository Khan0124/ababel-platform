        </div> <!-- End page-content -->
    </main> <!-- End main-content -->
</div> <!-- End body wrapper -->

<!-- Footer -->
<footer class="footer">
    <div class="footer-content">
        <div class="footer-section">
            <div class="footer-brand">
                <img src="/assets/images/logo.png" alt="China Ababel" class="footer-logo">
                <h4 class="footer-title">China Ababel</h4>
                <p class="footer-description"><?= __('Professional accounting and financial management system') ?></p>
            </div>
        </div>
        
        <div class="footer-section">
            <h5 class="footer-heading"><?= __('Quick Links') ?></h5>
            <ul class="footer-links">
                <li><a href="/dashboard"><?= __('Dashboard') ?></a></li>
                <li><a href="/clients"><?= __('Clients') ?></a></li>
                <li><a href="/transactions"><?= __('Transactions') ?></a></li>
                <li><a href="/reports"><?= __('Reports') ?></a></li>
            </ul>
        </div>
        
        <div class="footer-section">
            <h5 class="footer-heading"><?= __('Support') ?></h5>
            <ul class="footer-links">
                <li><a href="/help"><?= __('Help Center') ?></a></li>
                <li><a href="/contact"><?= __('Contact Us') ?></a></li>
                <li><a href="/privacy"><?= __('Privacy Policy') ?></a></li>
                <li><a href="/terms"><?= __('Terms of Service') ?></a></li>
            </ul>
        </div>
        
        <div class="footer-section">
            <h5 class="footer-heading"><?= __('System Info') ?></h5>
            <div class="system-info">
                <div class="info-item">
                    <span class="info-label"><?= __('Version') ?>:</span>
                    <span class="info-value">2.0.0</span>
                </div>
                <div class="info-item">
                    <span class="info-label"><?= __('Last Updated') ?>:</span>
                    <span class="info-value"><?= date('M j, Y') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label"><?= __('Server Time') ?>:</span>
                    <span class="info-value" id="serverTime"><?= date('H:i:s') ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="footer-bottom">
        <div class="footer-bottom-content">
            <p class="copyright">
                © <?= date('Y') ?> China Ababel. <?= __('All rights reserved.') ?>
            </p>
            <div class="footer-actions">
                <button class="btn btn-sm btn-outline" onclick="scrollToTop()">
                    <span>⬆️</span>
                    <?= __('Back to Top') ?>
                </button>
            </div>
        </div>
    </div>
</footer>

<style>
.footer {
    background-color: var(--gray-900);
    color: var(--gray-300);
    margin-top: 4rem;
    border-top: 1px solid var(--gray-800);
}

.footer-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 3rem 1.5rem 2rem;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
}

.footer-section {
    display: flex;
    flex-direction: column;
}

.footer-brand {
    margin-bottom: 1rem;
}

.footer-logo {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-bottom: 0.75rem;
}

.footer-title {
    color: var(--white);
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0 0 0.5rem 0;
}

.footer-description {
    color: var(--gray-400);
    font-size: 0.875rem;
    line-height: 1.5;
    margin: 0;
}

.footer-heading {
    color: var(--white);
    font-size: 1rem;
    font-weight: 600;
    margin: 0 0 1rem 0;
}

.footer-links {
    list-style: none;
    margin: 0;
    padding: 0;
}

.footer-links li {
    margin-bottom: 0.5rem;
}

.footer-links a {
    color: var(--gray-400);
    text-decoration: none;
    font-size: 0.875rem;
    transition: color 0.15s ease;
}

.footer-links a:hover {
    color: var(--white);
}

.system-info {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.875rem;
}

.info-label {
    color: var(--gray-400);
}

.info-value {
    color: var(--gray-300);
    font-weight: 500;
}

.footer-bottom {
    border-top: 1px solid var(--gray-800);
    padding: 1.5rem;
}

.footer-bottom-content {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.copyright {
    color: var(--gray-400);
    font-size: 0.875rem;
    margin: 0;
}

.footer-actions {
    display: flex;
    gap: 1rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .footer-content {
        grid-template-columns: 1fr;
        gap: 2rem;
        padding: 2rem 1rem 1rem;
    }
    
    .footer-bottom-content {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
}

/* RTL Support */
[dir="rtl"] .info-item {
    flex-direction: row-reverse;
}

[dir="rtl"] .footer-bottom-content {
    flex-direction: row-reverse;
}
</style>

<script>
// Update server time
function updateServerTime() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('en-US', {
        hour12: false,
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
    document.getElementById('serverTime').textContent = timeString;
}

// Update time every second
setInterval(updateServerTime, 1000);

// Scroll to top functionality
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Show/hide scroll to top button based on scroll position
window.addEventListener('scroll', function() {
    const scrollToTopBtn = document.querySelector('.footer-actions .btn');
    if (window.scrollY > 300) {
        scrollToTopBtn.style.opacity = '1';
        scrollToTopBtn.style.visibility = 'visible';
    } else {
        scrollToTopBtn.style.opacity = '0';
        scrollToTopBtn.style.visibility = 'hidden';
    }
});

// Initialize scroll to top button state
document.addEventListener('DOMContentLoaded', function() {
    const scrollToTopBtn = document.querySelector('.footer-actions .btn');
    scrollToTopBtn.style.opacity = '0';
    scrollToTopBtn.style.visibility = 'hidden';
    scrollToTopBtn.style.transition = 'opacity 0.3s ease, visibility 0.3s ease';
});
</script>

</body>
</html>