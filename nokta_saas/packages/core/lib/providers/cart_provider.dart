import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../models/product.dart';
import '../models/cart_item.dart';
import '../models/order.dart';
import '../services/order_service.dart';
import '../db/local_db.dart';

// Cart State
class CartState {
  final List<CartItem> items;
  final double taxRate;
  final double deliveryFee;
  final double discount;
  final String? couponCode;

  CartState({
    this.items = const [],
    this.taxRate = 0.15, // 15% VAT
    this.deliveryFee = 0.0,
    this.discount = 0.0,
    this.couponCode,
  });

  double get subtotal => items.fold(0, (sum, item) => sum + item.totalPrice);
  double get tax => subtotal * taxRate;
  double get total => subtotal + tax + deliveryFee - discount;
  int get itemCount => items.fold(0, (sum, item) => sum + item.quantity);

  CartState copyWith({
    List<CartItem>? items,
    double? taxRate,
    double? deliveryFee,
    double? discount,
    String? couponCode,
  }) {
    return CartState(
      items: items ?? this.items,
      taxRate: taxRate ?? this.taxRate,
      deliveryFee: deliveryFee ?? this.deliveryFee,
      discount: discount ?? this.discount,
      couponCode: couponCode ?? this.couponCode,
    );
  }
}

// Cart Notifier
class CartNotifier extends StateNotifier<CartState> {
  final Ref ref;

  CartNotifier(this.ref) : super(CartState());

  void addItem(Product product, {int quantity = 1}) {
    final existingIndex = state.items.indexWhere(
      (item) => item.product.id == product.id,
    );

    if (existingIndex >= 0) {
      // Update quantity if item exists
      final updatedItems = [...state.items];
      final existingItem = updatedItems[existingIndex];
      updatedItems[existingIndex] = existingItem.copyWith(
        quantity: existingItem.quantity + quantity,
      );
      state = state.copyWith(items: updatedItems);
    } else {
      // Add new item
      final newItem = CartItem(
        product: product,
        quantity: quantity,
        unitPrice: product.price,
      );
      state = state.copyWith(items: [...state.items, newItem]);
    }
  }

  void removeItem(int productId) {
    state = state.copyWith(
      items: state.items.where((item) => item.product.id != productId).toList(),
    );
  }

  void updateQuantity(int productId, int quantity) {
    if (quantity <= 0) {
      removeItem(productId);
      return;
    }

    final updatedItems = state.items.map((item) {
      if (item.product.id == productId) {
        return item.copyWith(quantity: quantity);
      }
      return item;
    }).toList();

    state = state.copyWith(items: updatedItems);
  }

  void updateItemModifiers(int productId, List<CartItemModifier> modifiers) {
    final updatedItems = state.items.map((item) {
      if (item.product.id == productId) {
        return item.copyWith(modifiers: modifiers);
      }
      return item;
    }).toList();

    state = state.copyWith(items: updatedItems);
  }

  void addItemNote(int productId, String note) {
    final updatedItems = state.items.map((item) {
      if (item.product.id == productId) {
        return item.copyWith(notes: note);
      }
      return item;
    }).toList();

    state = state.copyWith(items: updatedItems);
  }

  void applyCoupon(String code) {
    // Validate coupon and calculate discount
    // This should call an API to validate the coupon
    double discountAmount = 0;
    
    // Example coupon logic
    switch (code.toUpperCase()) {
      case 'WELCOME10':
        discountAmount = state.subtotal * 0.10; // 10% discount
        break;
      case 'SAVE20':
        discountAmount = state.subtotal * 0.20; // 20% discount
        break;
      case 'FREESHIP':
        discountAmount = state.deliveryFee; // Free shipping
        break;
      default:
        discountAmount = 0;
    }

    state = state.copyWith(
      discount: discountAmount,
      couponCode: code,
    );
  }

  void removeCoupon() {
    state = state.copyWith(
      discount: 0,
      couponCode: null,
    );
  }

  void setDeliveryFee(double fee) {
    state = state.copyWith(deliveryFee: fee);
  }

  void clear() {
    state = CartState();
  }

  Future<Order?> checkout({
    required OrderType orderType,
    required PaymentMethod paymentMethod,
    String? customerName,
    String? customerPhone,
    String? customerEmail,
    String? customerAddress,
    int? tableNumber,
    String? specialInstructions,
    DateTime? scheduledFor,
  }) async {
    if (state.items.isEmpty) return null;

    try {
      final order = Order(
        id: 0, // Will be assigned by database
        tenantId: 1, // Get from auth
        branchId: 1, // Get from settings
        orderType: orderType,
        status: OrderStatus.pending,
        items: state.items.map((item) => OrderItem.fromCartItem(item)).toList(),
        subtotal: state.subtotal,
        tax: state.tax,
        discount: state.discount,
        deliveryFee: state.deliveryFee,
        total: state.total,
        paymentMethod: paymentMethod,
        paymentStatus: 'pending',
        customerName: customerName,
        customerPhone: customerPhone,
        customerEmail: customerEmail,
        customerAddress: customerAddress,
        tableNumber: tableNumber,
        specialInstructions: specialInstructions,
        scheduledFor: scheduledFor,
        createdAt: DateTime.now(),
        updatedAt: DateTime.now(),
      );

      // Save order to local database
      final orderId = await LocalDB.insertOrder(order);
      
      // Try to sync with server
      final orderService = ref.read(orderServiceProvider);
      await orderService.syncOrder(orderId);

      // Clear cart after successful checkout
      clear();

      return order.copyWith(id: orderId);
    } catch (e) {
      print('Checkout error: $e');
      return null;
    }
  }

  // Save cart to local storage for persistence
  Future<void> saveCart() async {
    // Implement cart persistence logic
  }

  // Load cart from local storage
  Future<void> loadCart() async {
    // Implement cart loading logic
  }
}

// Providers
final cartProvider = StateNotifierProvider<CartNotifier, CartState>((ref) {
  return CartNotifier(ref);
});

final cartItemsProvider = Provider<List<CartItem>>((ref) {
  return ref.watch(cartProvider).items;
});

final cartItemCountProvider = Provider<int>((ref) {
  return ref.watch(cartProvider).itemCount;
});

final cartTotalsProvider = Provider<CartTotals>((ref) {
  final cart = ref.watch(cartProvider);
  return CartTotals(
    subtotal: cart.subtotal,
    tax: cart.tax,
    discount: cart.discount,
    deliveryFee: cart.deliveryFee,
    total: cart.total,
  );
});

// Cart Totals Model
class CartTotals {
  final double subtotal;
  final double tax;
  final double discount;
  final double deliveryFee;
  final double total;

  CartTotals({
    required this.subtotal,
    required this.tax,
    required this.discount,
    required this.deliveryFee,
    required this.total,
  });
}
