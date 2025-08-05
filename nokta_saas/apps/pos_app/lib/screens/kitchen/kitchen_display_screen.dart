import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:core/models/order.dart';
import 'package:core/services/order_service.dart';

class KitchenDisplayScreen extends ConsumerStatefulWidget {
  const KitchenDisplayScreen({super.key});

  @override
  ConsumerState<KitchenDisplayScreen> createState() => _KitchenDisplayScreenState();
}

class _KitchenDisplayScreenState extends ConsumerState<KitchenDisplayScreen> {
  @override
  Widget build(BuildContext context) {
    final pendingOrdersAsync = ref.watch(ordersProvider(const OrdersQuery(status: OrderStatus.pending)));
    final preparingOrdersAsync = ref.watch(ordersProvider(const OrdersQuery(status: OrderStatus.preparing)));

    return Scaffold(
      appBar: AppBar(
        title: const Text('شاشة المطبخ'),
        backgroundColor: Colors.orange,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () {
              ref.refresh(ordersProvider(const OrdersQuery(status: OrderStatus.pending)));
              ref.refresh(ordersProvider(const OrdersQuery(status: OrderStatus.preparing)));
            },
          ),
        ],
      ),
      body: Row(
        children: [
          // Pending Orders
          Expanded(
            child: Column(
              children: [
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(16),
                  color: Colors.red.shade100,
                  child: const Text(
                    'طلبات في الانتظار',
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: Colors.red,
                    ),
                    textAlign: TextAlign.center,
                  ),
                ),
                Expanded(
                  child: pendingOrdersAsync.when(
                    data: (orders) => _buildOrdersList(orders, OrderStatus.pending),
                    loading: () => const Center(child: CircularProgressIndicator()),
                    error: (error, stack) => Center(child: Text('خطأ: $error')),
                  ),
                ),
              ],
            ),
          ),
          
          const VerticalDivider(width: 1),
          
          // Preparing Orders
          Expanded(
            child: Column(
              children: [
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(16),
                  color: Colors.orange.shade100,
                  child: const Text(
                    'قيد التحضير',
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: Colors.orange,
                    ),
                    textAlign: TextAlign.center,
                  ),
                ),
                Expanded(
                  child: preparingOrdersAsync.when(
                    data: (orders) => _buildOrdersList(orders, OrderStatus.preparing),
                    loading: () => const Center(child: CircularProgressIndicator()),
                    error: (error, stack) => Center(child: Text('خطأ: $error')),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildOrdersList(List<Order> orders, OrderStatus currentStatus) {
    if (orders.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              currentStatus == OrderStatus.pending 
                  ? Icons.pending_actions 
                  : Icons.restaurant_menu,
              size: 64,
              color: Colors.grey,
            ),
            const SizedBox(height: 16),
            Text(
              currentStatus == OrderStatus.pending 
                  ? 'لا توجد طلبات في الانتظار'
                  : 'لا توجد طلبات قيد التحضير',
              style: const TextStyle(fontSize: 18, color: Colors.grey),
            ),
          ],
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(8),
      itemCount: orders.length,
      itemBuilder: (context, index) {
        final order = orders[index];
        return _buildOrderCard(order, currentStatus);
      },
    );
  }

  Widget _buildOrderCard(Order order, OrderStatus currentStatus) {
    final duration = DateTime.now().difference(order.createdAt);
    final isUrgent = duration.inMinutes > 15;

    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      color: isUrgent ? Colors.red.shade50 : null,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: _getOrderTypeColor(order.orderType),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    _getOrderTypeText(order.orderType),
                    style: const TextStyle(
                      color: Colors.white,
                      fontWeight: FontWeight.bold,
                      fontSize: 12,
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                if (order.tableNumber != null)
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: Colors.blue,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(
                      'طاولة ${order.tableNumber}',
                      style: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.bold,
                        fontSize: 12,
                      ),
                    ),
                  ),
                const Spacer(),
                Text(
                  '${duration.inMinutes} د',
                  style: TextStyle(
                    color: isUrgent ? Colors.red : Colors.grey,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            
            // Order Items
            ...order.items.map((item) => Padding(
              padding: const EdgeInsets.only(bottom: 4),
              child: Row(
                children: [
                  Container(
                    width: 24,
                    height: 24,
                    decoration: BoxDecoration(
                      color: Theme.of(context).primaryColor,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Center(
                      child: Text(
                        '${item.quantity}',
                        style: const TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.bold,
                          fontSize: 12,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      item.productName,
                      style: const TextStyle(fontSize: 16),
                    ),
                  ),
                ],
              ),
            )),
            
            if (order.specialInstructions?.isNotEmpty == true) ...[
              const SizedBox(height: 8),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: Colors.amber.shade100,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  'ملاحظات: ${order.specialInstructions}',
                  style: TextStyle(
                    color: Colors.amber.shade800,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
            ],

            const SizedBox(height: 12),
            
            // Action Buttons
            Row(
              children: [
                if (currentStatus == OrderStatus.pending) ...[
                  Expanded(
                    child: ElevatedButton(
                      onPressed: () => _updateOrderStatus(order, OrderStatus.preparing),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.orange,
                        foregroundColor: Colors.white,
                      ),
                      child: const Text('بدء التحضير'),
                    ),
                  ),
                ] else if (currentStatus == OrderStatus.preparing) ...[
                  Expanded(
                    child: ElevatedButton(
                      onPressed: () => _updateOrderStatus(order, OrderStatus.ready),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.green,
                        foregroundColor: Colors.white,
                      ),
                      child: const Text('جاهز للتقديم'),
                    ),
                  ),
                ],
              ],
            ),
          ],
        ),
      ),
    );
  }

  Color _getOrderTypeColor(OrderType orderType) {
    switch (orderType) {
      case OrderType.dineIn:
        return Colors.blue;
      case OrderType.takeaway:
        return Colors.purple;
      case OrderType.delivery:
        return Colors.green;
      case OrderType.online:
        return Colors.orange;
    }
  }

  String _getOrderTypeText(OrderType orderType) {
    switch (orderType) {
      case OrderType.dineIn:
        return 'محلي';
      case OrderType.takeaway:
        return 'خارجي';
      case OrderType.delivery:
        return 'توصيل';
      case OrderType.online:
        return 'أونلاين';
    }
  }

  void _updateOrderStatus(Order order, OrderStatus newStatus) async {
    try {
      final orderService = ref.read(orderServiceProvider);
      await orderService.updateOrderStatus(order.id, newStatus);
      
      // Refresh the orders
      ref.refresh(ordersProvider(const OrdersQuery(status: OrderStatus.pending)));
      ref.refresh(ordersProvider(const OrdersQuery(status: OrderStatus.preparing)));
      
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('تم تحديث حالة الطلب')),
      );
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('خطأ في تحديث الطلب: $e')),
      );
    }
  }
}