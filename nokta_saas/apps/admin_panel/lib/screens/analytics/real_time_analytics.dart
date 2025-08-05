// apps/admin_panel/lib/screens/analytics/real_time_analytics.dart
class RealTimeAnalytics extends ConsumerStatefulWidget {
  @override
  _RealTimeAnalyticsState createState() => _RealTimeAnalyticsState();
}

class _RealTimeAnalyticsState extends ConsumerState<RealTimeAnalytics> {
  Timer? _refreshTimer;
  
  @override
  void initState() {
    super.initState();
    // Refresh every 30 seconds
    _refreshTimer = Timer.periodic(
      const Duration(seconds: 30),
      (_) => ref.invalidate(realTimeStatsProvider),
    );
  }
  
  @override
  Widget build(BuildContext context) {
    final stats = ref.watch(realTimeStatsProvider);
    
    return stats.when(
      data: (data) => SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Real-time KPIs
            Row(
              children: [
                Expanded(
                  child: LiveMetricCard(
                    title: 'Active Orders',
                    value: data.activeOrders.toString(),
                    sparklineData: data.ordersTrend,
                    color: Colors.orange,
                    icon: Icons.restaurant_menu,
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: LiveMetricCard(
                    title: 'Online Drivers',
                    value: data.onlineDrivers.toString(),
                    subtitle: '${data.availableDrivers} available',
                    color: Colors.green,
                    icon: Icons.delivery_dining,
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: LiveMetricCard(
                    title: 'Avg. Delivery Time',
                    value: '${data.avgDeliveryTime} min',
                    trend: data.deliveryTimeTrend,
                    color: Colors.blue,
                    icon: Icons.timer,
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: LiveMetricCard(
                    title: 'Revenue Today',
                    value: formatCurrency(data.todayRevenue),
                    progress: data.revenueProgress,
                    target: data.revenueTarget,
                    color: Colors.purple,
                    icon: Icons.attach_money,
                  ),
                ),
              ],
            ),
            
            const SizedBox(height: 32),
            
            // Live Order Map
            Card(
              child: Padding(
                padding: const EdgeInsets.all(24),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          'Live Order Map',
                          style: Theme.of(context).textTheme.titleLarge,
                        ),
                        SegmentedButton<MapViewType>(
                          segments: const [
                            ButtonSegment(
                              value: MapViewType.orders,
                              label: Text('Orders'),
                            ),
                            ButtonSegment(
                              value: MapViewType.drivers,
                              label: Text('Drivers'),
                            ),
                            ButtonSegment(
                              value: MapViewType.heatmap,
                              label: Text('Heatmap'),
                            ),
                          ],
                          selected: {ref.watch(mapViewTypeProvider)},
                          onSelectionChanged: (types) => ref
                              .read(mapViewTypeProvider.notifier)
                              .state = types.first,
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),
                    SizedBox(
                      height: 400,
                      child: LiveOrderMap(
                        orders: data.liveOrders,
                        drivers: data.drivers,
                        viewType: ref.watch(mapViewTypeProvider),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            
            const SizedBox(height: 32),
            
            // Performance Metrics
            Row(
              children: [
                Expanded(
                  flex: 2,
                  child: Card(
                    child: Padding(
                      padding: const EdgeInsets.all(24),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Order Volume Timeline',
                            style: Theme.of(context).textTheme.titleLarge,
                          ),
                          const SizedBox(height: 16),
                          SizedBox(
                            height: 300,
                            child: OrderVolumeChart(
                              data: data.orderVolumeByHour,
                              showComparison: true,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Card(
                    child: Padding(
                      padding: const EdgeInsets.all(24),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Restaurant Performance',
                            style: Theme.of(context).textTheme.titleLarge,
                          ),
                          const SizedBox(height: 16),
                          SizedBox(
                            height: 300,
                            child: RestaurantPerformanceList(
                              restaurants: data.topPerformingRestaurants,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
      loading: () => const ShimmerAnalytics(),
      error: (error, stack) => ErrorWidget(error),
    );
  }
  
  @override
  void dispose() {
    _refreshTimer?.cancel();
    super.dispose();
  }
}