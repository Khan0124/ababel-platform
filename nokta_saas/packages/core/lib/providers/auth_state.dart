// packages/core/lib/providers/auth_state.dart
import 'package:riverpod/riverpod.dart';

@freezed
class AuthState with _$AuthState {
  const factory AuthState.initial() = _Initial;
  const factory AuthState.loading() = _Loading;
  const factory AuthState.authenticated(User user) = _Authenticated;
  const factory AuthState.unauthenticated() = _Unauthenticated;
  const factory AuthState.error(String message) = _Error;
}

class AuthNotifier extends StateNotifier<AuthState> {
  AuthNotifier(this._authService) : super(const AuthState.initial()) {
    _checkAuthStatus();
  }
  final AuthService _authService;

  Future<void> _checkAuthStatus() async {
    if (await _authService.isTokenValid()) {
      // Fetch user profile
      final user = await _authService.getCurrentUser();
      if (user != null) {
        state = AuthState.authenticated(user);
      } else {
        state = const AuthState.unauthenticated();
      }
    } else {
      state = const AuthState.unauthenticated();
    }
  }

  Future<void> login(String username, String password, String tenantId) async {
    state = const AuthState.loading();

    final result = await _authService.login(
      username: username,
      password: password,
      tenantId: tenantId,
    );

    result.when(
      success: (user, token) => state = AuthState.authenticated(user),
      failure: (message) => state = AuthState.error(message),
    );
  }
}

final authStateProvider = StateNotifierProvider<AuthNotifier, AuthState>(
  (ref) => AuthNotifier(ref.watch(authServiceProvider)),
);
