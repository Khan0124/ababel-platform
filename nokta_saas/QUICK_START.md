# 🚀 دليل البدء السريع - نظام نوكتا POS

## خطوات التشغيل السريع (5 دقائق)

### الخطوة 1: التحقق من المتطلبات
```bash
flutter --version   # Flutter 3.24.0+
node --version      # Node.js 20+
docker --version    # Docker 24+
```

### الخطوة 2: تشغيل النظام (Windows)
```bash
cd C:\flutter_projects\nokta_saas
deploy.bat
```

### الخطوة 3: فتح التطبيقات
```
✅ Admin Panel: http://localhost/admin
   Username: admin
   Password: admin123

✅ Customer App: http://localhost
   
✅ POS App: تشغيل التطبيق من
   apps\pos_app\build\windows\x64\runner\Release\pos_app.exe
```

---

## 🎯 تشغيل كل تطبيق منفرد

### POS Application
```bash
cd apps/pos_app
flutter run -d windows  # Windows
flutter run -d chrome   # Web
flutter run             # Mobile
```

### Customer Application
```bash
cd apps/customer_app
flutter run -d chrome   # Web
flutter run             # Mobile
```

### Driver Application
```bash
cd apps/driver_app
flutter run             # Mobile only
```

### Admin Panel
```bash
cd apps/admin_panel
flutter run -d chrome   # Web only
```

### Backend API
```bash
cd backend
npm start               # Production
npm run dev            # Development
```

---

## 📱 بناء التطبيقات للإنتاج

### Android APK
```bash
cd apps/pos_app
flutter build apk --release

cd apps/customer_app
flutter build apk --release

cd apps/driver_app
flutter build apk --release
```

### iOS (على macOS فقط)
```bash
cd apps/pos_app
flutter build ios --release

cd apps/customer_app
flutter build ios --release

cd apps/driver_app
flutter build ios --release
```

### Windows Desktop
```bash
cd apps/pos_app
flutter build windows --release
```

### Web
```bash
cd apps/customer_app
flutter build web --release

cd apps/admin_panel
flutter build web --release
```

---

## 🐳 Docker Commands

### تشغيل جميع الخدمات
```bash
docker-compose up -d
```

### إيقاف جميع الخدمات
```bash
docker-compose down
```

### عرض السجلات
```bash
docker-compose logs -f
```

### الدخول لقاعدة البيانات
```bash
docker exec -it nokta_mysql mysql -u root -p
# Password: nokta_root_2024
```

---

## 🔧 حل المشاكل الشائعة

### مشكلة 1: Flutter packages not found
```bash
melos bootstrap
```

### مشكلة 2: Backend not starting
```bash
cd backend
npm install
cp .env.example .env
npm start
```

### مشكلة 3: Database connection error
```bash
docker-compose restart mysql
```

### مشكلة 4: Port already in use
```bash
# Windows
netstat -ano | findstr :3001
taskkill /PID <PID> /F

# Linux/Mac
lsof -i :3001
kill -9 <PID>
```

---

## 📋 اختبار النظام

### 1. اختبار تسجيل الدخول
- افتح http://localhost/admin
- أدخل: admin / admin123
- يجب أن تظهر لوحة التحكم

### 2. اختبار إضافة منتج
- اذهب إلى المنتجات
- اضغط إضافة منتج جديد
- املأ البيانات واحفظ

### 3. اختبار إنشاء طلب
- افتح POS App
- اختر منتجات
- أضف للسلة
- اكمل الدفع

### 4. اختبار تتبع الطلب
- افتح Customer App
- اعرض الطلبات
- تتبع حالة الطلب

---

## 🎉 النظام جاهز!

إذا وصلت لهذه النقطة، فالنظام يعمل بنجاح! 🚀

للمساعدة: support@nokta-pos.com
