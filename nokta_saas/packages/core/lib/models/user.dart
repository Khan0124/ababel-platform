import 'package:freezed_annotation/freezed_annotation.dart';

part 'user.freezed.dart';
part 'user.g.dart';

@freezed
class User with _$User {
  const factory User({
    required int id,
    required int tenantId,
    required String username,
    required String email,
    String? phone,
    String? fullName,
    String? avatarUrl,
    int? restaurantId,
    int? branchId,
    required UserRole role,
    required bool isActive,
    DateTime? lastLoginAt,
    required DateTime createdAt,
    required DateTime updatedAt,
  }) = _User;
  
  factory User.fromJson(Map<String, dynamic> json) => _$UserFromJson(json);
}

enum UserRole {
  @JsonValue('platform_admin')
  platformAdmin,
  @JsonValue('restaurant_owner')
  restaurantOwner,
  @JsonValue('manager')
  manager,
  @JsonValue('cashier')
  cashier,
  @JsonValue('call_center')
  callCenter,
  @JsonValue('driver')
  driver,
  @JsonValue('customer')
  customer,
}

enum UserRole { manager, callCenter, supervisor, cashier, driver }

class User {
  final int id;
  final String username;
  final String password; // مبدئيًا نص عادي – لاحقًا هش
  final int restaurantId;
  final int branchId;
  final UserRole role;

  User({
    required this.id,
    required this.username,
    required this.password,
    required this.restaurantId,
    required this.branchId,
    required this.role,
  });

  factory User.fromMap(Map<String, dynamic> m) => User(
    id: m['id'],
    username: m['username'],
    password: m['password'],
    restaurantId: m['restaurant_id'],
    branchId: m['branch_id'],
    role: UserRole.values[m['role']],
  );

  Map<String, dynamic> toMap() => {
    'id': id,
    'username': username,
    'password': password,
    'restaurant_id': restaurantId,
    'branch_id': branchId,
    'role': role.index,
  };
}
