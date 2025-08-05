import 'package:freezed_annotation/freezed_annotation.dart';

part 'tenant.freezed.dart';
part 'tenant.g.dart';

enum TenantStatus {
  @JsonValue('active')
  active,
  @JsonValue('trial')
  trial,
  @JsonValue('suspended')
  suspended,
  @JsonValue('expired')
  expired,
  @JsonValue('cancelled')
  cancelled,
}

enum SubscriptionPlan {
  @JsonValue('basic')
  basic,
  @JsonValue('standard')
  standard,
  @JsonValue('premium')
  premium,
  @JsonValue('enterprise')
  enterprise,
}

@freezed
class Tenant with _$Tenant {
  const factory Tenant({
    required int id,
    required String name,
    required String subdomain,
    required SubscriptionPlan plan,
    required TenantStatus status,
    required DateTime createdAt,
    required DateTime updatedAt,
    String? description,
    String? logoUrl,
    String? primaryColor,
    String? secondaryColor,
    DateTime? trialEndsAt,
    DateTime? subscriptionEndsAt,
    int? maxUsers,
    int? maxBranches,
    int? maxProducts,
    bool? hasMultiLocation,
    bool? hasDelivery,
    bool? hasOnlineOrdering,
    bool? hasAnalytics,
    bool? hasInventoryManagement,
    Map<String, dynamic>? settings,
  }) = _Tenant;

  factory Tenant.fromJson(Map<String, dynamic> json) => _$TenantFromJson(json);

  factory Tenant.fromMap(Map<String, dynamic> map) => Tenant(
    id: map['id'] as int,
    name: map['name'] as String,
    subdomain: map['subdomain'] as String,
    plan: SubscriptionPlan.values.firstWhere(
      (e) => e.name == map['plan'],
      orElse: () => SubscriptionPlan.basic,
    ),
    status: TenantStatus.values.firstWhere(
      (e) => e.name == map['status'],
      orElse: () => TenantStatus.trial,
    ),
    description: map['description'] as String?,
    logoUrl: map['logo_url'] as String?,
    primaryColor: map['primary_color'] as String?,
    secondaryColor: map['secondary_color'] as String?,
    createdAt: DateTime.parse(map['created_at'] as String),
    updatedAt: DateTime.parse(map['updated_at'] as String),
    trialEndsAt: map['trial_ends_at'] != null
        ? DateTime.parse(map['trial_ends_at'] as String)
        : null,
    subscriptionEndsAt: map['subscription_ends_at'] != null
        ? DateTime.parse(map['subscription_ends_at'] as String)
        : null,
    maxUsers: map['max_users'] as int?,
    maxBranches: map['max_branches'] as int?,
    maxProducts: map['max_products'] as int?,
    hasMultiLocation: map['has_multi_location'] != null
        ? (map['has_multi_location'] as int) == 1
        : null,
    hasDelivery: map['has_delivery'] != null
        ? (map['has_delivery'] as int) == 1
        : null,
    hasOnlineOrdering: map['has_online_ordering'] != null
        ? (map['has_online_ordering'] as int) == 1
        : null,
    hasAnalytics: map['has_analytics'] != null
        ? (map['has_analytics'] as int) == 1
        : null,
    hasInventoryManagement: map['has_inventory_management'] != null
        ? (map['has_inventory_management'] as int) == 1
        : null,
    settings: map['settings'] != null
        ? Map<String, dynamic>.from(map['settings'])
        : null,
  );

  Map<String, dynamic> toMap() => {
    'id': id,
    'name': name,
    'subdomain': subdomain,
    'plan': plan.name,
    'status': status.name,
    'description': description,
    'logo_url': logoUrl,
    'primary_color': primaryColor,
    'secondary_color': secondaryColor,
    'created_at': createdAt.toIso8601String(),
    'updated_at': updatedAt.toIso8601String(),
    'trial_ends_at': trialEndsAt?.toIso8601String(),
    'subscription_ends_at': subscriptionEndsAt?.toIso8601String(),
    'max_users': maxUsers,
    'max_branches': maxBranches,
    'max_products': maxProducts,
    'has_multi_location': hasMultiLocation != null
        ? (hasMultiLocation! ? 1 : 0)
        : null,
    'has_delivery': hasDelivery != null ? (hasDelivery! ? 1 : 0) : null,
    'has_online_ordering': hasOnlineOrdering != null
        ? (hasOnlineOrdering! ? 1 : 0)
        : null,
    'has_analytics': hasAnalytics != null ? (hasAnalytics! ? 1 : 0) : null,
    'has_inventory_management': hasInventoryManagement != null
        ? (hasInventoryManagement! ? 1 : 0)
        : null,
    'settings': settings,
  };
}
