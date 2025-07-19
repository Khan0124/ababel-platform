// apps/pos_app/lib/screens/kitchen/kitchen_display_screen.dart
class KitchenDisplayScreen extends ConsumerStatefulWidget {
  @override
  _KitchenDisplayScreenState createState() => _KitchenDisplayScreenState();
}

class _KitchenDisplayScreenState extends ConsumerState<KitchenDisplayScreen> {
  @override
  Widget build(BuildContext context) {
    final orders = ref.watch(kitchenOrdersProvider);
    
    return Scaffold(
      appBar: AppBar(
        title: const Text('Kitchen Display'),
        actions: [
          // Order stats
          Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                _buildStat('Pending', orders.pending.length, Colors.orange),
                const SizedBox(width: 24),
                _buildStat('Preparing', orders.preparing.length, Colors.blue),
                const SizedBox(width: 24),
                _buildStat('Ready', orders.ready.length, Colors.green),
              ],
            ),
          ),
        ],
      ),
      body: Row(
        children: [
          // Pending Orders
          Expanded(
            child: _buildOrderColumn(
              title: 'Pending',
              orders: orders.pending,
              color: Colors.orange,
              onOrderTap: (order) => _startPreparing(order),
              actionLabel: 'Start',
            ),
          ),
          // Preparing Orders
          Expanded(
            child: _buildOrderColumn(
              title: 'Preparing',
              orders: orders.preparing,
              color: Colors.blue,
              onOrderTap: (order) => _markReady(order),
              actionLabel: 'Ready',
              showTimer: true,
            ),
          ),
          // Ready Orders
          Expanded(
            child: _buildOrderColumn(
              title: 'Ready',
              orders: orders.ready,
              color: Colors.green,
              onOrderTap: (order) => _markCompleted(order),
              actionLabel: 'Complete',
            ),
          ),
        ],
      ),
    );
  }
  
  Widget _buildOrderColumn({
    required String title,
    required List<KitchenOrder> orders,
    required Color color,
    required Function(KitchenOrder) onOrderTap,
    required String actionLabel,
    bool showTimer = false,
  }) {
    return Container(
      decoration: BoxDecoration(
        border: Border(
          right: BorderSide(color: Colors.grey.shade300),
        ),
      ),
      child: Column(
        children: [
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(16),
            color: color.withOpacity(0.1),
            child: Text(
              title,
              style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                color: color,
                fontWeight: FontWeight.bold,
              ),
              textAlign: TextAlign.center,
            ),
          ),
          Expanded(
            child: ListView.builder(
              padding: const EdgeInsets.all(8),
              itemCount: orders.length,
              itemBuilder: (context, index) {
                final order = orders[index];
                return Card(
                  margin: const EdgeInsets.only(bottom: 8),
                  child: InkWell(
                    onTap: () => onOrderTap(order),
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(
                                '#${order.orderNumber}',
                                style: Theme.of(context).textTheme.titleLarge,
                              ),
                              if (showTimer)
                                OrderTimer(
                                  startTime: order.preparingStartedAt!,
                                  estimatedMinutes: order.estimatedPrepTime,
                                ),
                            ],
                          ),
                          const SizedBox(height: 8),
                          Text(
                            order.type.displayName,
                            style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                              color: order.type.color,
                            ),
                          ),
                          if (order.table != null)
                            Text('Table ${order.table!.number}'),
                          const Divider(),
                          ...order.items.map((item) => Padding(
                            padding: const EdgeInsets.symmetric(vertical: 4),
                            child: Row(
                              children: [
                                CircleAvatar(
                                  radius: 16,
                                  backgroundColor: color,
                                  child: Text(
                                    '${item.quantity}',
                                    style: const TextStyle(color: Colors.white),
                                  ),
                                ),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        item.productName,
                                        style: const TextStyle(
                                          fontWeight: FontWeight.w500,
                                        ),
                                      ),
                                      if (item.modifiers.isNotEmpty)
                                        Text(
                                          item.modifiers.join(', '),
                                          style: Theme.of(context)
                                              .textTheme
                                              .bodySmall,
                                        ),
                                      if (item.notes != null)
                                        Text(
                                          'Note: ${item.notes}',
                                          style: Theme.of(context)
                                              .textTheme
                                              .bodySmall
                                              ?.copyWith(
                                                color: Colors.red,
                                                fontStyle: FontStyle.italic,
                                              ),
                                        ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          )),
                          const SizedBox(height: 8),
                          SizedBox(
                            width: double.infinity,
                            child: ElevatedButton(
                              onPressed: () => onOrderTap(order),
                              style: ElevatedButton.styleFrom(
                                backgroundColor: color,
                              ),
                              child: Text(actionLabel),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}