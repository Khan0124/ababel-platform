import 'package:freezed_annotation/freezed_annotation.dart';

part 'branch.freezed.dart';
part 'branch.g.dart';

@freezed
class Branch with _$Branch {
  const factory Branch({
    required int id,
    required int tenantId,
    required String name,
    String? code,
    String? address,
    String? phone,
    String? email,
    double? latitude,
    double? longitude,
    String? openingTime,
    String? closingTime,
    @Default(false) bool isMain,
    @Default(true) bool isActive,
    Map<String, dynamic>? settings,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) = _Branch;

  factory Branch.fromJson(Map<String, dynamic> json) => _$BranchFromJson(json);

  factory Branch.fromMap(Map<String, dynamic> map) {
    return Branch(
      id: map['id'] as int,
      tenantId: map['tenant_id'] as int,
      name: map['name'] as String,
      code: map['code'] as String?,
      address: map['address'] as String?,
      phone: map['phone'] as String?,
      email: map['email'] as String?,
      latitude: map['latitude'] != null 
          ? (map['latitude'] as num).toDouble()
          : null,
      longitude: map['longitude'] != null 
          ? (map['longitude'] as num).toDouble()
          : null,
      openingTime: map['opening_time'] as String?,
      closingTime: map['closing_time'] as String?,
      isMain: (map['is_main'] as int? ?? 0) == 1,
      isActive: (map['is_active'] as int? ?? 1) == 1,
      settings: map['settings'] as Map<String, dynamic>?,
      createdAt: map['created_at'] != null 
          ? DateTime.parse(map['created_at'] as String)
          : null,
      updatedAt: map['updated_at'] != null 
          ? DateTime.parse(map['updated_at'] as String)
          : null,
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'id': id,
      'tenant_id': tenantId,
      'name': name,
      'code': code,
      'address': address,
      'phone': phone,
      'email': email,
      'latitude': latitude,
      'longitude': longitude,
      'opening_time': openingTime,
      'closing_time': closingTime,
      'is_main': isMain ? 1 : 0,
      'is_active': isActive ? 1 : 0,
      'settings': settings,
      'created_at': createdAt?.toIso8601String(),
      'updated_at': updatedAt?.toIso8601String(),
    };
  }
}
