<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الصفحة غير موجودة - 404</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
            direction: rtl;
        }
        
        .error-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            padding: 60px 40px;
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        
        .error-code {
            font-size: 120px;
            font-weight: bold;
            color: #667eea;
            margin: 0;
            line-height: 1;
        }
        
        .error-message {
            font-size: 24px;
            color: #333;
            margin: 20px 0;
        }
        
        .error-description {
            font-size: 16px;
            color: #666;
            margin-bottom: 40px;
            line-height: 1.6;
        }
        
        .btn-home {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: #667eea;
            color: white;
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-home:hover {
            background: #5a67d8;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .error-icon {
            font-size: 60px;
            color: #667eea;
            margin-bottom: 20px;
        }
        
        @media (max-width: 480px) {
            .error-container {
                padding: 40px 20px;
            }
            
            .error-code {
                font-size: 80px;
            }
            
            .error-message {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="fas fa-search"></i>
        </div>
        <h1 class="error-code">404</h1>
        <h2 class="error-message">الصفحة غير موجودة</h2>
        <p class="error-description">
            عذراً، الصفحة التي تبحث عنها غير موجودة أو تم نقلها إلى مكان آخر.
            <br>
            يرجى التحقق من الرابط أو العودة إلى الصفحة الرئيسية.
        </p>
        <a href="/" class="btn-home">
            <i class="fas fa-home"></i>
            العودة للرئيسية
        </a>
    </div>
</body>
</html>