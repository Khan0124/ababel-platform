// nokta_pos main database file
import 'package:sqflite/sqflite.dart';
import 'package:path/path.dart';
import '../models/product.dart';
import '../models/order.dart';
import '../models/category.dart';

class LocalDB {
  static Database? _database;
  static LocalDB? _instance;

  LocalDB._();

  static LocalDB get instance => _instance ??= LocalDB._();

  static Future<void> initialize() async {
    _instance ??= LocalDB._();
    await _instance!._initDatabase();
  }

  Future<Database> get database async {
    _database ??= await _initDatabase();
    return _database!;
  }

  Future<Database> _initDatabase() async {
    final dbPath = await getDatabasesPath();
    final path = join(dbPath, 'nokta_pos.db');
    
    return await openDatabase(
      path,
      version: 1,
      onCreate: _onCreate,
      onUpgrade: _onUpgrade,
    );
  }

  Future<void> _onCreate(Database db, int version) async {
    // Products table
    await db.execute('''
      CREATE TABLE products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        description TEXT,
        price REAL NOT NULL,
        categoryId INTEGER,
        imageUrl TEXT,
        barcode TEXT,
        sku TEXT,
        isAvailable INTEGER DEFAULT 1,
        stockQuantity INTEGER DEFAULT 0,
        createdAt TEXT,
        updatedAt TEXT
      )
    ''');

    // Categories table
    await db.execute('''
      CREATE TABLE categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        description TEXT,
        parentId INTEGER,
        sortOrder INTEGER DEFAULT 0,
        isActive INTEGER DEFAULT 1,
        createdAt TEXT,
        updatedAt TEXT
      )
    ''');

    // Orders table
    await db.execute('''
      CREATE TABLE orders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        orderNumber TEXT NOT NULL,
        status TEXT NOT NULL,
        orderType TEXT NOT NULL,
        subtotal REAL NOT NULL,
        tax REAL DEFAULT 0,
        discount REAL DEFAULT 0,
        deliveryFee REAL DEFAULT 0,
        total REAL NOT NULL,
        paymentMethod TEXT,
        paymentStatus TEXT,
        customerName TEXT,
        customerPhone TEXT,
        customerEmail TEXT,
        customerAddress TEXT,
        tableNumber TEXT,
        specialInstructions TEXT,
        createdAt TEXT NOT NULL,
        updatedAt TEXT,
        syncStatus TEXT DEFAULT 'pending'
      )
    ''');

    // Order items table
    await db.execute('''
      CREATE TABLE order_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        orderId INTEGER NOT NULL,
        productId INTEGER NOT NULL,
        productName TEXT NOT NULL,
        quantity INTEGER NOT NULL,
        unitPrice REAL NOT NULL,
        totalPrice REAL NOT NULL,
        notes TEXT,
        FOREIGN KEY (orderId) REFERENCES orders (id),
        FOREIGN KEY (productId) REFERENCES products (id)
      )
    ''');

    // Settings table
    await db.execute('''
      CREATE TABLE settings (
        key TEXT PRIMARY KEY,
        value TEXT NOT NULL
      )
    ''');
  }

  Future<void> _onUpgrade(Database db, int oldVersion, int newVersion) async {
    // Handle database upgrades
  }

  // Product operations
  Future<List<Product>> getProducts() async {
    final db = await database;
    final maps = await db.query('products', where: 'isAvailable = ?', whereArgs: [1]);
    return maps.map((map) => _productFromMap(map)).toList();
  }

  Future<Product?> getProduct(int id) async {
    final db = await database;
    final maps = await db.query('products', where: 'id = ?', whereArgs: [id]);
    if (maps.isEmpty) return null;
    return _productFromMap(maps.first);
  }

  Future<int> insertProduct(Product product) async {
    final db = await database;
    return await db.insert('products', _productToMap(product));
  }

  Future<int> updateProduct(Product product) async {
    final db = await database;
    return await db.update(
      'products',
      _productToMap(product),
      where: 'id = ?',
      whereArgs: [product.id],
    );
  }

  Future<int> deleteProduct(int id) async {
    final db = await database;
    return await db.delete('products', where: 'id = ?', whereArgs: [id]);
  }

  // Order operations
  Future<List<Order>> getOrders({String? status}) async {
    final db = await database;
    if (status != null) {
      final maps = await db.query('orders', where: 'status = ?', whereArgs: [status]);
      return maps.map((map) => _orderFromMap(map)).toList();
    }
    final maps = await db.query('orders', orderBy: 'createdAt DESC');
    return maps.map((map) => _orderFromMap(map)).toList();
  }

  Future<Order?> getOrder(int id) async {
    final db = await database;
    final maps = await db.query('orders', where: 'id = ?', whereArgs: [id]);
    if (maps.isEmpty) return null;
    return _orderFromMap(maps.first);
  }

