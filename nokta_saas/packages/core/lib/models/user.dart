import 'package:freezed_annotation/freezed_annotation.dart';

part 'user.freezed.dart';
part 'user.g.dart';

enum UserRole {
  @JsonValue('super_admin')
  superAdmin,
  @JsonValue('admin')
  admin,
  @JsonValue('manager')
  manager,
  @JsonValue('cashier')
  cashier,
  @JsonValue('kitchen')
  kitchen,
  @JsonValue('driver')
  driver,
  @JsonValue('customer')
  customer,
}

@freezed
class User with _$User {
  const factory User({
    required int id,
    required int tenantId,
    int? branchId,
    required String username,
    required String email,
    String? fullName,
    String? phone,
    required UserRole role,
    String? avatar,
    @Default(true) bool isActive,
    @Default(false) bool twoFactorEnabled,
    DateTime? lastLogin,
    Map<String, dynamic>? permissions,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) = _User;

  factory User.fromJson(Map<String, dynamic> json) => _$UserFromJson(json);

  factory User.fromMap(Map<String, dynamic> map) {
    return User(
      id: map['id'] as int,
      tenantId: map['tenant_id'] as int,
      branchId: map['branch_id'] as int?,
      username: map['username'] as String,
      email: map['email'] as String,
      fullName: map['full_name'] as String?,
      phone: map['phone'] as String?,
      role: UserRole.values.firstWhere(
        (e) => e.name == map['role'],
        orElse: () => UserRole.customer,
      ),
      avatar: map['avatar'] as String?,
      isActive: (map['is_active'] as int? ?? 1) == 1,
      twoFactorEnabled: (map['two_factor_enabled'] as int? ?? 0) == 1,
      lastLogin: map['last_login'] != null 
          ? DateTime.parse(map['last_login'] as String)
          : null,
      permissions: map['permissions'] as Map<String, dynamic>?,
      createdAt: map['created_at'] != null 
          ? DateTime.parse(map['created_at'] as String)
          : null,
      updatedAt: map['updated_at'] != null 
          ? DateTime.parse(map['updated_at'] as String)
          : null,
    );
  }
}

// Auth Result Model
class AuthResult {
  final bool success;
  final User? user;
  final String? token;
  final String? refreshToken;
  final String? message;

  AuthResult({
    required this.success,
    this.user,
    this.token,
    this.refreshToken,
    this.message,
  });

  factory AuthResult.success({
    required User user,
    required String token,
    String? refreshToken,
  }) {
    return AuthResult(
      success: true,
      user: user,
      token: token,
      refreshToken: refreshToken,
    );
  }

  factory AuthResult.failure({required String message}) {
    return AuthResult(
      success: false,
      message: message,
    );
  }
}
