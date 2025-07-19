// lib/models/order.dart
class Order {
  final int id;
  final double total;
  final String orderType;
  final String paymentMethod;
  final String? customerName;
  final String? customerPhone;
  final String? customerAddress;
  final DateTime createdAt;
  final List<OrderItem> items;

  Order({
    required this.id,
    required this.total,
    required this.orderType,
    required this.paymentMethod,
    this.customerName,
    this.customerPhone,
    this.customerAddress,
    required this.createdAt,
    required this.items,
  });

  Map<String, dynamic> toJson() {
    return {
      'total': total,
      'order_type': orderType,
      'payment_method': paymentMethod,
      'customer_name': customerName,
      'customer_phone': customerPhone,
      'customer_address': customerAddress,
      'items': items.map((item) => item.toJson()).toList(),
    };
  }
}

class OrderItem {
  final int productId;
  final int quantity;
  final double price;

  OrderItem({
    required this.productId,
    required this.quantity,
    required this.price,
  });

  Map<String, dynamic> toJson() {
    return {
      'product_id': productId,
      'quantity': quantity,
      'price': price,
    };
  }
}