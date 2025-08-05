// apps/admin_panel/lib/screens/dashboard/admin_dashboard_screen.dart
class AdminDashboardScreen extends ConsumerWidget {
  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final selectedTenant = ref.watch(selectedTenantProvider);
    final dateRange = ref.watch(dateRangeProvider);
    
    return Scaffold(
      body: Row(
        children: [
          // Sidebar
          NavigationRail(
            extended: true,
            destinations: const [
              NavigationRailDestination(
                icon: Icon(Icons.dashboard),
                label: Text('Dashboard'),
              ),
              NavigationRailDestination(
                icon: Icon(Icons.business),
                label: Text('Tenants'),
              ),
              NavigationRailDestination(
                icon: Icon(Icons.restaurant),
                label: Text('Restaurants'),
              ),
              NavigationRailDestination(
                icon: Icon(Icons.receipt),
                label: Text('Orders'),
              ),
              NavigationRailDestination(
                icon: Icon(Icons.people),
                label: Text('Users'),
              ),
              NavigationRailDestination(
                icon: Icon(Icons.attach_money),
                label: Text('Finance'),
              ),
              NavigationRailDestination(
                icon: Icon(Icons.analytics),
                label: Text('Analytics'),
              ),
              NavigationRailDestination(
                icon: Icon(Icons.settings),
                label: Text('Settings'),
              ),
            ],
            selectedIndex: ref.watch(selectedNavIndexProvider),
            onDestinationSelected: (index) =>
                ref.read(selectedNavIndexProvider.notifier).state = index,
          ),
          
          // Main Content
          Expanded(
            child: Column(
              children: [
                // Header
                Container(
                  height: 80,
                  padding: const EdgeInsets.symmetric(horizontal: 24),
                  decoration: BoxDecoration(
                    color: Theme.of(context).cardColor,
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(0.05),
                        blurRadius: 10,
                      ),
                    ],
                  ),
                  child: Row(
                    children: [
                      // Tenant Selector
                      if (ref.watch(selectedNavIndexProvider) > 0)
                        SizedBox(
                          width: 300,
                          child: TenantSelector(
                            selectedTenant: selectedTenant,
                            onChanged: (tenant) => ref
                                .read(selectedTenantProvider.notifier)
                                .state = tenant,
                          ),
                        ),
                      const Spacer(),
                      // Date Range Picker
                      DateRangePicker(
                        dateRange: dateRange,
                        onChanged: (range) =>
                            ref.read(dateRangeProvider.notifier).state = range,
                      ),
                      const SizedBox(width: 24),
                      // Notifications
                      IconButton(
                        icon: const Icon(Icons.notifications),
                        onPressed: () => _showNotifications(context),
                      ),
                      const SizedBox(width: 16),
                      // Profile
                      const CircleAvatar(
                        child: Text('AD'),
                      ),
                    ],
                  ),
                ),
                
                // Content
                Expanded(
                  child: _buildContent(ref),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
  
  Widget _buildContent(WidgetRef ref) {
    final index = ref.watch(selectedNavIndexProvider);
    
    switch (index) {
      case 0:
        return const DashboardOverview();
      case 1:
        return const TenantsManagement();
      case 2:
        return const RestaurantsManagement();
      case 3:
        return const OrdersManagement();
      case 4:
        return const UsersManagement();
      case 5:
        return const FinanceManagement();
      case 6:
        return const AnalyticsView();
      case 7:
        return const SettingsView();
      default:
        return const DashboardOverview();
    }
  }
}