  Future<int> insertOrder(Order order) async {
    final db = await database;
    return await db.insert('orders', _orderToMap(order));
  }

  Future<int> updateOrder(Order order) async {
    final db = await database;
    return await db.update(
      'orders',
      _orderToMap(order),
      where: 'id = ?',
      whereArgs: [order.id],
    );
  }

  // Category operations
  Future<List<Category>> getCategories() async {
    final db = await database;
    final maps = await db.query('categories', where: 'isActive = ?', whereArgs: [1]);
    return maps.map((map) => _categoryFromMap(map)).toList();
  }

  // Helper methods for mapping
  Product _productFromMap(Map<String, dynamic> map) {
    return Product(
      id: map['id'] as int?,
      name: map['name'] as String,
      description: map['description'] as String?,
      price: map['price'] as double,
      categoryId: map['categoryId'] as int?,
      imageUrl: map['imageUrl'] as String?,
      barcode: map['barcode'] as String?,
      sku: map['sku'] as String?,
      isAvailable: map['isAvailable'] == 1,
      stockQuantity: map['stockQuantity'] as int?,
      tenantId: 1, // Default tenant
      createdAt: DateTime.now(),
      updatedAt: DateTime.now(),
    );
  }

  Map<String, dynamic> _productToMap(Product product) {
    return {
      'id': product.id,
      'name': product.name,
      'description': product.description,
      'price': product.price,
      'categoryId': product.categoryId,
      'imageUrl': product.imageUrl,
      'barcode': product.barcode,
      'sku': product.sku,
      'isAvailable': product.isAvailable ? 1 : 0,
      'stockQuantity': product.stockQuantity,
      'createdAt': product.createdAt.toIso8601String(),
      'updatedAt': product.updatedAt.toIso8601String(),
    };
  }

  Order _orderFromMap(Map<String, dynamic> map) {
    return Order(
      id: map['id'] as int?,
      orderNumber: map['orderNumber'] as String,
      status: OrderStatus.values.firstWhere(
        (e) => e.name == map['status'],
        orElse: () => OrderStatus.pending,
      ),
      orderType: OrderType.values.firstWhere(
        (e) => e.name == map['orderType'],
        orElse: () => OrderType.dineIn,
      ),
      subtotal: map['subtotal'] as double,
      tax: map['tax'] as double? ?? 0,
      discount: map['discount'] as double? ?? 0,
      deliveryFee: map['deliveryFee'] as double? ?? 0,
      total: map['total'] as double,
      paymentMethod: map['paymentMethod'] as String?,
      paymentStatus: map['paymentStatus'] as String?,
      customerName: map['customerName'] as String?,
      customerPhone: map['customerPhone'] as String?,
      customerEmail: map['customerEmail'] as String?,
      customerAddress: map['customerAddress'] as String?,
      tableNumber: map['tableNumber'] as String?,
      specialInstructions: map['specialInstructions'] as String?,
      createdAt: DateTime.parse(map['createdAt'] as String),
      updatedAt: map['updatedAt'] != null 
        ? DateTime.parse(map['updatedAt'] as String)
        : null,
      items: [], // Load separately
      tenantId: 1,
      branchId: 1,
    );
  }

  Map<String, dynamic> _orderToMap(Order order) {
    return {
      'id': order.id,
      'orderNumber': order.orderNumber,
      'status': order.status.name,
      'orderType': order.orderType.name,
      'subtotal': order.subtotal,
      'tax': order.tax,
      'discount': order.discount,
      'deliveryFee': order.deliveryFee,
      'total': order.total,
      'paymentMethod': order.paymentMethod,
      'paymentStatus': order.paymentStatus,
      'customerName': order.customerName,
      'customerPhone': order.customerPhone,
      'customerEmail': order.customerEmail,
      'customerAddress': order.customerAddress,
      'tableNumber': order.tableNumber,
      'specialInstructions': order.specialInstructions,
      'createdAt': order.createdAt.toIso8601String(),
      'updatedAt': order.updatedAt.toIso8601String(),
    };
  }

  Category _categoryFromMap(Map<String, dynamic> map) {
    return Category(
      id: map['id'] as int?,
      name: map['name'] as String,
      description: map['description'] as String?,
      parentId: map['parentId'] as int?,
      sortOrder: map['sortOrder'] as int? ?? 0,
      isActive: map['isActive'] == 1,
      tenantId: 1,
      createdAt: DateTime.now(),
      updatedAt: DateTime.now(),
    );
  }

  // Clear all data
  Future<void> clearAllData() async {
    final db = await database;
    await db.delete('products');
    await db.delete('categories');
    await db.delete('orders');
    await db.delete('order_items');
    await db.delete('settings');
  }

  // Close database
  Future<void> close() async {
    final db = await database;
    await db.close();
    _database = null;
  }
}
