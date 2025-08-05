import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../models/product.dart';
import '../models/cart_item.dart';

class CartNotifier extends StateNotifier<List<CartItem>> {
  CartNotifier() : super([]);

  // Add item to cart
  void addItem(Product product, {int quantity = 1}) {
    final existingIndex = state.indexWhere(
      (item) => item.product.id == product.id,
    );

    if (existingIndex >= 0) {
      // Update existing item quantity
      final existingItem = state[existingIndex];
      final newQuantity = existingItem.quantity + quantity;
      final updatedItem = existingItem.copyWith(
        quantity: newQuantity,
        totalPrice: product.price * newQuantity,
      );

      state = [
        ...state.sublist(0, existingIndex),
        updatedItem,
        ...state.sublist(existingIndex + 1),
      ];
    } else {
      // Add new item
      final newItem = CartItem.fromProduct(product, quantity: quantity);
      state = [...state, newItem];
    }
  }

  // Remove item from cart
  void removeItem(int productId) {
    state = state.where((item) => item.product.id != productId).toList();
  }

  // Update item quantity
  void updateQuantity(int productId, int quantity) {
    if (quantity <= 0) {
      removeItem(productId);
      return;
    }

    final index = state.indexWhere((item) => item.product.id == productId);
    if (index >= 0) {
      final item = state[index];
      final updatedItem = item.copyWith(
        quantity: quantity,
        totalPrice: item.product.price * quantity,
      );

      state = [
        ...state.sublist(0, index),
        updatedItem,
        ...state.sublist(index + 1),
      ];
    }
  }

  // Update item notes
  void updateNotes(int productId, String notes) {
    final index = state.indexWhere((item) => item.product.id == productId);
    if (index >= 0) {
      final item = state[index];
      final updatedItem = item.copyWith(notes: notes);

      state = [
        ...state.sublist(0, index),
        updatedItem,
        ...state.sublist(index + 1),
      ];
    }
  }

  // Clear cart
  void clear() {
    state = [];
  }

  // Get cart totals
  CartTotals get totals {
    final subtotal = state.fold<double>(
      0,
      (sum, item) => sum + item.totalPrice,
    );
    final tax = subtotal * 0.15; // 15% tax
    final total = subtotal + tax;

    return CartTotals(
      subtotal: subtotal,
      tax: tax,
      total: total,
      itemCount: state.fold<int>(0, (sum, item) => sum + item.quantity),
    );
  }

  // Check if product is in cart
  bool isInCart(int productId) =>
      state.any((item) => item.product.id == productId);

  // Get item quantity in cart
  int getQuantity(int productId) {
    final item = state
        .where((item) => item.product.id == productId)
        .firstOrNull;
    return item?.quantity ?? 0;
  }
}

// Cart totals model
class CartTotals {
  CartTotals({
    required this.subtotal,
    required this.tax,
    required this.total,
    required this.itemCount,
  });
  final double subtotal;
  final double tax;
  final double total;
  final int itemCount;
}

// Providers
final cartProvider = StateNotifierProvider<CartNotifier, List<CartItem>>(
  (ref) => CartNotifier(),
);

final cartTotalsProvider = Provider<CartTotals>((ref) {
  final cart = ref.watch(cartProvider.notifier);
  return cart.totals;
});

final cartItemCountProvider = Provider<int>((ref) {
  final cartItems = ref.watch(cartProvider);
  return cartItems.fold<int>(0, (sum, item) => sum + item.quantity);
});

final cartSubtotalProvider = Provider<double>((ref) {
  final cartItems = ref.watch(cartProvider);
  return cartItems.fold<double>(0, (sum, item) => sum + item.totalPrice);
});

final isCartEmptyProvider = Provider<bool>((ref) {
  final cartItems = ref.watch(cartProvider);
  return cartItems.isEmpty;
});

// Helper extension
extension ListExtension<T> on List<T> {
  T? get firstOrNull => isEmpty ? null : first;
}
