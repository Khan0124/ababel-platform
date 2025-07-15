
<?php
if (session_status() == PHP_SESSION_NONE) session_start();

// roles المسموح بها للصفحة
function allow_roles($allowed_roles = []) {
  if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
    echo "<!DOCTYPE html> <html lang='ar' dir='rtl'> <head>   <meta charset='UTF-8'>   <title>صلاحيات مفقودة</title>   <style>     body { font-family: 'Cairo', sans-serif; background: #f8f8f8; text-align: center; padding: 60px; }     .box { background: white; display: inline-block; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px #ccc; }     h2 { color: #a00; }     a { display: inline-block; margin-top: 20px; text-decoration: none; background: #711739; color: white; padding: 10px 20px; border-radius: 6px; }   </style> </head> <body>   <div class='box'>     <h2>🚫 ليس لديك صلاحية للوصول إلى هذه الصفحة</h2>     <p>يرجى الرجوع إلى الإدارة أو استخدام الصفحة المناسبة لصلاحياتك.</p>     <a href='dashboard.php'>⬅️ الرجوع إلى الصفحة الرئيسية</a>   </div> </body> </html>"; exit;
  }
}
?>
