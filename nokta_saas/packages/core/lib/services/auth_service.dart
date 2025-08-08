// packages/core/lib/services/auth_service.dart
import 'package:dio/dio.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:jwt_decoder/jwt_decoder.dart';
import 'package:riverpod/riverpod.dart';

class AuthService {
  final Dio _dio;
  final FlutterSecureStorage _storage;
  
  AuthService(this._dio, this._storage);
  
  Future<AuthResult> login({
    required String username,
    required String password,
    required String tenantId,
  }) async {
    try {
      final response = await _dio.post('/auth/login', data: {
        'username': username,
        'password': password,
      }, options: Options(headers: {
        'X-Tenant-ID': tenantId,
      }));
      
      final token = response.data['token'];
      final user = User.fromJson(response.data['user']);
      
      // Store credentials securely
      await _storage.write(key: 'auth_token', value: token);
      await _storage.write(key: 'tenant_id', value: tenantId);
      
      // Configure dio for future requests
      _dio.options.headers['Authorization'] = 'Bearer $token';
      _dio.options.headers['X-Tenant-ID'] = tenantId;
      
      return AuthResult.success(user: user, token: token);
    } on DioException catch (e) {
      return AuthResult.failure(
        message: e.response?.data['message'] ?? 'Login failed',
      );
    }
  }
  
  Future<bool> isTokenValid() async {
    final token = await _storage.read(key: 'auth_token');
    if (token == null) return false;
    
    return !JwtDecoder.isExpired(token);
  }
  
  Future<void> logout() async {
    await _storage.deleteAll();
    _dio.options.headers.remove('Authorization');
    _dio.options.headers.remove('X-Tenant-ID');
  }
}

// Provider setup
final authServiceProvider = Provider((ref) {
  final dio = ref.watch(dioProvider);
  return AuthService(dio, const FlutterSecureStorage());
});