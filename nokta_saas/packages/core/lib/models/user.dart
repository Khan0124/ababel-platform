import 'package:freezed_annotation/freezed_annotation.dart';

part 'user.freezed.dart';
part 'user.g.dart';

enum UserRole {
  @JsonValue(0)
  admin,
  @JsonValue(1)
  manager,
  @JsonValue(2)
  cashier,
  @JsonValue(3)
  callCenter,
  @JsonValue(4)
  supervisor,
  @JsonValue(5)
  driver,
  @JsonValue(6)
  customer,
}

@freezed
class User with _$User {
  const factory User({
    required int id,
    required int tenantId,
    required String username,
    required UserRole role,
    required bool isActive,
    required DateTime createdAt,
    required DateTime updatedAt,
    String? email,
    String? phone,
    String? fullName,
    String? avatarUrl,
    int? restaurantId,
    int? branchId,
    DateTime? lastLoginAt,
    int? failedLoginAttempts,
    DateTime? lockedUntil,
  }) = _User;

  factory User.fromJson(Map<String, dynamic> json) => _$UserFromJson(json);

  // Factory method for database mapping
  factory User.fromMap(Map<String, dynamic> map) => User(
    id: map['id'] as int,
    tenantId: map['tenant_id'] as int? ?? 1,
    username: map['username'] as String,
    email: map['email'] as String?,
    phone: map['phone'] as String?,
    fullName: map['full_name'] as String?,
    avatarUrl: map['avatar_url'] as String?,
    restaurantId: map['restaurant_id'] as int?,
    branchId: map['branch_id'] as int?,
    role: UserRole.values[map['role'] as int? ?? 0],
    isActive: (map['is_active'] as int? ?? 1) == 1,
    lastLoginAt: map['last_login_at'] != null
        ? DateTime.parse(map['last_login_at'] as String)
        : null,
    createdAt: DateTime.parse(map['created_at'] as String),
    updatedAt: DateTime.parse(map['updated_at'] as String),
    failedLoginAttempts: map['failed_login_attempts'] as int? ?? 0,
    lockedUntil: map['locked_until'] != null
        ? DateTime.parse(map['locked_until'] as String)
        : null,
  );

  // Method to convert to database map
  Map<String, dynamic> toMap() => {
    'id': id,
    'tenant_id': tenantId,
    'username': username,
    'email': email,
    'phone': phone,
    'full_name': fullName,
    'avatar_url': avatarUrl,
    'restaurant_id': restaurantId,
    'branch_id': branchId,
    'role': role.index,
    'is_active': isActive ? 1 : 0,
    'last_login_at': lastLoginAt?.toIso8601String(),
    'created_at': createdAt.toIso8601String(),
    'updated_at': updatedAt.toIso8601String(),
    'failed_login_attempts': failedLoginAttempts ?? 0,
    'locked_until': lockedUntil?.toIso8601String(),
  };
}
