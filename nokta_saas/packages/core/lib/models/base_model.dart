// packages/core/lib/models/base_model.dart
abstract class BaseModel {
  
  BaseModel({
    required this.id,
    required this.createdAt,
    required this.updatedAt,
  });
  final int id;
  final DateTime createdAt;
  final DateTime updatedAt;
  
  Map<String, dynamic> toJson();
}

// packages/core/lib/services/tenant_service.dart
import 'package:riverpod/riverpod.dart';

class TenantService {
  String? _currentTenantId;
  
  String get currentTenantId => _currentTenantId ?? '';
  
  void setTenant(String tenantId) {
    _currentTenantId = tenantId;
  }
  
  Map<String, String> get headers => {
    if (_currentTenantId != null) 'X-Tenant-ID': _currentTenantId!,
  };
}

final tenantServiceProvider = Provider((ref) => TenantService());