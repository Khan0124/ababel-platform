import 'package:sqflite/sqflite.dart';
import 'package:path/path.dart';
import 'package:crypto/crypto.dart';
import 'dart:convert';

import '../models/product.dart';
import '../models/user.dart';
import '../models/cart_item.dart';
import '../services/security_service.dart';

class LocalDB {
  factory LocalDB() => _instance;
  LocalDB._internal();
  static final LocalDB _instance = LocalDB._internal();

  Database? _db;

  Future<Database> get db async {
    _db ??= await _init();
    return _db!;
  }

  Future<Database> _init() async {
    final dbPath = await getDatabasesPath();
    final path = join(dbPath, 'nokta_pos.db');

    return openDatabase(
      path,
      version: 1,
      onCreate: (db, version) async {
        await db.execute('''
          CREATE TABLE products(
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            price INTEGER NOT NULL
          );
        ''');

        await db.execute('''
          CREATE TABLE users(
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            restaurant_id INTEGER,
            branch_id INTEGER,
            tenant_id INTEGER NOT NULL DEFAULT 1,
            role INTEGER NOT NULL DEFAULT 0,
            is_active INTEGER NOT NULL DEFAULT 1,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            last_login_at TEXT,
            failed_login_attempts INTEGER NOT NULL DEFAULT 0,
            locked_until TEXT
          );
        ''');

        // Create secure admin user
        final adminPassword = SecurityService.hashPassword('Admin@2024#Secure');
        await db.insert('users', {
          'username': 'admin',
          'password_hash': adminPassword,
          'restaurant_id': 1,
          'branch_id': 1,
          'tenant_id': 1,
          'role': 0, // Admin role
          'is_active': 1,
          'created_at': DateTime.now().toIso8601String(),
          'updated_at': DateTime.now().toIso8601String(),
        });

        await db.execute('''
          CREATE TABLE orders(
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            total INTEGER NOT NULL,
            order_type TEXT NOT NULL,
            payment_method TEXT NOT NULL,
            customer_name TEXT,
            customer_phone TEXT,
            customer_address TEXT,
            created_at TEXT NOT NULL
          );
        ''');

        await db.execute('''
          CREATE TABLE order_items(
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INTEGER NOT NULL,
            product_id INTEGER NOT NULL,
            qty INTEGER NOT NULL,
            price INTEGER NOT NULL,
            FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY(product_id) REFERENCES products(id)
          );
        ''');
      },
      onOpen: (db) async {
        await db.execute('PRAGMA foreign_keys = ON');
      },
    );
  }

  Future<List<Product>> allProducts() async {
    final d = await db;
    final maps = await d.query('products', orderBy: 'id DESC');
    return maps.map(Product.fromMap).toList();
  }

  Future<int> insertProduct(Product p) async {
    final d = await db;
    final map = p.toMap();
    map.remove('id');
    return d.insert('products', map);
  }

  Future<int> updateProduct(Product p) async {
    final d = await db;
    return d.update('products', p.toMap(), where: 'id = ?', whereArgs: [p.id]);
  }

  Future<int> deleteProduct(int id) async {
    final d = await db;
    return d.delete('products', where: 'id = ?', whereArgs: [id]);
  }

  Future<User?> login(String username, String password) async {
    final d = await db;

    // Validate and sanitize input
    final cleanUsername = SecurityService.sanitizeInput(username.trim());
    if (!SecurityService.validateInput(cleanUsername) ||
        !SecurityService.validateInput(password)) {
      return null;
    }

    // Check if user exists and is active
    final res = await d.query(
      'users',
      where: 'username = ? AND is_active = 1',
      whereArgs: [cleanUsername],
      limit: 1,
    );

    if (res.isEmpty) return null;

    final userMap = res.first;

    // Check if account is locked
    if (userMap['locked_until'] != null) {
      final lockedUntil = DateTime.parse(userMap['locked_until']! as String);
      if (DateTime.now().isBefore(lockedUntil)) {
        return null; // Account is locked
      }
    }

    // Verify password
    final storedHash = userMap['password_hash']! as String;
    if (!SecurityService.verifyPassword(password, storedHash)) {
      // Increment failed login attempts
      await _incrementFailedLogins(userMap['id']! as int);
      return null;
    }

    // Reset failed login attempts and update last login
    await _resetFailedLogins(userMap['id']! as int);

    return User.fromMap(userMap);
  }

  Future<void> _incrementFailedLogins(int userId) async {
    final d = await db;

    // Get current failed attempts
    final res = await d.query(
      'users',
      columns: ['failed_login_attempts'],
      where: 'id = ?',
      whereArgs: [userId],
    );

    if (res.isNotEmpty) {
      final currentAttempts = res.first['failed_login_attempts']! as int;
      final newAttempts = currentAttempts + 1;

      // Lock account if too many failed attempts (5 attempts)
      String? lockedUntil;
      if (newAttempts >= 5) {
        lockedUntil = DateTime.now()
            .add(const Duration(minutes: 30))
            .toIso8601String();
      }

      await d.update(
        'users',
        {
          'failed_login_attempts': newAttempts,
          'locked_until': lockedUntil,
          'updated_at': DateTime.now().toIso8601String(),
        },
        where: 'id = ?',
        whereArgs: [userId],
      );
    }
  }

  Future<void> _resetFailedLogins(int userId) async {
    final d = await db;
    await d.update(
      'users',
      {
        'failed_login_attempts': 0,
        'locked_until': null,
        'last_login_at': DateTime.now().toIso8601String(),
        'updated_at': DateTime.now().toIso8601String(),
      },
      where: 'id = ?',
      whereArgs: [userId],
    );
  }

  Future<int> createOrder({
    required int total,
    required List<CartItem> cart,
    required String orderType,
    required String paymentMethod,
    String? customerName,
    String? customerPhone,
    String? customerAddress,
  }) async {
    final d = await db;
    final orderId = await d.insert('orders', {
      'total': total,
      'order_type': orderType,
      'payment_method': paymentMethod,
      'customer_name': customerName,
      'customer_phone': customerPhone,
      'customer_address': customerAddress,
      'created_at': DateTime.now().toIso8601String(),
    });

    for (final ci in cart) {
      await d.insert('order_items', {
        'order_id': orderId,
        'product_id': ci.product.id,
        'qty': ci.quantity,
        'price': ci.product.price,
      });
    }

    return orderId;
  }

  Future<List<Map<String, Object?>>> allOrders() async {
    final d = await db;
    return d.query('orders', orderBy: 'id DESC');
  }

  Future<void> deleteDatabaseFile() async {
    final dbPath = await getDatabasesPath();
    final path = join(dbPath, 'nokta_pos.db');
    await deleteDatabase(path);
  }
}
