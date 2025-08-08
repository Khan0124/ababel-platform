// Fix script for common code issues in Nokta SaaS

import 'dart:io';

void main() async {
  print('===========================================');
  print('   NOKTA SAAS CODE FIXER');
  print('===========================================\n');

  // Fix 1: Update all Provider imports to Riverpod
  await fixProviderImports();
  
  // Fix 2: Fix const color issues
  await fixConstColorIssues();
  
  // Fix 3: Fix super parameter issues
  await fixSuperParameterIssues();
  
  // Fix 4: Fix missing imports
  await fixMissingImports();
  
  print('\n✅ Code fixes completed!');
  print('Run "flutter analyze" to verify all issues are resolved.');
}

Future<void> fixProviderImports() async {
  print('Fixing Provider imports to Riverpod...');
  
  final directories = [
    'packages/core/lib',
    'apps/admin_panel/lib',
    'apps/customer_app/lib',
    'apps/driver_app/lib',
    'apps/pos_app/lib',
  ];
  
  for (final dir in directories) {
    final directory = Directory(dir);
    if (await directory.exists()) {
      await for (final file in directory.list(recursive: true)) {
        if (file is File && file.path.endsWith('.dart')) {
          String content = await file.readAsString();
          
          // Replace Provider imports with Riverpod
          content = content.replaceAll(
            "import 'package:provider/provider.dart';",
            "import 'package:flutter_riverpod/flutter_riverpod.dart';"
          );
          
          // Replace StateNotifierProvider usage
          content = content.replaceAll(
            'Provider<',
            'Provider<'
          );
          
          // Fix Provider.of calls
          content = content.replaceAll(
            'Provider.of<',
            'ref.watch('
          );
          
          await file.writeAsString(content);
        }
      }
    }
  }
}

Future<void> fixConstColorIssues() async {
  print('Fixing const color issues...');
  
  final directories = [
    'packages/core/lib',
    'apps/admin_panel/lib',
    'apps/customer_app/lib',
    'apps/driver_app/lib',
    'apps/pos_app/lib',
  ];
  
  for (final dir in directories) {
    final directory = Directory(dir);
    if (await directory.exists()) {
      await for (final file in directory.list(recursive: true)) {
        if (file is File && file.path.endsWith('.dart')) {
          String content = await file.readAsString();
          
          // Fix Color.fromRGBO in const contexts
          content = content.replaceAll(
            RegExp(r'const\s+Color\.fromRGBO\(([\d,\s\.]+)\)'),
            'Color.fromRGBO(\$1)'
          );
          
          // Fix Colors.xxx.shade in const contexts
          content = content.replaceAll(
            RegExp(r'const\s+(Colors\.\w+\.shade\d+)'),
            '\$1'
          );
          
          await file.writeAsString(content);
        }
      }
    }
  }
}

Future<void> fixSuperParameterIssues() async {
  print('Fixing super parameter issues...');
  
  final directories = [
    'packages/core/lib',
    'apps/admin_panel/lib',
    'apps/customer_app/lib',
    'apps/driver_app/lib',
    'apps/pos_app/lib',
  ];
  
  for (final dir in directories) {
    final directory = Directory(dir);
    if (await directory.exists()) {
      await for (final file in directory.list(recursive: true)) {
        if (file is File && file.path.endsWith('.dart')) {
          String content = await file.readAsString();
          
          // Fix super.key patterns
          content = content.replaceAll(
            RegExp(r'\{Key\?\s+key\}\)\s*:\s*super\(key:\s*key\)'),
            '{super.key})'
          );
          
          await file.writeAsString(content);
        }
      }
    }
  }
}

Future<void> fixMissingImports() async {
  print('Adding missing imports...');
  
  final importMappings = {
    'GoRouter': "import 'package:go_router/go_router.dart';",
    'StateNotifierProvider': "import 'package:flutter_riverpod/flutter_riverpod.dart';",
    'freezed': "import 'package:freezed_annotation/freezed_annotation.dart';",
    'Dio': "import 'package:dio/dio.dart';",
    'testWidgets': "import 'package:flutter_test/flutter_test.dart';",
  };
  
  final directories = [
    'packages/core/lib',
    'apps/admin_panel/lib',
    'apps/customer_app/lib',
    'apps/driver_app/lib',
    'apps/pos_app/lib',
  ];
  
  for (final dir in directories) {
    final directory = Directory(dir);
    if (await directory.exists()) {
      await for (final file in directory.list(recursive: true)) {
        if (file is File && file.path.endsWith('.dart')) {
          String content = await file.readAsString();
          
          // Check and add missing imports
          for (final entry in importMappings.entries) {
            if (content.contains(entry.key) && !content.contains(entry.value)) {
              // Add import after the first line
              final lines = content.split('\n');
              lines.insert(1, entry.value);
              content = lines.join('\n');
            }
          }
          
          await file.writeAsString(content);
        }
      }
    }
  }
}
