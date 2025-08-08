import 'package:freezed_annotation/freezed_annotation.dart';
import 'product.dart';

part 'cart_item.freezed.dart';
part 'cart_item.g.dart';

enum ModifierType {
  @JsonValue('size')
  size,
  @JsonValue('addon')
  addon,
  @JsonValue('extra')
  extra,
  @JsonValue('option')
  option,
}

@freezed
class CartItem with _$CartItem {
  const factory CartItem({
    required Product product,
    required int quantity,
    required double unitPrice,
    String? notes,
    List<CartItemModifier>? modifiers,
  }) = _CartItem;

  const CartItem._();

  double get totalPrice {
    double modifiersPrice = modifiers?.fold(0, (sum, m) => sum + m.price) ?? 0;
    return (unitPrice + modifiersPrice) * quantity;
  }

  factory CartItem.fromJson(Map<String, dynamic> json) => _$CartItemFromJson(json);
}

@freezed
class CartItemModifier with _$CartItemModifier {
  const factory CartItemModifier({
    required int id,
    required String name,
    required double price,
    required ModifierType type,
    String? groupName,
  }) = _CartItemModifier;

  factory CartItemModifier.fromJson(Map<String, dynamic> json) => 
      _$CartItemModifierFromJson(json);
}
