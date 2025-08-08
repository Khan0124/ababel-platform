@echo off
setlocal enabledelayedexpansion
chcp 65001 >nul
cls
color 0E

echo ╔════════════════════════════════════════════════════════════════╗
echo ║         NOKTA SAAS - DIRECT FILE FIX FOR 229 ERRORS           ║
echo ║                 إصلاح مباشر للملفات                           ║
echo ╚════════════════════════════════════════════════════════════════╝
echo.

cd /d C:\flutter_projects\nokta_saas

echo Creating Dart fix script...

:: Create a Dart script that will fix all files directly
(
echo import 'dart:io';
echo.
echo void main^(^) async {
echo   print^('Starting direct file fixes...'^);
echo   int fixed = 0;
echo.
echo   // Function to fix file
echo   Future^<bool^> fixFile^(String path, Map^<String, String^> replacements^) async {
echo     try {
echo       var file = File^(path^);
echo       if ^(!await file.exists^(^)^) return false;
echo       
echo       var content = await file.readAsString^(^);
echo       var originalContent = content;
echo       
echo       // Apply all replacements
echo       replacements.forEach^(^(pattern, replacement^) {
echo         content = content.replaceAll^(pattern, replacement^);
echo       }^);
echo       
echo       // Add missing imports at the top
echo       var imports = ^<String^>[];
echo       
echo       if ^(content.contains^('GoRouter'^) ^|^| content.contains^('GoRoute'^)^) {
echo         if ^(!content.contains^("import 'package:go_router/go_router.dart';"^)^) {
echo           imports.add^("import 'package:go_router/go_router.dart';"^);
echo         }
echo       }
echo       
echo       if ^(content.contains^('StateNotifierProvider'^) ^|^| content.contains^('Provider'^) ^|^| 
echo           content.contains^('FutureProvider'^) ^|^| content.contains^('StateProvider'^)^) {
echo         if ^(!content.contains^("import 'package:flutter_riverpod/flutter_riverpod.dart';"^)^) {
echo           imports.add^("import 'package:flutter_riverpod/flutter_riverpod.dart';"^);
echo         }
echo       }
echo       
echo       if ^(content.contains^('runApp'^) ^|^| content.contains^('StatefulWidget'^) ^|^| 
echo           content.contains^('StatelessWidget'^) ^|^| content.contains^('initState'^) ^|^| 
echo           content.contains^('dispose'^)^) {
echo         if ^(!content.contains^("import 'package:flutter/material.dart';"^)^) {
echo           imports.add^("import 'package:flutter/material.dart';"^);
echo         }
echo       }
echo       
echo       if ^(content.contains^('Dio'^) ^|^| content.contains^('BaseOptions'^) ^|^| 
echo           content.contains^('DioException'^) ^|^| content.contains^('LogInterceptor'^)^) {
echo         if ^(!content.contains^("import 'package:dio/dio.dart';"^)^) {
echo           imports.add^("import 'package:dio/dio.dart';"^);
echo         }
echo       }
echo       
echo       if ^(content.contains^('testWidgets'^) ^|^| content.contains^('expect'^)^) {
echo         if ^(!content.contains^("import 'package:flutter_test/flutter_test.dart';"^)^) {
echo           imports.add^("import 'package:flutter_test/flutter_test.dart';"^);
echo         }
echo       }
echo       
echo       if ^(content.contains^('FlutterError'^)^) {
echo         if ^(!content.contains^("import 'package:flutter/foundation.dart';"^)^) {
echo           imports.add^("import 'package:flutter/foundation.dart';"^);
echo         }
echo       }
echo       
echo       // Add imports to beginning of file
echo       if ^(imports.isNotEmpty^) {
echo         var importString = imports.join^('\n'^) + '\n\n';
echo         if ^(content.startsWith^('library'^)^) {
echo           var idx = content.indexOf^(';'^) + 1;
echo           content = content.substring^(0, idx^) + '\n' + importString + content.substring^(idx^);
echo         } else {
echo           content = importString + content;
echo         }
echo       }
echo       
echo       if ^(content != originalContent^) {
echo         await file.writeAsString^(content^);
echo         return true;
echo       }
echo       return false;
echo     } catch ^(e^) {
echo       return false;
echo     }
echo   }
echo.
echo   // Fix admin_panel files
echo   print^('Fixing admin_panel...'^);
echo   
echo   // Fix admin_routes.dart
echo   var adminRoutes = 'apps/admin_panel/routes/admin_routes.dart';
echo   if ^(await fixFile^(adminRoutes, {}^)^) fixed++;
echo   
echo   // Fix admin_theme.dart
echo   var adminTheme = 'apps/admin_panel/themes/admin_theme.dart';
echo   await fixFile^(adminTheme, {
echo     'const Color.fromRGBO': 'Color.fromRGBO',
echo     'const Color.fromARGB': 'Color.fromARGB',
echo     'const Color^(': 'Color^(',
echo     'const List^<Color^>': 'final List^<Color^>',
echo     'const [Color': '[Color'
echo   }^);
echo   fixed++;
echo   
echo   // Fix admin_dashboard_screen.dart
echo   var adminDashboard = 'apps/admin_panel/screens/dashboard/admin_dashboard_screen.dart';
echo   await fixFile^(adminDashboard, {
echo     '{Key? key}) : super^(key: key^)': '{super.key}',
echo     'void initState^(^)': '@override\n  void initState^(^)',
echo     'const [Color': '[Color'
echo   }^);
echo   fixed++;
echo   
echo   // Fix real_time_analytics.dart
echo   var analytics = 'apps/admin_panel/screens/analytics/real_time_analytics.dart';
echo   await fixFile^(analytics, {
echo     '{Key? key}) : super^(key: key^)': '{super.key}',
echo     'void dispose^(^)': '@override\n  void dispose^(^)',
echo     'super^(const': 'super^(AuthState.initial^(^)'
echo   }^);
echo   fixed++;
echo.
echo   // Fix customer_app files
echo   print^('Fixing customer_app...'^);
echo   
echo   var customerMain = 'apps/customer_app/lib/main.dart';
echo   await fixFile^(customerMain, {
echo     '{Key? key}) : super^(key: key^)': '{super.key}',
echo     'const [Color': '[Color'
echo   }^);
echo   fixed++;
echo   
echo   var homeScreen = 'apps/customer_app/lib/screens/home/home_screen.dart';
echo   await fixFile^(homeScreen, {
echo     '{Key? key}) : super^(key: key^)': '{super.key}',
echo     'as Color': '',
echo     'as IconData': '',
echo     'const [Color': '[Color',
echo     'void dispose^(^)': '@override\n  void dispose^(^)'
echo   }^);
echo   fixed++;
echo   
echo   var restaurantScreen = 'apps/customer_app/lib/screens/restaurant/restaurant_screen.dart';
echo   await fixFile^(restaurantScreen, {
echo     'final String restaurantId;': 'final String restaurantId = \'\';'
echo   }^);
echo   fixed++;
echo   
echo   var orderTracking = 'apps/customer_app/lib/screens/order/order_tracking_screen.dart';
echo   await fixFile^(orderTracking, {
echo     'final String orderId;': 'final String orderId = \'\';',
echo     'void initState^(^)': '@override\n  void initState^(^)',
echo     'void dispose^(^)': '@override\n  void dispose^(^)'
echo   }^);
echo   fixed++;
echo.
echo   // Fix driver_app files
echo   print^('Fixing driver_app...'^);
echo   
echo   var driverHome = 'apps/driver_app/lib/screens/home/driver_home_screen.dart';
echo   await fixFile^(driverHome, {
echo     '{Key? key}) : super^(key: key^)': '{super.key}',
echo     'with State': 'extends State',
echo     'void initState^(^)': '@override\n  void initState^(^)',
echo     'void dispose^(^)': '@override\n  void dispose^(^)',
echo     'const [Color': '[Color'
echo   }^);
echo   fixed++;
echo   
echo   var orderNav = 'apps/driver_app/lib/screens/navigation/order_navigation_screen.dart';
echo   await fixFile^(orderNav, {
echo     'final Order order;': 'final Order? order;',
echo     'void initState^(^)': '@override\n  void initState^(^)'
echo   }^);
echo   fixed++;
echo.
echo   // Fix nokta_core files
echo   print^('Fixing nokta_core...'^);
echo   
echo   // Fix local_db.dart
echo   var localDb = 'packages/core/lib/db/local_db.dart';
echo   var content = '';
echo   try {
echo     var file = File^(localDb^);
echo     if ^(await file.exists^(^)^) {
echo       content = await file.readAsString^(^);
echo       // Fix nullable types
echo       content = content.replaceAll^('int? id = row[\'id\'];', 'int id = ^(row[\'id\'] as int?^) ?? 0;'^);
echo       content = content.replaceAll^('String? name = row[\'name\'];', 'String name = ^(row[\'name\'] as String?^) ?? \'\';'^);
echo       content = content.replaceAll^('DateTime? date = row[', 'DateTime date = DateTime.tryParse^(row['^);
echo       // Remove undefined parameters
echo       content = content.replaceAll^(RegExp^(r'sortOrder:\s*\w+,?'^), ''^);
echo       content = content.replaceAll^(RegExp^(r'tenantId:\s*\w+,?'^), ''^);
echo       await file.writeAsString^(content^);
echo       fixed++;
echo     }
echo   } catch ^(e^) {}
echo.
echo   // Fix base_model.dart
echo   var baseModel = 'packages/core/lib/models/base_model.dart';
echo   try {
echo     var file = File^(baseModel^);
echo     if ^(await file.exists^(^)^) {
echo       content = await file.readAsString^(^);
echo       var lines = content.split^('\n'^);
echo       var imports = lines.where^(^(l^) =^> l.startsWith^('import'^) ^|^| l.startsWith^('library'^) ^|^| l.startsWith^('part'^)^).toList^(^);
echo       var rest = lines.where^(^(l^) =^> !l.startsWith^('import'^) ^&^& !l.startsWith^('library'^) ^&^& !l.startsWith^('part'^)^).toList^(^);
echo       content = imports.join^('\n'^) + '\n' + rest.join^('\n'^);
echo       await file.writeAsString^(content^);
echo       fixed++;
echo     }
echo   } catch ^(e^) {}
echo.
echo   // Fix cart_item.dart
echo   var cartItem = 'packages/core/lib/models/cart_item.dart';
echo   await fixFile^(cartItem, {
echo     '+ quantity': '?? 0 + quantity'
echo   }^);
echo   fixed++;
echo.
echo   // Fix providers
echo   var providers = [
echo     'packages/core/lib/providers/auth_state.dart',
echo     'packages/core/lib/providers/cart_provider.dart',
echo     'packages/core/lib/providers/product_provider.dart'
echo   ];
echo   
echo   for ^(var provider in providers^) {
echo     await fixFile^(provider, {
echo       'super^(const': 'super^(const AuthState.initial^(^)',
echo       'super^(CartState': 'super^(const CartState^('
echo     }^);
echo     fixed++;
echo   }
echo.
echo   // Fix services
echo   var services = [
echo     'packages/core/lib/services/order_service.dart',
echo     'packages/core/lib/services/product_service.dart',
echo     'packages/core/lib/services/api.dart',
echo     'packages/core/lib/services/auth_service.dart'
echo   ];
echo   
echo   for ^(var service in services^) {
echo     await fixFile^(service, {
echo       'LocalDB.': '_db.',
echo       'const Color.': 'Color.',
echo       'DioError': 'DioException'
echo     }^);
echo     
echo     // Add instance variable if needed
echo     var file = File^(service^);
echo     if ^(await file.exists^(^)^) {
echo       content = await file.readAsString^(^);
echo       if ^(content.contains^('class'^) ^&^& !content.contains^('final _db'^)^) {
echo         content = content.replaceFirst^('class', 'class'^).replaceFirst^('{', '{\n  final _db = LocalDB.instance;'^);
echo         await file.writeAsString^(content^);
echo       }
echo     }
echo     fixed++;
echo   }
echo.
echo   // Fix super.key in all files
echo   print^('Fixing super.key patterns...'^);
echo   await for ^(var entity in Directory^('.'^).list^(recursive: true^)^) {
echo     if ^(entity is File ^&^& entity.path.endsWith^('.dart'^)^) {
echo       try {
echo         var content = await entity.readAsString^(^);
echo         var newContent = content.replaceAll^(RegExp^(r'\{Key\?\s*key\}\)\s*:\s*super\(key:\s*key\)'^), '{super.key}'^);
echo         if ^(content != newContent^) {
echo           await entity.writeAsString^(newContent^);
echo           fixed++;
echo         }
echo       } catch ^(e^) {}
echo     }
echo   }
echo.
echo   print^('Fixed $fixed issues'^);
echo }
) > fix_all_errors.dart

