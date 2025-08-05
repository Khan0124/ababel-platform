# نوكتا POS - نظام نقاط البيع الذكي

<div align="center">

![Nokta POS Logo](assets/images/nokta_logo.png)

**نظام نقاط بيع متطور متعدد المنصات مبني بـ Flutter**

[![Flutter Version](https://img.shields.io/badge/Flutter-3.24.0+-blue.svg)](https://flutter.dev/)
[![Dart Version](https://img.shields.io/badge/Dart-3.8.1+-blue.svg)](https://dart.dev/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

[العربية](#) | [English](#) | [Documentation](#)

</div>

## 📋 نظرة عامة

نوكتا POS هو نظام نقاط بيع شامل ومتطور مصمم للمطاعم والمقاهي والمتاجر. يوفر النظام حلولاً متكاملة لإدارة المبيعات، المخزون، الطلبات، والتحليلات.

### 🎯 الميزات الرئيسية

- **💳 نقطة البيع المتقدمة**: واجهة سهلة الاستخدام لمعالجة الطلبات والمدفوعات
- **📱 تطبيق العملاء**: تطبيق جوال للطلب المباشر والتوصيل
- **🚗 تطبيق السائقين**: إدارة التوصيل وتتبع الطلبات
- **👥 لوحة الإدارة**: تحليلات شاملة وإدارة النظام
- **🔒 أمان متقدم**: تشفير البيانات والمصادقة الآمنة
- **☁️ سحابي**: مزامنة البيانات عبر جميع الأجهزة

## 🏗️ بنية المشروع

```
nokta_pos/
├── apps/                     # التطبيقات الرئيسية
│   ├── pos_app/             # تطبيق نقطة البيع
│   ├── customer_app/        # تطبيق العملاء
│   ├── driver_app/          # تطبيق السائقين
│   └── admin_panel/         # لوحة الإدارة
├── packages/
│   └── core/               # الحزمة الأساسية المشتركة
├── melos.yaml              # إعدادات Monorepo
└── pubspec.yaml           # التبعيات الرئيسية
```

## 🛠️ التقنيات المستخدمة

### Frontend
- **Flutter 3.24+**: إطار العمل الرئيسي
- **Riverpod**: إدارة الحالة المتقدمة
- **Go Router**: التنقل والتوجيه
- **Freezed**: توليد الكود للنماذج
- **Dio**: العميل HTTP المتقدم

### Backend Integration
- **Node.js API**: API متقدم مع Express
- **MySQL**: قاعدة البيانات الرئيسية
- **Redis**: التخزين المؤقت
- **JWT**: المصادقة الآمنة

### Security
- **Flutter Secure Storage**: تخزين آمن للبيانات الحساسة
- **تشفير متقدم**: حماية البيانات المهمة
- **مصادقة متعددة العوامل**: أمان إضافي للحسابات

## 🚀 البدء السريع

### المتطلبات الأساسية

- Flutter SDK 3.24.0+
- Dart SDK 3.8.1+
- Android Studio / VS Code
- Git

### التثبيت

1. **استنساخ المشروع**
```bash
git clone https://github.com/your-org/nokta-pos.git
cd nokta-pos
```

2. **تثبيت Melos**
```bash
dart pub global activate melos
```

3. **إعداد المشروع**
```bash
melos bootstrap
```

4. **تشغيل التطبيق**
```bash
# تطبيق نقطة البيع
cd apps/pos_app
flutter run

# تطبيق العملاء
cd apps/customer_app
flutter run
```

## 📦 التطبيقات والحزم

### 🎯 تطبيق نقطة البيع (POS App)
- واجهة نقطة البيع الرئيسية
- إدارة الطلبات والمدفوعات
- طباعة الفواتير
- إدارة المخزون المحلي

### 📱 تطبيق العملاء (Customer App)
- تصفح القوائم والمنتجات
- إنشاء الطلبات
- تتبع الطلبات
- التقييمات والمراجعات

### 🚗 تطبيق السائقين (Driver App)
- استلام طلبات التوصيل
- التنقل والخرائط
- تتبع الإيرادات
- إدارة الحالة

### 👥 لوحة الإدارة (Admin Panel)
- التحليلات والتقارير
- إدارة المستخدمين
- إعدادات النظام
- مراقبة الأداء

### 🧩 الحزمة الأساسية (Core Package)
- النماذج المشتركة
- خدمات API
- أدوات الأمان
- قاعدة البيانات المحلية

## 🔧 التطوير

### تشغيل الاختبارات
```bash
melos test
```

### تحليل الكود
```bash
melos analyze
```

### إنشاء الكود المولد
```bash
melos build:runner
```

### تشغيل جميع التطبيقات
```bash
melos run dev
```

## 🌐 النشر

### Android
```bash
cd apps/pos_app
flutter build apk --release
```

### iOS
```bash
cd apps/pos_app
flutter build ios --release
```

### Web
```bash
cd apps/admin_panel
flutter build web --release
```

## 🔒 الأمان

- **تشفير البيانات**: جميع البيانات الحساسة مشفرة
- **مصادقة JWT**: توكنات آمنة للوصول
- **التحقق من الصحة**: فلترة وتنظيف جميع المدخلات
- **HTTPS**: جميع الاتصالات مشفرة
- **التدقيق**: سجل شامل للأنشطة

## 📊 المراقبة والتحليلات

- **Firebase Analytics**: تتبع الاستخدام
- **Crashlytics**: مراقبة الأخطاء
- **تقارير الأداء**: مراقبة السرعة والاستجابة
- **تحليلات الأعمال**: إحصائيات المبيعات والإيرادات

## 🤝 المساهمة

نرحب بمساهماتكم! يرجى اتباع الخطوات التالية:

1. Fork المشروع
2. إنشاء فرع للميزة (`git checkout -b feature/AmazingFeature`)
3. Commit التغييرات (`git commit -m 'Add AmazingFeature'`)
4. Push للفرع (`git push origin feature/AmazingFeature`)
5. فتح Pull Request

## 📝 الترخيص

هذا المشروع مرخص تحت رخصة MIT - انظر ملف [LICENSE](LICENSE) للتفاصيل.

## 📞 الدعم والتواصل

- **البريد الإلكتروني**: support@nokta-pos.com
- **الموقع الإلكتروني**: https://nokta-pos.com
- **التوثيق**: https://docs.nokta-pos.com
- **المجتمع**: https://community.nokta-pos.com

## 🙏 شكر وتقدير

شكر خاص لجميع المساهمين والمطورين الذين ساهموا في إنجاح هذا المشروع.

---

<div align="center">
Made with ❤️ by the Nokta Team
</div>