// packages/core/lib/providers/cart_provider.dart
@freezed
class CartState with _$CartState {
  const factory CartState({
    Restaurant? restaurant,
    @Default([]) List<CartItem> items,
    Coupon? appliedCoupon,
    @Default(0) double deliveryFee,
    @Default(0) double taxAmount,
  }) = _CartState;
  
  const CartState._();
  
  double get subtotal => items.fold(0, (sum, item) => sum + item.totalPrice);
  double get discount => appliedCoupon?.calculateDiscount(subtotal) ?? 0;
  double get total => subtotal - discount + deliveryFee + taxAmount;
  int get totalItems => items.fold(0, (sum, item) => sum + item.quantity);
}

class CartNotifier extends StateNotifier<CartState> {
  CartNotifier() : super(const CartState());
  
  void addItem(Product product, Restaurant restaurant) {
    // Check if switching restaurants
    if (state.restaurant != null && state.restaurant!.id != restaurant.id) {
      // Show confirmation dialog
      return;
    }
    
    final existingIndex = state.items.indexWhere((item) => 
      item.product.id == product.id && 
      item.modifiers.equals(product.selectedModifiers)
    );
    
    if (existingIndex >= 0) {
      // Increment quantity
      final items = [...state.items];
      items[existingIndex] = items[existingIndex].copyWith(
        quantity: items[existingIndex].quantity + 1,
      );
      state = state.copyWith(items: items);
    } else {
      // Add new item
      state = state.copyWith(
        restaurant: restaurant,
        items: [
          ...state.items,
          CartItem(
            product: product,
            modifiers: product.selectedModifiers,
            quantity: 1,
          ),
        ],
      );
    }
    
    // Recalculate fees
    _updateFees();
  }
  
  void removeItem(int index) {
    final items = [...state.items];
    items.removeAt(index);
    
    if (items.isEmpty) {
      state = const CartState();
    } else {
      state = state.copyWith(items: items);
      _updateFees();
    }
  }
  
  void applyCoupon(Coupon coupon) {
    if (coupon.isValidFor(state.restaurant!, state.subtotal)) {
      state = state.copyWith(appliedCoupon: coupon);
    }
  }
  
  void _updateFees() {
    if (state.restaurant != null) {
      final taxAmount = state.subtotal * state.restaurant!.taxRate / 100;
      state = state.copyWith(
        taxAmount: taxAmount,
        deliveryFee: state.restaurant!.deliveryFee,
      );
    }
  }
  
  void clear() {
    state = const CartState();
  }
}

final cartProvider = StateNotifierProvider<CartNotifier, CartState>((ref) {
  return CartNotifier();
});