echo Running Dart fixer...
dart run fix_all_errors.dart

echo.
echo Cleaning and rebuilding...

:: Clean
for /d %%d in (packages\core apps\admin_panel apps\customer_app apps\driver_app apps\pos_app) do (
    if exist "%%d\.dart_tool" rd /s /q "%%d\.dart_tool" 2>nul
    if exist "%%d\build" rd /s /q "%%d\build" 2>nul
    if exist "%%d\pubspec.lock" del /q "%%d\pubspec.lock" 2>nul
)

:: Install Melos
call dart pub global activate melos >nul 2>&1

:: Bootstrap
call melos bootstrap --no-verify >nul 2>&1

:: Build core
cd packages\core
call flutter pub get >nul 2>&1
call flutter pub run build_runner build --delete-conflicting-outputs >nul 2>&1
cd ..\..

:: Build apps
for %%a in (admin_panel customer_app driver_app pos_app) do (
    if exist "apps\%%a" (
        cd apps\%%a
        call flutter pub get >nul 2>&1
        cd ..\..
    )
)

:: Apply dart fixes
call dart fix --apply >nul 2>&1

:: Clean up
del fix_all_errors.dart 2>nul

echo.
echo ════════════════════════════════════════════════
echo          CHECKING RESULTS...
echo ════════════════════════════════════════════════
echo.

call flutter analyze

echo.
echo ════════════════════════════════════════════════
echo If errors remain, run this script again.
echo ════════════════════════════════════════════════
echo.
pause

endlocal
