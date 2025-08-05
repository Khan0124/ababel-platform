import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:riverpod/riverpod.dart';
import '../db/local_db.dart';
import '../models/order.dart';
import '../models/cart_item.dart';
import 'api.dart';

class OrderService {
  final LocalDB _localDB = LocalDB();
  final ApiService _apiService = ApiService();

  // Create new order
  Future<Order> createOrder({
    required List<CartItem> cartItems,
    required OrderType orderType,
    required PaymentMethod paymentMethod,
    String? customerName,
    String? customerPhone,
    String? customerAddress,
    int? tableNumber,
    String? specialInstructions,
  }) async {
    try {
      final subtotal = cartItems.fold<double>(
        0,
        (sum, item) => sum + item.totalPrice,
      );
      final tax = subtotal * 0.15; // 15% tax
      final total = subtotal + tax;

      if (kIsWeb) {
        final orderData = {
          'order_type': orderType.name,
          'payment_method': paymentMethod.name,
          'subtotal': subtotal,
          'tax': tax,
          'total': total,
          'customer_name': customerName,
          'customer_phone': customerPhone,
          'customer_address': customerAddress,
          'table_number': tableNumber,
          'special_instructions': specialInstructions,
          'items': cartItems
              .map(
                (item) => {
                  'product_id': item.product.id,
                  'quantity': item.quantity,
                  'unit_price': item.unitPrice,
                  'total_price': item.totalPrice,
                  'notes': item.notes,
                },
              )
              .toList(),
        };

        final response = await _apiService.post('/orders', data: orderData);
        return Order.fromJson(response.data);
      } else {
        // Use local DB
        final orderId = await _localDB.createOrder(
          total: total.toInt(),
          cart: cartItems,
          orderType: orderType.name,
          paymentMethod: paymentMethod.name,
          customerName: customerName,
          customerPhone: customerPhone,
          customerAddress: customerAddress,
        );

        return Order(
          id: orderId,
          tenantId: 1,
          branchId: 1,
          orderType: orderType,
          status: OrderStatus.pending,
          items: cartItems.map(OrderItem.fromCartItem).toList(),
          subtotal: subtotal,
          tax: tax,
          discount: 0,
          deliveryFee: 0,
          total: total,
          paymentMethod: paymentMethod,
          customerName: customerName,
          customerPhone: customerPhone,
          customerAddress: customerAddress,
          tableNumber: tableNumber,
          specialInstructions: specialInstructions,
          createdAt: DateTime.now(),
          updatedAt: DateTime.now(),
        );
      }
    } catch (e) {
      throw Exception('Failed to create order: $e');
    }
  }

  // Get all orders
  Future<List<Order>> getOrders({
    OrderStatus? status,
    int page = 1,
    int limit = 50,
  }) async {
    try {
      if (kIsWeb) {
        final queryParams = <String, dynamic>{'page': page, 'limit': limit};
        if (status != null) queryParams['status'] = status.name;

        final response = await _apiService.get(
          '/orders',
          queryParams: queryParams,
        );
        final List<dynamic> data = response.data['data'];

        return data.map((json) => Order.fromJson(json)).toList();
      } else {
        final orderMaps = await _localDB.allOrders();
        return orderMaps.map(Order.fromMap).toList();
      }
    } catch (e) {
      throw Exception('Failed to fetch orders: $e');
    }
  }

  // Get single order
  Future<Order> getOrder(int id) async {
    try {
      if (kIsWeb) {
        final response = await _apiService.get('/orders/$id');
        return Order.fromJson(response.data);
      } else {
        final orders = await getOrders();
        return orders.firstWhere((order) => order.id == id);
      }
    } catch (e) {
      throw Exception('Failed to fetch order: $e');
    }
  }

  // Update order status
  Future<Order> updateOrderStatus(int orderId, OrderStatus status) async {
    try {
      if (kIsWeb) {
        final response = await _apiService.put(
          '/orders/$orderId/status',
          data: {'status': status.name},
        );
        return Order.fromJson(response.data);
      } else {
        // For local DB, we'd need to implement update functionality
        throw UnimplementedError(
          'Order status update not implemented for local DB',
        );
      }
    } catch (e) {
      throw Exception('Failed to update order status: $e');
    }
  }

  // Cancel order
  Future<void> cancelOrder(int orderId, String reason) async {
    try {
      if (kIsWeb) {
        await _apiService.put(
          '/orders/$orderId/cancel',
          data: {'reason': reason},
        );
      } else {
        throw UnimplementedError(
          'Order cancellation not implemented for local DB',
        );
      }
    } catch (e) {
      throw Exception('Failed to cancel order: $e');
    }
  }

  // Get order statistics
  Future<OrderStatistics> getOrderStatistics() async {
    try {
      if (kIsWeb) {
        final response = await _apiService.get('/orders/statistics');
        return OrderStatistics.fromJson(response.data);
      } else {
        final orders = await getOrders();
        return OrderStatistics(
          totalOrders: orders.length,
          pendingOrders: orders
              .where((o) => o.status == OrderStatus.pending)
              .length,
          completedOrders: orders
              .where((o) => o.status == OrderStatus.delivered)
              .length,
          totalRevenue: orders.fold<double>(
            0,
            (sum, order) => sum + order.total,
          ),
        );
      }
    } catch (e) {
      throw Exception('Failed to fetch order statistics: $e');
    }
  }
}

// Order Statistics Model
class OrderStatistics {
  OrderStatistics({
    required this.totalOrders,
    required this.pendingOrders,
    required this.completedOrders,
    required this.totalRevenue,
  });

  factory OrderStatistics.fromJson(Map<String, dynamic> json) {
    return OrderStatistics(
      totalOrders: json['total_orders'],
      pendingOrders: json['pending_orders'],
      completedOrders: json['completed_orders'],
      totalRevenue: (json['total_revenue'] as num).toDouble(),
    );
  }
  final int totalOrders;
  final int pendingOrders;
  final int completedOrders;
  final double totalRevenue;
}

// Providers
final orderServiceProvider = Provider<OrderService>((ref) => OrderService());

final ordersProvider = FutureProvider.autoDispose
    .family<List<Order>, OrdersQuery>((ref, query) {
      final orderService = ref.watch(orderServiceProvider);
      return orderService.getOrders(
        status: query.status,
        page: query.page,
        limit: query.limit,
      );
    });

final orderProvider = FutureProvider.autoDispose.family<Order, int>((ref, id) {
  final orderService = ref.watch(orderServiceProvider);
  return orderService.getOrder(id);
});

final orderStatisticsProvider = FutureProvider.autoDispose<OrderStatistics>((
  ref,
) {
  final orderService = ref.watch(orderServiceProvider);
  return orderService.getOrderStatistics();
});

// Query class
class OrdersQuery {
  const OrdersQuery({this.status, this.page = 1, this.limit = 50});
  final OrderStatus? status;
  final int page;
  final int limit;

  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      other is OrdersQuery &&
          runtimeType == other.runtimeType &&
          status == other.status &&
          page == other.page &&
          limit == other.limit;

  @override
  int get hashCode => status.hashCode ^ page.hashCode ^ limit.hashCode;
}
