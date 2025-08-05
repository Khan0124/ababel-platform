import 'package:freezed_annotation/freezed_annotation.dart';
import 'product.dart';

part 'cart_item.freezed.dart';
part 'cart_item.g.dart';

@freezed
class CartItem with _$CartItem {
  const factory CartItem({
    required Product product,
    required int quantity,
    required double unitPrice,
    required double totalPrice,
    required DateTime addedAt,
    String? notes,
    List<CartItemModifier>? modifiers,
  }) = _CartItem;

  factory CartItem.fromJson(Map<String, dynamic> json) =>
      _$CartItemFromJson(json);

  factory CartItem.fromProduct(Product product, {int quantity = 1}) => CartItem(
    product: product,
    quantity: quantity,
    unitPrice: product.price,
    totalPrice: product.price * quantity,
    addedAt: DateTime.now(),
  );
}

@freezed
class CartItemModifier with _$CartItemModifier {
  const factory CartItemModifier({
    required int id,
    required String name,
    required double price,
    required ModifierType type,
  }) = _CartItemModifier;

  factory CartItemModifier.fromJson(Map<String, dynamic> json) =>
      _$CartItemModifierFromJson(json);
}

enum ModifierType {
  @JsonValue('addon')
  addon,
  @JsonValue('size')
  size,
  @JsonValue('extras')
  extras,
  @JsonValue('customization')
  customization,
}
