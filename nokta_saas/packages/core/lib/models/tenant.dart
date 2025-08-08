import 'package:freezed_annotation/freezed_annotation.dart';

part 'tenant.freezed.dart';
part 'tenant.g.dart';

enum TenantStatus {
  @JsonValue('active')
  active,
  @JsonValue('suspended')
  suspended,
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
    String? domain,
    String? logo,
    String? phone,
    String? email,
    String? address,
    @Default(TenantStatus.active) TenantStatus status,
    @Default(SubscriptionPlan.basic) SubscriptionPlan subscriptionPlan,
    DateTime? subscriptionExpires,
    Map<String, dynamic>? settings,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) = _Tenant;

  factory Tenant.fromJson(Map<String, dynamic> json) => _$TenantFromJson(json);

  factory Tenant.fromMap(Map<String, dynamic> map) {
    return Tenant(
      id: map['id'] as int,
      name: map['name'] as String,
      domain: map['domain'] as String?,
      logo: map['logo'] as String?,
      phone: map['phone'] as String?,
      email: map['email'] as String?,
      address: map['address'] as String?,
      status: TenantStatus.values.firstWhere(
        (e) => e.name == map['status'],
        orElse: () => TenantStatus.active,
      ),
      subscriptionPlan: SubscriptionPlan.values.firstWhere(
        (e) => e.name == map['subscription_plan'],
        orElse: () => SubscriptionPlan.basic,
      ),
      subscriptionExpires: map['subscription_expires'] != null 
          ? DateTime.parse(map['subscription_expires'] as String)
          : null,
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
      'name': name,
      'domain': domain,
      'logo': logo,
      'phone': phone,
      'email': email,
      'address': address,
      'status': status.name,
      'subscription_plan': subscriptionPlan.name,
      'subscription_expires': subscriptionExpires?.toIso8601String(),
      'settings': settings,
      'created_at': createdAt?.toIso8601String(),
      'updated_at': updatedAt?.toIso8601String(),
    };
  }
}

// Tenant Settings Model
class TenantSettings {
  final double taxRate;
  final String currency;
  final String timezone;
  final String orderPrefix;
  final bool enableOnlineOrdering;
  final bool enableTableBooking;
  final bool enableLoyaltyProgram;
  final bool enableInventoryTracking;
  final bool enableMultiBranch;
  final Map<String, dynamic> paymentMethods;
  final Map<String, dynamic> deliverySettings;
  final Map<String, dynamic> printingSettings;

  TenantSettings({
    this.taxRate = 0.15,
    this.currency = 'SAR',
    this.timezone = 'Asia/Riyadh',
    this.orderPrefix = 'ORD',
    this.enableOnlineOrdering = true,
    this.enableTableBooking = true,
    this.enableLoyaltyProgram = true,
    this.enableInventoryTracking = true,
    this.enableMultiBranch = false,
    this.paymentMethods = const {},
    this.deliverySettings = const {},
    this.printingSettings = const {},
  });

  factory TenantSettings.fromJson(Map<String, dynamic> json) {
    return TenantSettings(
      taxRate: (json['tax_rate'] as num?)?.toDouble() ?? 0.15,
      currency: json['currency'] as String? ?? 'SAR',
      timezone: json['timezone'] as String? ?? 'Asia/Riyadh',
      orderPrefix: json['order_prefix'] as String? ?? 'ORD',
      enableOnlineOrdering: json['enable_online_ordering'] as bool? ?? true,
      enableTableBooking: json['enable_table_booking'] as bool? ?? true,
      enableLoyaltyProgram: json['enable_loyalty_program'] as bool? ?? true,
      enableInventoryTracking: json['enable_inventory_tracking'] as bool? ?? true,
      enableMultiBranch: json['enable_multi_branch'] as bool? ?? false,
      paymentMethods: json['payment_methods'] as Map<String, dynamic>? ?? {},
      deliverySettings: json['delivery_settings'] as Map<String, dynamic>? ?? {},
      printingSettings: json['printing_settings'] as Map<String, dynamic>? ?? {},
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'tax_rate': taxRate,
      'currency': currency,
      'timezone': timezone,
      'order_prefix': orderPrefix,
      'enable_online_ordering': enableOnlineOrdering,
      'enable_table_booking': enableTableBooking,
      'enable_loyalty_program': enableLoyaltyProgram,
      'enable_inventory_tracking': enableInventoryTracking,
      'enable_multi_branch': enableMultiBranch,
      'payment_methods': paymentMethods,
      'delivery_settings': deliverySettings,
      'printing_settings': printingSettings,
    };
  }
}
