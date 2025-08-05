// apps/customer_app/lib/screens/checkout/checkout_screen.dart
class CheckoutScreen extends ConsumerStatefulWidget {
  @override
  _CheckoutScreenState createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends ConsumerState<CheckoutScreen> {
  OrderType _orderType = OrderType.delivery;
  PaymentMethod _paymentMethod = PaymentMethod.cash;
  CustomerAddress? _selectedAddress;
  DateTime? _scheduledTime;
  final _notesController = TextEditingController();
  
  @override
  Widget build(BuildContext context) {
    final cart = ref.watch(cartProvider);
    final addresses = ref.watch(customerAddressesProvider);
    
    return Scaffold(
      appBar: AppBar(title: const Text('Checkout')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Order Type Selection
            OrderTypeSelector(
              selectedType: _orderType,
              availableTypes: cart.restaurant!.availableOrderTypes,
              onChanged: (type) => setState(() => _orderType = type),
            ),
            
            // Delivery Address (if delivery)
            if (_orderType == OrderType.delivery) ...[
              const SizedBox(height: 24),
              AddressSelector(
                addresses: addresses,
                selectedAddress: _selectedAddress,
                onSelected: (address) => setState(() => _selectedAddress = address),
                onAddNew: () => _showAddAddressDialog(),
              ),
            ],
            
            // Schedule Time
            const SizedBox(height: 24),
            ScheduleTimeSelector(
              selectedTime: _scheduledTime,
              onChanged: (time) => setState(() => _scheduledTime = time),
            ),
            
            // Order Summary
            const SizedBox(height: 24),
            OrderSummaryCard(cart: cart),
            
            // Payment Method
            const SizedBox(height: 24),
            PaymentMethodSelector(
              selectedMethod: _paymentMethod,
              availableMethods: _getAvailablePaymentMethods(),
              onChanged: (method) => setState(() => _paymentMethod = method),
            ),
            
            // Notes
            const SizedBox(height: 24),
            TextField(
              controller: _notesController,
              decoration: const InputDecoration(
                labelText: 'Notes',
                hintText: 'Any special instructions?',
                border: OutlineInputBorder(),
              ),
              maxLines: 3,
            ),
          ],
        ),
      ),
      bottomNavigationBar: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: ElevatedButton(
            onPressed: _canPlaceOrder() ? _placeOrder : null,
            style: ElevatedButton.styleFrom(
              minimumSize: const Size(double.infinity, 56),
            ),
            child: Text('Place Order - ${formatCurrency(cart.total)}'),
          ),
        ),
      ),
    );
  }
  
  bool _canPlaceOrder() {
    if (_orderType == OrderType.delivery && _selectedAddress == null) {
      return false;
    }
    return true;
  }
  
  Future<void> _placeOrder() async {
    final cart = ref.read(cartProvider);
    final customer = ref.read(customerProvider);
    
    final order = CreateOrderRequest(
      restaurantId: cart.restaurant!.id,
      branchId: cart.restaurant!.nearestBranch.id,
      customerId: customer?.id,
      type: _orderType,
      paymentMethod: _paymentMethod,
      deliveryAddressId: _selectedAddress?.id,
      scheduledTime: _scheduledTime,
      notes: _notesController.text,
      items: cart.items.map((item) => OrderItemRequest(
        productId: item.product.id,
        quantity: item.quantity,
        modifiers: item.modifiers,
      )).toList(),
      couponCode: cart.appliedCoupon?.code,
    );
    
    try {
      final result = await ref.read(orderServiceProvider).createOrder(order);
      
      if (_paymentMethod != PaymentMethod.cash) {
        // Process payment
        final paymentResult = await _processPayment(result.order);
        if (!paymentResult.success) {
          // Handle payment failure
          return;
        }
      }
      
      // Clear cart
      ref.read(cartProvider.notifier).clear();
      
      // Navigate to order tracking
      context.goNamed('order-tracking', params: {'id': result.order.id.toString()});
      
    } catch (e) {
      showErrorSnackbar(context, e.toString());
    }
  }
}