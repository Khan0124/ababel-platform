// apps/pos_app/lib/screens/pos/enhanced_pos_screen.dart
class EnhancedPOSScreen extends ConsumerStatefulWidget {
  @override
  _EnhancedPOSScreenState createState() => _EnhancedPOSScreenState();
}

class _EnhancedPOSScreenState extends ConsumerState<EnhancedPOSScreen> {
  final List<CartItem> _cart = [];
  Table? _selectedTable;
  Customer? _customer;
  OrderType _orderType = OrderType.dineIn;
  
  @override
  Widget build(BuildContext context) {
    final branch = ref.watch(currentBranchProvider);
    final categories = ref.watch(categoriesProvider);
    
    return Scaffold(
      body: Row(
        children: [
          // Left Panel - Categories & Products
          Expanded(
            flex: 3,
            child: Column(
              children: [
                // Category Tabs
                CategoryTabBar(
                  categories: categories,
                  onCategorySelected: (category) => 
                      ref.read(selectedCategoryProvider.notifier).state = category,
                ),
                // Product Grid
                Expanded(
                  child: ProductGrid(
                    onProductTap: _addToCart,
                  ),
                ),
              ],
            ),
          ),
          
          // Right Panel - Cart & Actions
          Expanded(
            flex: 2,
            child: Container(
              color: Theme.of(context).cardColor,
              child: Column(
                children: [
                  // Order Type & Table Selection
                  Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      children: [
                        SegmentedButton<OrderType>(
                          segments: const [
                            ButtonSegment(
                              value: OrderType.dineIn,
                              label: Text('Dine In'),
                              icon: Icon(Icons.restaurant),
                            ),
                            ButtonSegment(
                              value: OrderType.takeaway,
                              label: Text('Takeaway'),
                              icon: Icon(Icons.shopping_bag),
                            ),
                            ButtonSegment(
                              value: OrderType.delivery,
                              label: Text('Delivery'),
                              icon: Icon(Icons.delivery_dining),
                            ),
                          ],
                          selected: {_orderType},
                          onSelectionChanged: (types) {
                            setState(() => _orderType = types.first);
                          },
                        ),
                        if (_orderType == OrderType.dineIn) ...[
                          const SizedBox(height: 16),
                          TableSelector(
                            selectedTable: _selectedTable,
                            onTableSelected: (table) => 
                                setState(() => _selectedTable = table),
                          ),
                        ],
                        if (_orderType == OrderType.delivery) ...[
                          const SizedBox(height: 16),
                          CustomerSearchField(
                            onCustomerSelected: (customer) =>
                                setState(() => _customer = customer),
                          ),
                        ],
                      ],
                    ),
                  ),
                  
                  // Cart Items
                  Expanded(
                    child: CartItemsList(
                      items: _cart,
                      onQuantityChanged: _updateQuantity,
                      onItemRemoved: _removeItem,
                      onModifierAdded: _showModifierDialog,
                    ),
                  ),
                  
                  // Cart Summary
                  CartSummary(
                    subtotal: _calculateSubtotal(),
                    tax: _calculateTax(),
                    discount: _appliedDiscount,
                    total: _calculateTotal(),
                  ),
                  
                  // Action Buttons
                  Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      children: [
                        Row(
                          children: [
                            Expanded(
                              child: OutlinedButton.icon(
                                onPressed: _showDiscountDialog,
                                icon: const Icon(Icons.percent),
                                label: const Text('Discount'),
                              ),
                            ),
                            const SizedBox(width: 8),
                            Expanded(
                              child: OutlinedButton.icon(
                                onPressed: _splitBill,
                                icon: const Icon(Icons.call_split),
                                label: const Text('Split'),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 8),
                        Row(
                          children: [
                            Expanded(
                              child: OutlinedButton.icon(
                                onPressed: _holdOrder,
                                icon: const Icon(Icons.pause),
                                label: const Text('Hold'),
                              ),
                            ),
                            const SizedBox(width: 8),
                            Expanded(
                              child: OutlinedButton.icon(
                                onPressed: _printKitchen,
                                icon: const Icon(Icons.print),
                                label: const Text('Kitchen'),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 16),
                        SizedBox(
                          width: double.infinity,
                          height: 56,
                          child: ElevatedButton.icon(
                            onPressed: _cart.isEmpty ? null : _processPayment,
                            icon: const Icon(Icons.payment),
                            label: Text('Pay ${formatCurrency(_calculateTotal())}'),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
  
  void _showModifierDialog(CartItem item) {
    showDialog(
      context: context,
      builder: (context) => ModifierSelectionDialog(
        product: item.product,
        onConfirm: (modifiers) {
          setState(() {
            final index = _cart.indexOf(item);
            _cart[index] = item.copyWith(modifiers: modifiers);
          });
        },
      ),
    );
  }
  
  Future<void> _processPayment() async {
    final result = await showDialog<PaymentResult>(
      context: context,
      barrierDismissible: false,
      builder: (context) => PaymentDialog(
        amount: _calculateTotal(),
        onPaymentComplete: (method, reference) async {
          // Create order
          final order = await _createOrder(method, reference);
          return PaymentResult(success: true, orderId: order.id);
        },
      ),
    );
    
    if (result?.success ?? false) {
      // Print receipt
      await _printReceipt(result!.orderId);
      
      // Clear cart
      setState(() {
        _cart.clear();
        _selectedTable = null;
        _customer = null;
      });
      
      // Show success
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Order completed successfully')),
      );
    }
  }
}