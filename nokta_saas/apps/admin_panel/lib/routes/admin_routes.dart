import 'package:flutter/material.dart';
import '../screens/dashboard/admin_dashboard_screen.dart';
import '../screens/analytics/real_time_analytics.dart';

class AdminRoutes {
  static const String splash = '/';
  static const String dashboard = '/dashboard';
  static const String analytics = '/analytics';
  static const String tenants = '/tenants';
  static const String products = '/products';
  static const String users = '/users';
  static const String settings = '/settings';
  static const String reports = '/reports';
  static const String billing = '/billing';
  static const String support = '/support';

  static Map<String, WidgetBuilder> get routes => {
    splash: (context) => const AdminSplashScreen(),
    dashboard: (context) => const AdminDashboardScreen(),
    analytics: (context) => const RealTimeAnalytics(),
    tenants: (context) => const TenantsManagementScreen(),
    products: (context) => const ProductsManagementScreen(),
    users: (context) => const UsersManagementScreen(),
    settings: (context) => const AdminSettingsScreen(),
    reports: (context) => const AdminReportsScreen(),
    billing: (context) => const BillingManagementScreen(),
    support: (context) => const SupportScreen(),
  };

  static Route<dynamic> generateRoute(RouteSettings settings) {
    switch (settings.name) {
      case splash:
        return MaterialPageRoute(builder: (_) => const AdminSplashScreen());
      case dashboard:
        return MaterialPageRoute(builder: (_) => const AdminDashboardScreen());
      case analytics:
        return MaterialPageRoute(builder: (_) => const RealTimeAnalytics());
      case tenants:
        return MaterialPageRoute(
          builder: (_) => const TenantsManagementScreen(),
        );
      case products:
        return MaterialPageRoute(
          builder: (_) => const ProductsManagementScreen(),
        );
      case users:
        return MaterialPageRoute(builder: (_) => const UsersManagementScreen());
      case settings:
        return MaterialPageRoute(builder: (_) => const AdminSettingsScreen());
      case reports:
        return MaterialPageRoute(builder: (_) => const AdminReportsScreen());
      case billing:
        return MaterialPageRoute(
          builder: (_) => const BillingManagementScreen(),
        );
      case support:
        return MaterialPageRoute(builder: (_) => const SupportScreen());
      default:
        return MaterialPageRoute(
          builder: (_) => Scaffold(
            body: Center(child: Text('الصفحة غير موجودة: ${settings.name}')),
          ),
        );
    }
  }
}

class AdminSplashScreen extends StatefulWidget {
  const AdminSplashScreen({super.key});

  @override
  State<AdminSplashScreen> createState() => _AdminSplashScreenState();
}

class _AdminSplashScreenState extends State<AdminSplashScreen> {
  @override
  void initState() {
    super.initState();
    _navigateToMain();
  }

  void _navigateToMain() {
    Future.delayed(const Duration(seconds: 2), () {
      if (mounted) {
        Navigator.of(context).pushReplacementNamed(AdminRoutes.dashboard);
      }
    });
  }

  @override
  Widget build(BuildContext context) => Scaffold(
    backgroundColor: Theme.of(context).primaryColor,
    body: const Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.admin_panel_settings, size: 100, color: Colors.white),
          SizedBox(height: 20),
          Text(
            'نقطة - الإدارة',
            style: TextStyle(
              fontSize: 48,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
          ),
          SizedBox(height: 8),
          Text(
            'لوحة التحكم الإدارية',
            style: TextStyle(fontSize: 18, color: Colors.white70),
          ),
          SizedBox(height: 40),
          CircularProgressIndicator(
            valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
          ),
        ],
      ),
    ),
  );
}

class TenantsManagementScreen extends StatelessWidget {
  const TenantsManagementScreen({super.key});

  @override
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(
      title: const Text('إدارة المتاجر'),
      backgroundColor: Theme.of(context).primaryColor,
      foregroundColor: Colors.white,
      actions: [IconButton(icon: const Icon(Icons.add), onPressed: () {})],
    ),
    body: const Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.business, size: 64, color: Colors.grey),
          SizedBox(height: 16),
          Text(
            'إدارة المتاجر',
            style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
          ),
          SizedBox(height: 8),
          Text(
            'سيتم عرض جميع المتاجر المسجلة هنا',
            style: TextStyle(color: Colors.grey),
          ),
        ],
      ),
    ),
  );
}

class ProductsManagementScreen extends StatelessWidget {
  const ProductsManagementScreen({super.key});

