import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'screens/dashboard/admin_dashboard_screen.dart';
import 'screens/analytics/real_time_analytics.dart';

void main() {
  runApp(const ProviderScope(child: AdminPanelApp()));
}

class AdminPanelApp extends StatelessWidget {
  const AdminPanelApp({super.key});

  @override
  Widget build(BuildContext context) => MaterialApp(
    title: 'نقطة - لوحة التحكم الإدارية',
    debugShowCheckedModeBanner: false,
    theme: ThemeData(
      primarySwatch: Colors.indigo,
      primaryColor: const Color(0xFF3F51B5),
      colorScheme: ColorScheme.fromSeed(seedColor: const Color(0xFF3F51B5)),
      fontFamily: 'Cairo',
      textTheme: const TextTheme(
        displayLarge: TextStyle(fontSize: 32, fontWeight: FontWeight.bold),
        displayMedium: TextStyle(fontSize: 28, fontWeight: FontWeight.bold),
        displaySmall: TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
        headlineLarge: TextStyle(fontSize: 22, fontWeight: FontWeight.w600),
        headlineMedium: TextStyle(fontSize: 20, fontWeight: FontWeight.w600),
        headlineSmall: TextStyle(fontSize: 18, fontWeight: FontWeight.w600),
        titleLarge: TextStyle(fontSize: 16, fontWeight: FontWeight.w500),
        titleMedium: TextStyle(fontSize: 14, fontWeight: FontWeight.w500),
        titleSmall: TextStyle(fontSize: 12, fontWeight: FontWeight.w500),
        bodyLarge: TextStyle(fontSize: 16),
        bodyMedium: TextStyle(fontSize: 14),
        bodySmall: TextStyle(fontSize: 12),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
        ),
      ),
      cardTheme: CardTheme(
        elevation: 2,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      ),
    ),
    home: const AdminMainScreen(),
  );
}

class AdminMainScreen extends ConsumerStatefulWidget {
  const AdminMainScreen({super.key});

  @override
  ConsumerState<AdminMainScreen> createState() => _AdminMainScreenState();
}

class _AdminMainScreenState extends ConsumerState<AdminMainScreen> {
  int _selectedIndex = 0;

  @override
  Widget build(BuildContext context) {
    final screens = [
      const AdminDashboardScreen(),
      const RealTimeAnalytics(),
      const TenantsManagementScreen(),
      const ProductsManagementScreen(),
      const UsersManagementScreen(),
      const SettingsScreen(),
    ];

    return Scaffold(
      body: Row(
        children: [
          NavigationRail(
            selectedIndex: _selectedIndex,
            onDestinationSelected: (index) =>
                setState(() => _selectedIndex = index),
            labelType: NavigationRailLabelType.all,
            backgroundColor: Theme.of(context).primaryColor.withOpacity(0.1),
            selectedIconTheme: IconThemeData(
              color: Theme.of(context).primaryColor,
            ),
            selectedLabelTextStyle: TextStyle(
              color: Theme.of(context).primaryColor,
            ),
            destinations: const [
              NavigationRailDestination(
                icon: Icon(Icons.dashboard),
                label: Text('لوحة التحكم'),
              ),
              NavigationRailDestination(
                icon: Icon(Icons.analytics),
                label: Text('التحليلات'),
              ),
              NavigationRailDestination(
                icon: Icon(Icons.business),
                label: Text('المتاجر'),
              ),
              NavigationRailDestination(
                icon: Icon(Icons.inventory),
                label: Text('المنتجات'),
              ),
              NavigationRailDestination(
                icon: Icon(Icons.people),
                label: Text('المستخدمين'),
              ),
              NavigationRailDestination(
                icon: Icon(Icons.settings),
                label: Text('الإعدادات'),
              ),
            ],
          ),
          const VerticalDivider(thickness: 1, width: 1),
          Expanded(
            child: IndexedStack(index: _selectedIndex, children: screens),
          ),
        ],
      ),
    );
  }
}

class TenantsManagementScreen extends ConsumerWidget {
  const TenantsManagementScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) => Scaffold(
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

class ProductsManagementScreen extends ConsumerWidget {
  const ProductsManagementScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) => Scaffold(
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

class UsersManagementScreen extends ConsumerWidget {
  const UsersManagementScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) => Scaffold(
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

class SettingsScreen extends ConsumerWidget {
  const SettingsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) => Scaffold(
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
        const SizedBox(height: 8),
        Card(
          child: ListTile(
            leading: const Icon(Icons.update),
            title: const Text('تحديثات النظام'),
            subtitle: const Text('فحص والتحديث إلى أحدث إصدار'),
            trailing: const Icon(Icons.arrow_forward_ios),
            onTap: () {},
          ),
        ),
      ],
    ),
  );
}
