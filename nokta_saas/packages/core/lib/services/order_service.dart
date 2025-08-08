import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../models/order.dart';
import '../db/local_db.dart';

class OrderService {
  final Dio _dio;
  
  OrderService(this._dio);
  
  Future<Order> createOrder(Order order) async {
    try {
      // Save to local database first
      final localOrderId = await LocalDB.insertOrder(order);
      final localOrder = order.copyWith(id: localOrderId);
      
      // Try to sync with server
      try {
        final response = await _dio.post('/orders', data: order.toJson());
        if (response.statusCode == 201) {
          final serverOrder = Order.fromJson(response.data);
          // Update local order with server ID
          await LocalDB.updateOrderStatus(localOrderId, 'synced');
          return serverOrder;
        }
      } catch (e) {
        // Continue with local order if sync fails
        print('Order sync failed, will retry later: $e');
      }
      
      return localOrder;
    } catch (e) {
      throw Exception('Failed to create order: $e');
    }
  }
  
  Future<List<Order>> getOrders({
    String? status,
    DateTime? fromDate,
    DateTime? toDate,
    int? limit,
  }) async {
    try {
      // Get from local database
      final localOrders = await LocalDB.getOrders(
        status: status,
        fromDate: fromDate,
        toDate: toDate,
        limit: limit,
      );
      
      // Try to sync with server
      _syncOrdersInBackground();
      
      return localOrders;
    } catch (e) {
      throw Exception('Failed to get orders: $e');
    }
  }
  
  Future<Order?> getOrder(int orderId) async {
    try {
      // Try to get from server first
      try {
        final response = await _dio.get('/orders/$orderId');
        if (response.statusCode == 200) {
          return Order.fromJson(response.data);
        }
      } catch (e) {
        // Fallback to local
      }
      
      // Get from local database
      final orders = await LocalDB.getOrders(limit: 1);
      return orders.firstWhere((o) => o.id == orderId);
    } catch (e) {
      return null;
    }
  }
  
  Future<bool> updateOrderStatus(int orderId, OrderStatus status) async {
    try {
      // Update local database
      await LocalDB.updateOrderStatus(orderId, status.name);
      
      // Try to sync with server
      try {
        final response = await _dio.put(
          '/orders/$orderId/status',
          data: {'status': status.name},
        );
        return response.statusCode == 200;
      } catch (e) {
        // Add to sync queue if failed
        return true; // Return true since local update succeeded
      }
    } catch (e) {
      return false;
    }
  }
  
  Future<bool> assignDriver(int orderId, int driverId) async {
    try {
      final response = await _dio.put(
        '/orders/$orderId/driver',
        data: {'driver_id': driverId},
      );
      return response.statusCode == 200;
    } catch (e) {
      return false;
    }
  }
  
  Future<bool> cancelOrder(int orderId, String reason) async {
    try {
      // Update local
      await LocalDB.updateOrderStatus(orderId, OrderStatus.cancelled.name);
      
      // Sync with server
      try {
        final response = await _dio.put(
          '/orders/$orderId/cancel',
          data: {'reason': reason},
        );
        return response.statusCode == 200;
      } catch (e) {
        return true; // Local update succeeded
      }
    } catch (e) {
      return false;
    }
  }
  
  Future<bool> refundOrder(int orderId, double amount, String reason) async {
    try {
      final response = await _dio.post(
        '/orders/$orderId/refund',
        data: {
          'amount': amount,
          'reason': reason,
        },
      );
      
      if (response.statusCode == 200) {
        await LocalDB.updateOrderStatus(orderId, OrderStatus.refunded.name);
        return true;
      }
      return false;
    } catch (e) {
      return false;
    }
  }
  
  Future<void> syncOrder(int orderId) async {
    try {
      final orders = await LocalDB.getOrders(limit: 1);
      final order = orders.firstWhere((o) => o.id == orderId);
      
      final response = await _dio.post('/orders/sync', data: order.toJson());
      
      if (response.statusCode == 200) {
        // Mark as synced in local database
        await LocalDB.markSyncCompleted(orderId);
      }
    } catch (e) {
      print('Failed to sync order $orderId: $e');
    }
  }
  
  Future<void> _syncOrdersInBackground() async {
    try {
      final pendingSync = await LocalDB.getPendingSyncRecords();
      
      for (final record in pendingSync) {
        if (record['table_name'] == 'orders') {
          await syncOrder(record['record_id'] as int);
        }
      }
    } catch (e) {
      print('Background sync failed: $e');
    }
  }
  
  // Kitchen Display System
  Stream<Order> getKitchenOrders() {
    // Return stream of orders for kitchen display
    return Stream.periodic(const Duration(seconds: 5), (_) async {
      final orders = await getOrders(
        status: OrderStatus.confirmed.name,
        limit: 20,
      );
      return orders;
    }).asyncExpand((orders) => Stream.fromIterable(orders));
  }
  
  Future<bool> markOrderReady(int orderId) async {
    return updateOrderStatus(orderId, OrderStatus.ready);
  }
  
  // Analytics
  Future<OrderAnalytics> getOrderAnalytics({
    DateTime? fromDate,
    DateTime? toDate,
  }) async {
    try {
      final response = await _dio.get('/orders/analytics', queryParameters: {
        'from_date': fromDate?.toIso8601String(),
        'to_date': toDate?.toIso8601String(),
      });
      
      if (response.statusCode == 200) {
        return OrderAnalytics.fromJson(response.data);
      }
      
      // Fallback to local analytics
      final stats = await LocalDB.getDashboardStats();
      return OrderAnalytics(
        totalOrders: stats['todayOrders'] as int,
        totalRevenue: (stats['todayRevenue'] as num).toDouble(),
        averageOrderValue: 0,
        topProducts: [],
      );
    } catch (e) {
      throw Exception('Failed to get analytics: $e');
    }
  }
}

// Order Analytics Model
class OrderAnalytics {
  final int totalOrders;
  final double totalRevenue;
  final double averageOrderValue;
  final List<TopProduct> topProducts;
  
  OrderAnalytics({
    required this.totalOrders,
    required this.totalRevenue,
    required this.averageOrderValue,
    required this.topProducts,
  });
  
  factory OrderAnalytics.fromJson(Map<String, dynamic> json) {
    return OrderAnalytics(
      totalOrders: json['total_orders'] as int,
      totalRevenue: (json['total_revenue'] as num).toDouble(),
      averageOrderValue: (json['average_order_value'] as num).toDouble(),
      topProducts: (json['top_products'] as List)
          .map((p) => TopProduct.fromJson(p))
          .toList(),
    );
  }
}

class TopProduct {
  final String name;
  final int quantity;
  final double revenue;
  
  TopProduct({
    required this.name,
    required this.quantity,
    required this.revenue,
  });
  
  factory TopProduct.fromJson(Map<String, dynamic> json) {
    return TopProduct(
      name: json['name'] as String,
      quantity: json['quantity'] as int,
      revenue: (json['revenue'] as num).toDouble(),
    );
  }
}

// Provider
final orderServiceProvider = Provider((ref) {
  final dio = ref.watch(dioProvider);
  return OrderService(dio);
});

// Dio Provider
final dioProvider = Provider((ref) {
  final dio = Dio(BaseOptions(
    baseUrl: 'https://api.nokta-pos.com/v1',
    connectTimeout: const Duration(seconds: 30),
    receiveTimeout: const Duration(seconds: 30),
  ));
  
  // Add interceptors
  dio.interceptors.add(LogInterceptor(
    requestBody: true,
    responseBody: true,
  ));
  
  return dio;
});
