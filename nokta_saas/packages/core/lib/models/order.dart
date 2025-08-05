import 'package:freezed_annotation/freezed_annotation.dart';
import 'cart_item.dart';

part 'order.freezed.dart';
part 'order.g.dart';

enum OrderStatus {
  @JsonValue('pending')
  pending,
  @JsonValue('confirmed')
  confirmed,
  @JsonValue('preparing')
  preparing,
  @JsonValue('ready')
  ready,
  @JsonValue('out_for_delivery')
  outForDelivery,
  @JsonValue('delivered')
  delivered,
  @JsonValue('cancelled')
  cancelled,
  @JsonValue('refunded')
  refunded,
}

enum OrderType {
  @JsonValue('dine_in')
  dineIn,
  @JsonValue('takeaway')
  takeaway,
  @JsonValue('delivery')
  delivery,
  @JsonValue('online')
  online,
}

enum PaymentMethod {
  @JsonValue('cash')
  cash,
  @JsonValue('card')
  card,
  @JsonValue('mobile_payment')
  mobilePayment,
  @JsonValue('bank_transfer')
  bankTransfer,
}

@freezed
class Order with _$Order {
  const factory Order({
    required int id,
    required int tenantId,
    required int branchId,
    String? orderNumber,
    required OrderType orderType,
    required OrderStatus status,
    required List<OrderItem> items,
    required double subtotal,
    required double tax,
    required double discount,
    required double deliveryFee,
    required double total,
    required PaymentMethod paymentMethod,
    String? paymentStatus,
    String? paymentReference,
    String? customerName,
    String? customerPhone,
    String? customerEmail,
    String? customerAddress,
    int? tableNumber,
    String? specialInstructions,
    int? estimatedPrepTime,
    DateTime? scheduledFor,
    int? driverId,
    String? driverNotes,
    required DateTime createdAt,
    required DateTime updatedAt,
    DateTime? confirmedAt,
    DateTime? readyAt,
    DateTime? deliveredAt,
  }) = _Order;

  factory Order.fromJson(Map<String, dynamic> json) => _$OrderFromJson(json);

  factory Order.fromMap(Map<String, dynamic> map) {
    return Order(
      id: map['id'] as int,
      tenantId: map['tenant_id'] as int,
      branchId: map['branch_id'] as int,
      orderNumber: map['order_number'] as String?,
      orderType: OrderType.values.firstWhere(
        (e) => e.name == map['order_type'],
        orElse: () => OrderType.dineIn,
      ),
      status: OrderStatus.values.firstWhere(
        (e) => e.name == map['status'],
        orElse: () => OrderStatus.pending,
      ),
      items: [], // Will be loaded separately
      subtotal: (map['subtotal'] as num).toDouble(),
      tax: (map['tax'] as num).toDouble(),
      discount: (map['discount'] as num).toDouble(),
      deliveryFee: (map['delivery_fee'] as num).toDouble(),
      total: (map['total'] as num).toDouble(),
      paymentMethod: PaymentMethod.values.firstWhere(
        (e) => e.name == map['payment_method'],
        orElse: () => PaymentMethod.cash,
      ),
      paymentStatus: map['payment_status'] as String?,
      paymentReference: map['payment_reference'] as String?,
      customerName: map['customer_name'] as String?,
      customerPhone: map['customer_phone'] as String?,
      customerEmail: map['customer_email'] as String?,
      customerAddress: map['customer_address'] as String?,
      tableNumber: map['table_number'] as int?,
      specialInstructions: map['special_instructions'] as String?,
      estimatedPrepTime: map['estimated_prep_time'] as int?,
      scheduledFor: map['scheduled_for'] != null 
          ? DateTime.parse(map['scheduled_for'] as String)
          : null,
      driverId: map['driver_id'] as int?,
      driverNotes: map['driver_notes'] as String?,
      createdAt: DateTime.parse(map['created_at'] as String),
      updatedAt: DateTime.parse(map['updated_at'] as String),
      confirmedAt: map['confirmed_at'] != null 
          ? DateTime.parse(map['confirmed_at'] as String)
          : null,
      readyAt: map['ready_at'] != null 
          ? DateTime.parse(map['ready_at'] as String)
          : null,
      deliveredAt: map['delivered_at'] != null 
          ? DateTime.parse(map['delivered_at'] as String)
          : null,
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'id': id,
      'tenant_id': tenantId,
      'branch_id': branchId,
      'order_number': orderNumber,
      'order_type': orderType.name,
      'status': status.name,
      'subtotal': subtotal,
      'tax': tax,
      'discount': discount,
      'delivery_fee': deliveryFee,
      'total': total,
      'payment_method': paymentMethod.name,
      'payment_status': paymentStatus,
      'payment_reference': paymentReference,
      'customer_name': customerName,
      'customer_phone': customerPhone,
      'customer_email': customerEmail,
      'customer_address': customerAddress,
      'table_number': tableNumber,
      'special_instructions': specialInstructions,
      'estimated_prep_time': estimatedPrepTime,
      'scheduled_for': scheduledFor?.toIso8601String(),
      'driver_id': driverId,
      'driver_notes': driverNotes,
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
      'confirmed_at': confirmedAt?.toIso8601String(),
      'ready_at': readyAt?.toIso8601String(),
      'delivered_at': deliveredAt?.toIso8601String(),
    };
  }
}

@freezed
class OrderItem with _$OrderItem {
  const factory OrderItem({
    required int id,
    required int orderId,
    required int productId,
    required String productName,
    required int quantity,
    required double unitPrice,
    required double totalPrice,
    String? notes,
    List<OrderItemModifier>? modifiers,
  }) = _OrderItem;

  factory OrderItem.fromJson(Map<String, dynamic> json) => _$OrderItemFromJson(json);

  factory OrderItem.fromCartItem(CartItem cartItem) {
    return OrderItem(
      id: 0, // Will be set by database
      orderId: 0, // Will be set when adding to order
      productId: cartItem.product.id,
      productName: cartItem.product.name,
      quantity: cartItem.quantity,
      unitPrice: cartItem.unitPrice,
      totalPrice: cartItem.totalPrice,
      notes: cartItem.notes,
      modifiers: cartItem.modifiers?.map((m) => OrderItemModifier(
        id: m.id,
        name: m.name,
        price: m.price,
        type: m.type.name,
      )).toList(),
    );
  }
}

@freezed
class OrderItemModifier with _$OrderItemModifier {
  const factory OrderItemModifier({
    required int id,
    required String name,
    required double price,
    required String type,
  }) = _OrderItemModifier;

  factory OrderItemModifier.fromJson(Map<String, dynamic> json) => _$OrderItemModifierFromJson(json);
}