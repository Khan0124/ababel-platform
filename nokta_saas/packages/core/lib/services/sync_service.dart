// lib/services/sync_service.dart
import 'dart:convert';
import 'package:nokta_pos/services/api.dart';
import 'package:nokta_pos/db/local_db.dart';

class SyncService {
  static final LocalDB _localDB = LocalDB();

  static Future<void> syncAll() async {
    await _syncProducts();
    await _syncOrders();
  }

  static Future<void> _syncProducts() async {
    final lastUpdate = await _localDB.getLastProductUpdate();
    final response = await ApiService.get('/products?lastUpdate=$lastUpdate');
    if (response.statusCode == 200) {
      final products = (jsonDecode(response.body) as List)
          .map(Product.fromJson)
          .toList();
      await _localDB.saveProducts(products);
      await _localDB.setLastProductUpdate(DateTime.now());
    }
  }

  static Future<void> _syncOrders() async {
    final pendingOrders = await _localDB.getPendingOrders();
    for (final order in pendingOrders) {
      final response = await ApiService.createOrder(order.toJson());
      if (response.statusCode == 201) {
        await _localDB.markOrderAsSynced(order.id);
      }
    }
  }
}
