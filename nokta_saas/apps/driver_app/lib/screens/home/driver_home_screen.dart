// apps/driver_app/lib/screens/home/driver_home_screen.dart
class DriverHomeScreen extends ConsumerStatefulWidget {
  @override
  _DriverHomeScreenState createState() => _DriverHomeScreenState();
}

class _DriverHomeScreenState extends ConsumerState<DriverHomeScreen> {
  bool _isOnline = false;
  StreamSubscription? _locationSubscription;
  StreamSubscription? _orderSubscription;
  
  @override
  void initState() {
    super.initState();
    _requestLocationPermission();
  }
  
  Future<void> _requestLocationPermission() async {
    final permission = await Geolocator.requestPermission();
    if (permission == LocationPermission.denied) {
      // Show permission dialog
      return;
    }
    
    // Start location tracking
    _startLocationTracking();
  }
  
  void _startLocationTracking() {
    _locationSubscription = Geolocator.getPositionStream(
      locationSettings: const LocationSettings(
        accuracy: LocationAccuracy.high,
        distanceFilter: 10, // Update every 10 meters
      ),
    ).listen((position) {
      if (_isOnline) {
        ref.read(driverServiceProvider).updateLocation(
          lat: position.latitude,
          lng: position.longitude,
        );
      }
    });
  }
  
  void _toggleOnlineStatus() {
    setState(() => _isOnline = !_isOnline);
    ref.read(driverServiceProvider).setOnlineStatus(_isOnline);
    
    if (_isOnline) {
      _subscribeToOrders();
    } else {
      _orderSubscription?.cancel();
    }
  }
  
  void _subscribeToOrders() {
    _orderSubscription = ref.read(realtimeServiceProvider)
        .subscribeToDriverOrders()
        .listen((order) {
      _showNewOrderDialog(order);
    });
  }
  
  @override
  Widget build(BuildContext context) {
    final earnings = ref.watch(todayEarningsProvider);
    final activeOrder = ref.watch(activeOrderProvider);
    
    return Scaffold(
      appBar: AppBar(
        title: const Text('Nokta Driver'),
        actions: [
          Switch(
            value: _isOnline,
            onChanged: (_) => _toggleOnlineStatus(),
          ),
          const SizedBox(width: 16),
        ],
      ),
      body: Column(
        children: [
          // Earnings Summary
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(24),
            color: Theme.of(context).primaryColor.withOpacity(0.1),
            child: Column(
              children: [
                Text(
                  'Today\'s Earnings',
                  style: Theme.of(context).textTheme.titleMedium,
                ),
                const SizedBox(height: 8),
                Text(
                  formatCurrency(earnings.total),
                  style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 16),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceAround,
                  children: [
                    _buildStat('Trips', earnings.tripCount.toString()),
                    _buildStat('Online', formatDuration(earnings.onlineTime)),
                    _buildStat('Distance', '${earnings.distance.toStringAsFixed(1)} km'),
                  ],
                ),
              ],
            ),
          ),
          
          // Active Order or Waiting
          Expanded(
            child: activeOrder != null
                ? ActiveOrderView(
                    order: activeOrder,
                    onStatusUpdate: _updateOrderStatus,
                  )
                : const WaitingForOrderView(),
          ),
        ],
      ),
      drawer: const DriverDrawer(),
    );
  }
  
  void _showNewOrderDialog(DeliveryOrder order) {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => NewOrderDialog(
        order: order,
        onAccept: () {
          ref.read(driverServiceProvider).acceptOrder(order.id);
          Navigator.pop(context);
        },
        onReject: () {
          ref.read(driverServiceProvider).rejectOrder(order.id);
          Navigator.pop(context);
        },
      ),
    );
  }
}