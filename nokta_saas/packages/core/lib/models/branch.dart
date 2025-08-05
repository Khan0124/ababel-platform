import 'package:freezed_annotation/freezed_annotation.dart';

part 'branch.freezed.dart';
part 'branch.g.dart';

@freezed
class Branch with _$Branch {
  const factory Branch({
    required int id,
    required int tenantId,
    required int restaurantId,
    required String name,
    required String address,
    required bool isActive,
    required bool isDeliveryEnabled,
    required bool isTakeawayEnabled,
    required bool isDineInEnabled,
    required DateTime createdAt,
    required DateTime updatedAt,
    String? description,
    String? phone,
    String? email,
    double? latitude,
    double? longitude,
    int? maxTables,
    List<String>? workingHours,
    List<String>? deliveryAreas,
    double? deliveryRadius,
    double? minimumOrderAmount,
    double? deliveryFee,
  }) = _Branch;

  factory Branch.fromJson(Map<String, dynamic> json) => _$BranchFromJson(json);

  factory Branch.fromMap(Map<String, dynamic> map) => Branch(
    id: map['id'] as int,
    tenantId: map['tenant_id'] as int,
    restaurantId: map['restaurant_id'] as int,
    name: map['name'] as String,
    description: map['description'] as String?,
    address: map['address'] as String,
    phone: map['phone'] as String?,
    email: map['email'] as String?,
    latitude: map['latitude'] != null
        ? (map['latitude'] as num).toDouble()
        : null,
    longitude: map['longitude'] != null
        ? (map['longitude'] as num).toDouble()
        : null,
    isActive: (map['is_active'] as int? ?? 1) == 1,
    isDeliveryEnabled: (map['is_delivery_enabled'] as int? ?? 0) == 1,
    isTakeawayEnabled: (map['is_takeaway_enabled'] as int? ?? 1) == 1,
    isDineInEnabled: (map['is_dine_in_enabled'] as int? ?? 1) == 1,
    maxTables: map['max_tables'] as int?,
    workingHours: map['working_hours'] != null
        ? (map['working_hours'] as String).split(',')
        : null,
    deliveryAreas: map['delivery_areas'] != null
        ? (map['delivery_areas'] as String).split(',')
        : null,
    deliveryRadius: map['delivery_radius'] != null
        ? (map['delivery_radius'] as num).toDouble()
        : null,
    minimumOrderAmount: map['minimum_order_amount'] != null
        ? (map['minimum_order_amount'] as num).toDouble()
        : null,
    deliveryFee: map['delivery_fee'] != null
        ? (map['delivery_fee'] as num).toDouble()
        : null,
    createdAt: DateTime.parse(map['created_at'] as String),
    updatedAt: DateTime.parse(map['updated_at'] as String),
  );

  Map<String, dynamic> toMap() => {
    'id': id,
    'tenant_id': tenantId,
    'restaurant_id': restaurantId,
    'name': name,
    'description': description,
    'address': address,
    'phone': phone,
    'email': email,
    'latitude': latitude,
    'longitude': longitude,
    'is_active': isActive ? 1 : 0,
    'is_delivery_enabled': isDeliveryEnabled ? 1 : 0,
    'is_takeaway_enabled': isTakeawayEnabled ? 1 : 0,
    'is_dine_in_enabled': isDineInEnabled ? 1 : 0,
    'max_tables': maxTables,
    'working_hours': workingHours?.join(','),
    'delivery_areas': deliveryAreas?.join(','),
    'delivery_radius': deliveryRadius,
    'minimum_order_amount': minimumOrderAmount,
    'delivery_fee': deliveryFee,
    'created_at': createdAt.toIso8601String(),
    'updated_at': updatedAt.toIso8601String(),
  };
}
