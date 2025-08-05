// apps/customer_app/lib/screens/order/order_tracking_screen.dart
class OrderTrackingScreen extends ConsumerStatefulWidget {
  final int orderId;

  @override
  _OrderTrackingScreenState createState() => _OrderTrackingScreenState();
}

class _OrderTrackingScreenState extends ConsumerState<OrderTrackingScreen> {
  StreamSubscription? _orderSubscription;
  StreamSubscription? _driverLocationSubscription;

  @override
  void initState() {
    super.initState();
    _subscribeToOrderUpdates();
  }

  void _subscribeToOrderUpdates() {
    _orderSubscription = ref
        .read(realtimeServiceProvider)
        .subscribeToOrder(widget.orderId)
        .listen((update) {
          ref.read(orderProvider(widget.orderId).notifier).updateOrder(update);

          if (update.driverId != null) {
            _subscribeToDriverLocation(update.driverId!);
          }
        });
  }

  void _subscribeToDriverLocation(int driverId) {
    _driverLocationSubscription?.cancel();
    _driverLocationSubscription = ref
        .read(realtimeServiceProvider)
        .subscribeToDriverLocation(driverId)
        .listen((location) {
          ref.read(driverLocationProvider.notifier).updateLocation(location);
        });
  }

  @override
  Widget build(BuildContext context) {
    final order = ref.watch(orderProvider(widget.orderId));
    final driverLocation = ref.watch(driverLocationProvider);

    return order.when(
      data: (order) => Scaffold(
        appBar: AppBar(
          title: Text('Order #${order.orderNumber}'),
          actions: [
            IconButton(
              icon: const Icon(Icons.help),
              onPressed: _showSupportDialog,
            ),
          ],
        ),
        body: Column(
          children: [
            // Order Status Timeline
            OrderStatusTimeline(
              currentStatus: order.status,
              statusHistory: order.statusHistory,
            ),

            // Map (for delivery orders)
            if (order.type == OrderType.delivery)
              Expanded(
                child: OrderTrackingMap(
                  order: order,
                  driverLocation: driverLocation,
                ),
              ),

            // Order Details
            OrderDetailsCard(order: order),

            // Driver Info (if assigned)
            if (order.driver != null)
              DriverInfoCard(
                driver: order.driver!,
                onCall: () => _callDriver(order.driver!.phone),
                onChat: () => _openChat(order.id),
              ),
          ],
        ),
      ),
      loading: () => const LoadingScreen(),
      error: (error, stack) => ErrorScreen(error: error),
    );
  }

  @override
  void dispose() {
    _orderSubscription?.cancel();
    _driverLocationSubscription?.cancel();
    super.dispose();
  }
}
