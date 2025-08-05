// apps/customer_app/lib/screens/home/home_screen.dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:infinite_scroll_pagination/infinite_scroll_pagination.dart';

class HomeScreen extends ConsumerStatefulWidget {
  const HomeScreen({super.key});

  @override
  _HomeScreenState createState() => _HomeScreenState();
}

class _HomeScreenState extends ConsumerState<HomeScreen> {
  final PagingController<int, Restaurant> _pagingController = PagingController(
    firstPageKey: 1,
  );

  String? _selectedCategory;
  String _searchQuery = '';

  @override
  void initState() {
    super.initState();
    _pagingController.addPageRequestListener(_fetchRestaurants);
  }

  Future<void> _fetchRestaurants(int pageKey) async {
    try {
      final restaurants = await ref
          .read(restaurantServiceProvider)
          .getRestaurants(
            page: pageKey,
            category: _selectedCategory,
            search: _searchQuery,
            userLocation: ref.read(locationProvider),
          );

      final isLastPage = restaurants.length < 20;
      if (isLastPage) {
        _pagingController.appendLastPage(restaurants);
      } else {
        _pagingController.appendPage(restaurants, pageKey + 1);
      }
    } catch (error) {
      _pagingController.error = error;
    }
  }

  @override
  Widget build(BuildContext context) => Scaffold(
    body: CustomScrollView(
      slivers: [
        SliverAppBar(
          expandedHeight: 200,
          floating: true,
          pinned: true,
          flexibleSpace: FlexibleSpaceBar(
            background: LocationHeader(
              onLocationChanged: () => _pagingController.refresh(),
            ),
            title: SearchBar(
              onChanged: (query) {
                setState(() => _searchQuery = query);
                _pagingController.refresh();
              },
            ),
          ),
        ),
        SliverToBoxAdapter(
          child: CategoryFilter(
            onCategorySelected: (category) {
              setState(() => _selectedCategory = category);
              _pagingController.refresh();
            },
          ),
        ),
        PagedSliverGrid<int, Restaurant>(
          pagingController: _pagingController,
          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: 2,
            childAspectRatio: 0.75,
            crossAxisSpacing: 16,
            mainAxisSpacing: 16,
          ),
          builderDelegate: PagedChildBuilderDelegate<Restaurant>(
            itemBuilder: (context, restaurant, index) => RestaurantCard(
              restaurant: restaurant,
              onTap: () => context.pushNamed(
                'restaurant',
                params: {'id': restaurant.id.toString()},
              ),
            ),
          ),
        ),
      ],
    ),
  );
}
