<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'] ?? '';
  $email = $_POST['email'] ?? '';
  $message = $_POST['message'] ?? '';
  if ($name && $email && $message) {
    $to = "info@ababel.net";
    $subject = "رسالة جديدة من موقع أبابيل";
    $headers = "From: $email\r\nReply-To: $email\r\nContent-Type: text/plain; charset=utf-8";
    $body = "الاسم: $name\nالبريد: $email\n\nالرسالة:\n$message";
    @mail($to, $subject, $body, $headers);
    $sent = true;
  }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>شركة أبابيل للتنمية والاستثمار</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body { font-family: 'Cairo', sans-serif; margin: 0; background: #f4f4f4; scroll-behavior: smooth; }
    header {
      height: 100vh;
      background: url('banner.jpg') no-repeat center center/cover;
      position: relative;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      color: white;
      text-align: center;
    }
    header::after {
      content: '';
      position: absolute;
      inset: 0;
      background: rgba(0,0,0,0.6);
    }
    header * { position: relative; z-index: 2; }
    header img { height: 70px; margin-bottom: 15px; }
    header h1 { font-size: 2.2rem; animation: fadeInDown 1s; }
    header p { animation: fadeInUp 1.5s; }
    .btn-primary { background: #711739; border: none; }
    section { padding: 60px 0; }
    .icon-box {
      padding: 20px;
      border-radius: 10px;
      transition: all 0.3s ease;
      background: white;
      box-shadow: 0 0 5px rgba(0,0,0,0.05);
    }
    .icon-box i {
      font-size: 2.5rem;
      color: #711739;
      transition: transform 0.3s;
    }
    .icon-box:hover {
      transform: translateY(-8px);
    }
    .icon-box:hover i {
      transform: scale(1.2) rotate(-5deg);
      color: #a3204a;
    }
    .login-links a {
      margin: 10px;
      display: inline-block;
      padding: 10px 20px;
      color: white;
      background: #711739;
      border-radius: 5px;
      text-decoration: none;
      transition: background 0.3s;
    }
    .login-links a:hover {
      background: #941b47;
    }
    footer {
      background: #711739;
      color: white;
      padding: 20px 0;
      text-align: center;
    }
    @keyframes fadeInDown {
      from { opacity: 0; transform: translateY(-30px); }
      to { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>

<header>
  <img src="logo.png" alt="شعار الشركة">
  <h1>شركة أبابيل للتنمية والاستثمار المحدودة</h1>
  <p>خبراء في التخليص الجمركي والخدمات اللوجستية</p>
  <div class="login-links">
    <a href="app/client_login.php">دخول العملاء</a>
    <a href="app/login.php">دخول الموظفين</a>
  </div>
  <a href="#about" class="btn btn-light mt-3">اعرف أكثر</a>
</header>

<section id="about" class="text-center bg-white">
  <div class="container">
    <h2 class="text-primary">من نحن</h2>
    <p>شركة أبابيل شركة رائدة في التخليص الجمركي، الاستيراد من الصين، التتبع، والخدمات اللوجستية بمهنية وسرعة عالية.</p>
  </div>
</section>

<section id="services" style="background:#f1f1f1;">
  <div class="container text-center">
    <h2 class="text-primary">خدماتنا</h2>
    <div class="row mt-4 g-4">
      <div class="col-md-3 icon-box">
        <i class="bi bi-gear-fill"></i>
        <h5 class="mt-2">الخدمات اللوجستية</h5>
        <p>خدمات النقل والتتبع من الباب إلى الباب.</p>
      </div>
      <div class="col-md-3 icon-box">
        <i class="bi bi-truck"></i>
        <h5 class="mt-2">التجميع والشحن</h5>
        <p>تجميع البضائع في الصين وشحنها للحاويات.</p>
      </div>
      <div class="col-md-3 icon-box">
        <i class="bi bi-bag-check-fill"></i>
        <h5 class="mt-2">الاستيراد من الصين</h5>
        <p>شراء وتخليص وتسليم سريع وآمن.</p>
      </div>
      <div class="col-md-3 icon-box">
        <i class="bi bi-file-earmark-check-fill"></i>
        <h5 class="mt-2">التخليص الجمركي</h5>
        <p>إنهاء كافة الإجراءات الجمركية بدقة.</p>
      </div>
    </div>
  </div>
</section>

<section id="contact" style="background:#f8f8f8;">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-6 text-center">
        <h3 class="text-primary">تواصل معنا</h3>
        <?php if (!empty($sent)): ?>
          <div class="alert alert-success">✅ تم إرسال الرسالة بنجاح!</div>
        <?php endif; ?>
        <p><i class="bi bi-telephone-fill"></i> 0910564187</p>
        <p><i class="bi bi-envelope-fill"></i> info@ababel.net</p>
        <p><i class="bi bi-geo-alt-fill"></i> بورتسودان، السودان</p>
      </div>
      <div class="col-md-6">
        <form method="POST">
          <div class="mb-3">
            <input type="text" name="name" class="form-control" placeholder="اسمك" required>
          </div>
          <div class="mb-3">
            <input type="email" name="email" class="form-control" placeholder="بريدك الإلكتروني" required>
          </div>
          <div class="mb-3">
            <textarea name="message" class="form-control" rows="4" placeholder="رسالتك" required></textarea>
          </div>
          <button type="submit" class="btn btn-primary w-100">إرسال</button>
        </form>
      </div>
    </div>
  </div>
</section>

<footer>
  جميع الحقوق محفوظة &copy; شركة أبابيل 2025
</footer>

</body>
</html>