  @override
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(
      title: const Text('إدارة المنتجات'),
      backgroundColor: Theme.of(context).primaryColor,
      foregroundColor: Colors.white,
      actions: [IconButton(icon: const Icon(Icons.add), onPressed: () {})],
    ),
    body: const Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.inventory, size: 64, color: Colors.grey),
          SizedBox(height: 16),
          Text(
            'إدارة المنتجات',
            style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
          ),
          SizedBox(height: 8),
          Text(
            'سيتم عرض جميع المنتجات هنا',
            style: TextStyle(color: Colors.grey),
          ),
        ],
      ),
    ),
  );
}

class UsersManagementScreen extends StatelessWidget {
  const UsersManagementScreen({super.key});

  @override
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(
      title: const Text('إدارة المستخدمين'),
      backgroundColor: Theme.of(context).primaryColor,
      foregroundColor: Colors.white,
      actions: [
        IconButton(icon: const Icon(Icons.person_add), onPressed: () {}),
      ],
    ),
    body: const Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.people, size: 64, color: Colors.grey),
          SizedBox(height: 16),
          Text(
            'إدارة المستخدمين',
            style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
          ),
          SizedBox(height: 8),
          Text(
            'سيتم عرض جميع المستخدمين هنا',
            style: TextStyle(color: Colors.grey),
          ),
        ],
      ),
    ),
  );
}

class AdminSettingsScreen extends StatelessWidget {
  const AdminSettingsScreen({super.key});

  @override
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(
      title: const Text('الإعدادات العامة'),
      backgroundColor: Theme.of(context).primaryColor,
      foregroundColor: Colors.white,
    ),
    body: ListView(
      padding: const EdgeInsets.all(16),
      children: [
        Card(
          child: ListTile(
            leading: const Icon(Icons.security),
            title: const Text('إعدادات الأمان'),
            subtitle: const Text('إدارة صلاحيات النظام والأمان'),
            trailing: const Icon(Icons.arrow_forward_ios),
            onTap: () {},
          ),
        ),
        const SizedBox(height: 8),
        Card(
          child: ListTile(
            leading: const Icon(Icons.notifications),
            title: const Text('إعدادات الإشعارات'),
            subtitle: const Text('تكوين الإشعارات والتنبيهات'),
            trailing: const Icon(Icons.arrow_forward_ios),
            onTap: () {},
          ),
        ),
        const SizedBox(height: 8),
        Card(
          child: ListTile(
            leading: const Icon(Icons.backup),
            title: const Text('النسخ الاحتياطي'),
            subtitle: const Text('إدارة النسخ الاحتياطية للبيانات'),
            trailing: const Icon(Icons.arrow_forward_ios),
            onTap: () {},
          ),
        ),
      ],
    ),
  );
}

class AdminReportsScreen extends StatelessWidget {
  const AdminReportsScreen({super.key});

  @override
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(
      title: const Text('التقارير الإدارية'),
      backgroundColor: Theme.of(context).primaryColor,
      foregroundColor: Colors.white,
    ),
    body: const Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.analytics, size: 64, color: Colors.grey),
          SizedBox(height: 16),
          Text(
            'التقارير الإدارية',
            style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
          ),
          SizedBox(height: 8),
          Text(
            'سيتم عرض التقارير الإدارية هنا',
            style: TextStyle(color: Colors.grey),
          ),
        ],
      ),
    ),
  );
}

class BillingManagementScreen extends StatelessWidget {
  const BillingManagementScreen({super.key});

  @override
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(
      title: const Text('إدارة الفوترة'),
      backgroundColor: Theme.of(context).primaryColor,
      foregroundColor: Colors.white,
    ),
    body: const Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.receipt, size: 64, color: Colors.grey),
          SizedBox(height: 16),
          Text(
            'إدارة الفوترة',
            style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
          ),
          SizedBox(height: 8),
          Text(
            'سيتم عرض فواتير العملاء والاشتراكات هنا',
            style: TextStyle(color: Colors.grey),
          ),
        ],
      ),
    ),
  );
}

class SupportScreen extends StatelessWidget {
  const SupportScreen({super.key});

  @override
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(
      title: const Text('الدعم الفني'),
      backgroundColor: Theme.of(context).primaryColor,
      foregroundColor: Colors.white,
    ),
    body: const Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.support_agent, size: 64, color: Colors.grey),
          SizedBox(height: 16),
          Text(
            'الدعم الفني',
            style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
          ),
          SizedBox(height: 8),
          Text(
            'سيتم عرض طلبات الدعم الفني هنا',
            style: TextStyle(color: Colors.grey),
          ),
        ],
      ),
    ),
  );
}
