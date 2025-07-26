<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= env('APP_NAME', 'Laboratory Management System') ?> - نظام إدارة المعامل</title>
    <meta name="description" content="نظام إدارة المعامل الطبية المتطور - إدارة شاملة للفحوصات والمرضى والتقارير">
    <meta name="keywords" content="نظام معامل, إدارة مختبرات, تحاليل طبية, laboratory management, medical lab">
    <meta name="author" content="Laboratory Management System">
    <meta property="og:title" content="<?= env('APP_NAME', 'Laboratory Management System') ?> - نظام إدارة المعامل">
    <meta property="og:description" content="نظام إدارة المعامل الطبية المتطور - إدارة شاملة للفحوصات والمرضى والتقارير">
    <meta property="og:type" content="website">
    <link rel="icon" type="image/png" href="/assets/favicon.png">
    <link rel="apple-touch-icon" href="/assets/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0066CC;
            --secondary-color: #00B4D8;
            --accent-color: #90E0EF;
            --dark-color: #03045E;
            --light-bg: #F0F4F8;
            --white: #FFFFFF;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-bg);
            overflow-x: hidden;
            scroll-behavior: smooth;
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--dark-color) 100%);
            min-height: 100vh;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .animated-bg {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            opacity: 0.1;
            background-image: 
                radial-gradient(circle at 20% 80%, var(--accent-color) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, var(--secondary-color) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, var(--accent-color) 0%, transparent 50%);
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(30px, -30px) rotate(120deg); }
            66% { transform: translate(-20px, 20px) rotate(240deg); }
        }

        .hero-content {
            position: relative;
            z-index: 10;
            text-align: center;
            color: var(--white);
            padding: 2rem;
            max-width: 1200px;
            width: 100%;
        }

        .hero-title {
            font-size: 4rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            animation: fadeInUp 1s ease-out;
            background: linear-gradient(45deg, var(--white) 30%, var(--accent-color) 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }

        .hero-subtitle {
            font-size: 1.8rem;
            margin-bottom: 2rem;
            opacity: 0.9;
            animation: fadeInUp 1s ease-out 0.2s;
            animation-fill-mode: both;
        }

        .hero-description {
            font-size: 1.2rem;
            margin-bottom: 3rem;
            line-height: 1.8;
            opacity: 0.85;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
            animation: fadeInUp 1s ease-out 0.4s;
            animation-fill-mode: both;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Login Buttons */
        .login-buttons {
            display: flex;
            gap: 2rem;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeInUp 1s ease-out 0.6s;
            animation-fill-mode: both;
        }

        .login-btn {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: rgba(255, 255, 255, 0.15);
            color: var(--white);
            text-decoration: none;
            padding: 18px 40px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            border: 2px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            will-change: transform;
        }

        .login-btn:before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .login-btn:hover:before {
            width: 300px;
            height: 300px;
        }

        .login-btn:hover {
            color: var(--white);
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
        }

        .login-btn.admin {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
        }

        .login-btn.employee {
            background: linear-gradient(45deg, var(--secondary-color), var(--accent-color));
        }

        /* Animated Lab Equipment */
        .lab-equipment {
            position: absolute;
            z-index: 5;
        }

        .microscope {
            width: 200px;
            height: 200px;
            bottom: 10%;
            right: 5%;
            animation: float-microscope 15s ease-in-out infinite;
        }

        .test-tubes {
            width: 150px;
            height: 150px;
            top: 15%;
            left: 5%;
            animation: float-tubes 18s ease-in-out infinite;
        }

        .dna-helix {
            width: 120px;
            height: 180px;
            top: 20%;
            right: 10%;
            animation: rotate-dna 20s linear infinite;
        }

        @keyframes float-microscope {
            0%, 100% { transform: translateY(0) rotate(-5deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }

        @keyframes float-tubes {
            0%, 100% { transform: translateY(0) translateX(0) rotate(10deg); }
            25% { transform: translateY(-15px) translateX(10px) rotate(5deg); }
            75% { transform: translateY(10px) translateX(-10px) rotate(15deg); }
        }

        @keyframes rotate-dna {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Features Section */
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 4rem;
            animation: fadeInUp 1s ease-out 0.8s;
            animation-fill-mode: both;
        }

        .feature {
            background: rgba(255, 255, 255, 0.1);
            padding: 2.5rem;
            border-radius: 20px;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .feature:before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transform: rotate(45deg);
            transition: all 0.5s;
            opacity: 0;
        }

        .feature:hover:before {
            animation: shine 0.5s ease-in-out;
        }

        @keyframes shine {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }

        .feature:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            background: rgba(255, 255, 255, 0.15);
        }

        .feature-icon {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            display: inline-block;
            animation: pulse 2s ease-in-out infinite;
        }

        .feature:nth-child(1) .feature-icon { animation-delay: 0s; }
        .feature:nth-child(2) .feature-icon { animation-delay: 0.2s; }
        .feature:nth-child(3) .feature-icon { animation-delay: 0.4s; }
        .feature:nth-child(4) .feature-icon { animation-delay: 0.6s; }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .feature-title {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .feature-description {
            opacity: 0.9;
            line-height: 1.8;
            font-size: 1rem;
        }

        /* Loading animation */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--dark-color);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.5s ease-out;
        }
        
        .loading-overlay.hide {
            opacity: 0;
            pointer-events: none;
        }
        
        .loader {
            width: 50px;
            height: 50px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top-color: var(--accent-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.3rem;
            }

            .hero-description {
                font-size: 1rem;
            }
            
            .login-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .login-btn {
                width: 100%;
                max-width: 300px;
            }

            .lab-equipment {
                display: none;
            }
        }

        /* SVG Styles */
        .svg-icon {
            fill: currentColor;
            opacity: 0.15;
        }
    </style>
</head>
<body>
    <!-- Loading overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loader"></div>
    </div>
    
    <div class="hero-section">
        <div class="animated-bg"></div>
        
        <!-- Animated Lab Equipment SVGs -->
        <svg class="lab-equipment microscope" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
            <g class="svg-icon">
                <rect x="70" y="170" width="60" height="20" rx="3"/>
                <rect x="90" y="140" width="20" height="35" rx="2"/>
                <circle cx="100" cy="120" r="25"/>
                <rect x="85" y="50" width="30" height="70" rx="5"/>
                <circle cx="100" cy="40" r="15"/>
                <rect x="95" y="20" width="10" height="25"/>
                <circle cx="100" cy="15" r="8"/>
            </g>
        </svg>

        <svg class="lab-equipment test-tubes" viewBox="0 0 150 150" xmlns="http://www.w3.org/2000/svg">
            <g class="svg-icon">
                <rect x="20" y="40" width="20" height="80" rx="10" fill="currentColor"/>
                <rect x="20" y="40" width="20" height="40" rx="0" opacity="0.5"/>
                <rect x="55" y="30" width="20" height="90" rx="10" fill="currentColor"/>
                <rect x="55" y="30" width="20" height="50" rx="0" opacity="0.5"/>
                <rect x="90" y="35" width="20" height="85" rx="10" fill="currentColor"/>
                <rect x="90" y="35" width="20" height="45" rx="0" opacity="0.5"/>
                <rect x="10" y="120" width="120" height="10" rx="2"/>
            </g>
        </svg>

        <svg class="lab-equipment dna-helix" viewBox="0 0 120 180" xmlns="http://www.w3.org/2000/svg">
            <g class="svg-icon">
                <path d="M30 10 Q60 30 90 10 T90 50 Q60 70 30 50 T30 90 Q60 110 90 90 T90 130 Q60 150 30 130 T30 170" 
                      fill="none" stroke="currentColor" stroke-width="8" opacity="0.3"/>
                <path d="M90 10 Q60 30 30 10 T30 50 Q60 70 90 50 T90 90 Q60 110 30 90 T30 130 Q60 150 90 130 T90 170" 
                      fill="none" stroke="currentColor" stroke-width="8" opacity="0.3"/>
                <circle cx="30" cy="10" r="6" fill="currentColor"/>
                <circle cx="90" cy="10" r="6" fill="currentColor"/>
                <circle cx="30" cy="50" r="6" fill="currentColor"/>
                <circle cx="90" cy="50" r="6" fill="currentColor"/>
                <circle cx="30" cy="90" r="6" fill="currentColor"/>
                <circle cx="90" cy="90" r="6" fill="currentColor"/>
                <circle cx="30" cy="130" r="6" fill="currentColor"/>
                <circle cx="90" cy="130" r="6" fill="currentColor"/>
                <circle cx="30" cy="170" r="6" fill="currentColor"/>
                <circle cx="90" cy="170" r="6" fill="currentColor"/>
            </g>
        </svg>

        <div class="hero-content">
            <h1 class="hero-title">
                <i class="fas fa-flask"></i> <?= env('APP_NAME', 'Laboratory Management System') ?>
            </h1>
            <p class="hero-subtitle">نظام إدارة المعامل الطبية المتطور</p>
            <p class="hero-description">
                منصة شاملة لإدارة المعامل الطبية تتيح تتبع الفحوصات، إدارة المرضى، 
                متابعة المخزون، وإنتاج التقارير بطريقة احترافية ومتطورة. 
                نظام متكامل يدعم جميع احتياجات المعامل الحديثة.
            </p>
            
            <div class="login-buttons">
                <a href="/admin/login" class="login-btn admin">
                    <i class="fas fa-user-shield fa-lg"></i>
                    <span>تسجيل دخول المشرف</span>
                </a>
                <a href="/lab/login" class="login-btn employee">
                    <i class="fas fa-user-md fa-lg"></i>
                    <span>تسجيل دخول الموظف</span>
                </a>
            </div>
            
            <div class="features">
                <div class="feature">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="feature-title">إدارة المرضى</h3>
                    <p class="feature-description">
                        نظام شامل لإدارة بيانات المرضى وتتبع تاريخهم الطبي بكفاءة عالية
                    </p>
                </div>
                
                <div class="feature">
                    <div class="feature-icon">
                        <i class="fas fa-vial"></i>
                    </div>
                    <h3 class="feature-title">إدارة الفحوصات</h3>
                    <p class="feature-description">
                        تسجيل وتتبع جميع الفحوصات الطبية وإدارة النتائج بدقة متناهية
                    </p>
                </div>
                
                <div class="feature">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="feature-title">التقارير والإحصائيات</h3>
                    <p class="feature-description">
                        تقارير مفصلة وإحصائيات دقيقة لمتابعة الأداء واتخاذ القرارات
                    </p>
                </div>
                
                <div class="feature">
                    <div class="feature-icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <h3 class="feature-title">إدارة المخزون</h3>
                    <p class="feature-description">
                        تتبع المواد والأدوات الطبية وإدارة المخزون بكفاءة عالية
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Hide loading overlay when page is loaded
        window.addEventListener('load', function() {
            const loader = document.getElementById('loadingOverlay');
            setTimeout(() => {
                loader.classList.add('hide');
            }, 500);
        });
        
        // Add smooth hover effects to buttons
        document.querySelectorAll('.login-btn').forEach(btn => {
            btn.addEventListener('mouseenter', function(e) {
                const rect = this.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                const ripple = document.createElement('span');
                ripple.style.position = 'absolute';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                ripple.style.transform = 'translate(-50%, -50%)';
                
                this.appendChild(ripple);
                
                setTimeout(() => ripple.remove(), 600);
            });
        });
        
        // Intersection Observer for feature animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        };
        
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);
        
        document.querySelectorAll('.feature').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'all 0.6s ease-out';
            observer.observe(el);
        });
    </script>
</body>
</html>