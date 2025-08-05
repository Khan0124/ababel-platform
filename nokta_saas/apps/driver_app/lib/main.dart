// apps/driver_app/lib/main.dart
class DriverApp extends ConsumerWidget {
  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final authState = ref.watch(authStateProvider);
    
    return MaterialApp(
      title: 'Nokta Driver',
      theme: AppTheme.driverTheme(),
      home: authState.when(
        authenticated: (user) => const DriverHomeScreen(),
        unauthenticated: () => const LoginScreen(),
        loading: () => const SplashScreen(),
        error: (message) => ErrorScreen(message: message),
      ),
    );
  }
}