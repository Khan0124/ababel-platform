<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الصفحة غير موجودة - نظام إدارة المختبرات</title>
    
    <!-- Bootstrap RTL CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Segoe UI', 'Cairo', 'Amiri', Tahoma, Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        
        .error-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .error-icon {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 3rem;
            color: white;
        }
        
        .error-code {
            font-size: 4rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 10px;
        }
        
        .error-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 15px;
        }
        
        .error-message {
            color: #64748b;
            font-size: 1.1rem;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .btn-home {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 12px;
            padding: 15px 30px;
            color: white;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }
        
        .btn-back {
            background: #64748b;
            border: none;
            border-radius: 12px;
            padding: 15px 30px;
            color: white;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            margin-right: 15px;
        }
        
        .btn-back:hover {
            background: #475569;
            color: white;
        }
        
        .error-actions {
            margin-top: 30px;
        }
        
        .search-box {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 15px;
            margin: 20px 0;
        }
        
        .search-input {
            border: none;
            background: transparent;
            width: 100%;
            font-size: 1rem;
            outline: none;
        }
        
        @media (max-width: 576px) {
            .error-container {
                padding: 30px 20px;
            }
            
            .error-code {
                font-size: 3rem;
            }
            
            .error-title {
                font-size: 1.5rem;
            }
            
            .error-actions {
                display: flex;
                flex-direction: column;
                gap: 15px;
            }
            
            .btn-back {
                margin-right: 0;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="bi bi-search"></i>
        </div>
        
        <div class="error-code">404</div>
        <h1 class="error-title">الصفحة غير موجودة</h1>
        <p class="error-message">
            عذراً، الصفحة التي تبحث عنها غير موجودة. 
            قد تكون الرابط خاطئ أو تم نقل الصفحة.
        </p>
        
        <div class="search-box">
            <input type="text" class="search-input" placeholder="البحث في الموقع..." id="searchInput">
        </div>
        
        <div class="error-actions">
            <a href="javascript:history.back()" class="btn btn-back">
                <i class="bi bi-arrow-right me-2"></i>
                العودة للصفحة السابقة
            </a>
            <a href="/" class="btn btn-home">
                <i class="bi bi-house-fill me-2"></i>
                الصفحة الرئيسية
            </a>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const query = this.value.trim();
                if (query) {
                    window.location.href = '/search?q=' + encodeURIComponent(query);
                }
            }
        });
        
        // Auto-focus on search input
        document.getElementById('searchInput').focus();
    </script>
</body>
</html> 