// apps/customer_app/lib/screens/restaurant/restaurant_screen.dart
class RestaurantScreen extends ConsumerWidget {
  final int restaurantId;
  
  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final restaurant = ref.watch(restaurantProvider(restaurantId));
    final cart = ref.watch(cartProvider);
    
    return restaurant.when(
      data: (restaurant) => Scaffold(
        body: CustomScrollView(
          slivers: [
            SliverAppBar(
              expandedHeight: 250,
              pinned: true,
              flexibleSpace: RestaurantHeader(restaurant: restaurant),
            ),
            SliverToBoxAdapter(
              child: RestaurantInfo(restaurant: restaurant),
            ),
            SliverPersistentHeader(
              delegate: CategoryTabBarDelegate(
                categories: restaurant.categories,
              ),
              pinned: true,
            ),
            SliverList(
              delegate: SliverChildBuilderDelegate(
                (context, index) {
                  final category = restaurant.categories[index];
                  return CategorySection(
                    category: category,
                    products: category.products,
                    onAddToCart: (product) => ref.read(cartProvider.notifier)
                        .addItem(product, restaurant),
                  );
                },
                childCount: restaurant.categories.length,
              ),
            ),
          ],
        ),
        bottomNavigationBar: cart.items.isNotEmpty
            ? CartBottomBar(
                itemCount: cart.totalItems,
                totalAmount: cart.totalAmount,
                onViewCart: () => context.pushNamed('cart'),
              )
            : null,
      ),
      loading: () => const LoadingScreen(),
      error: (error, stack) => ErrorScreen(error: error),
    );
  }
}