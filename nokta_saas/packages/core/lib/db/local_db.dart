import 'package:sqflite/sqflite.dart';
import 'package:path/path.dart';

import '../models/product.dart';
import '../models/user.dart';
import '../models/cart_item.dart';

class LocalDB {
  static final LocalDB _instance = LocalDB._internal();
  factory LocalDB() => _instance;
  LocalDB._internal();

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
            password TEXT NOT NULL,
            restaurant_id INTEGER,
            branch_id INTEGER,
            role INTEGER
          );
        ''');

        await db.insert('users', {
          'username': 'boss',
          'password': '9999',
          'restaurant_id': 1,
          'branch_id': 1,
          'role': 0,
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
    return maps.map((m) => Product.fromMap(m)).toList();
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
    final res = await d.query(
      'users',
      where: 'username = ? AND password = ?',
      whereArgs: [username.trim(), password.trim()],
      limit: 1,
    );
    if (res.isEmpty) return null;
    return User.fromMap(res.first);
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
