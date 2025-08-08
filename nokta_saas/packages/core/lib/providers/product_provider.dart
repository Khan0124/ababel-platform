import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../models/product.dart';
import '../models/category.dart';
import '../services/product_service.dart';
import '../db/local_db.dart';

// Product Query Parameters
class ProductsQuery {
  final int? categoryId;
  final bool? isAvailable;
  final String? searchQuery;
  final String? sortBy;
  final int? limit;
  final int? offset;

  const ProductsQuery({
    this.categoryId,
    this.isAvailable,
    this.searchQuery,
    this.sortBy,
    this.limit,
    this.offset,
  });

  @override
  bool operator ==(Object other) {
    if (identical(this, other)) return true;
    return other is ProductsQuery &&
        other.categoryId == categoryId &&
        other.isAvailable == isAvailable &&
        other.searchQuery == searchQuery &&
        other.sortBy == sortBy &&
        other.limit == limit &&
        other.offset == offset;
  }

  @override
  int get hashCode =>
      categoryId.hashCode ^
      isAvailable.hashCode ^
      searchQuery.hashCode ^
      sortBy.hashCode ^
      limit.hashCode ^
      offset.hashCode;
}

// Products Provider
final productsProvider = FutureProvider.family<List<Product>, ProductsQuery>(
  (ref, query) async {
    try {
      // First try to get from local database
      final localProducts = await LocalDB.getProducts(
        categoryId: query.categoryId,
        isAvailable: query.isAvailable,
        searchQuery: query.searchQuery,
      );
      
      if (localProducts.isNotEmpty) {
        // Also trigger background sync
        ref.read(productServiceProvider).syncProducts();
        return localProducts;
      }
      
      // If no local data, fetch from API
      final service = ref.read(productServiceProvider);
      return await service.getProducts(query);
    } catch (e) {
      // Fallback to local data on error
      return await LocalDB.getProducts(
        categoryId: query.categoryId,
        isAvailable: query.isAvailable,
        searchQuery: query.searchQuery,
      );
    }
  },
);

// Single Product Provider
final productProvider = FutureProvider.family<Product?, int>(
  (ref, productId) async {
    try {
      // First check local database
      final localProduct = await LocalDB.getProduct(productId);
      if (localProduct != null) {
        return localProduct;
      }
      
      // Fetch from API
      final service = ref.read(productServiceProvider);
      return await service.getProduct(productId);
    } catch (e) {
      return await LocalDB.getProduct(productId);
    }
  },
);

// Categories Provider
final categoriesProvider = FutureProvider<List<Category>>((ref) async {
  try {
    final service = ref.read(productServiceProvider);
    return await service.getCategories();
  } catch (e) {
    // Return default categories on error
    return [
      const Category(id: 1, name: 'المقبلات', nameAr: 'المقبلات'),
      const Category(id: 2, name: 'الأطباق الرئيسية', nameAr: 'الأطباق الرئيسية'),
      const Category(id: 3, name: 'المشروبات', nameAr: 'المشروبات'),
      const Category(id: 4, name: 'الحلويات', nameAr: 'الحلويات'),
    ];
  }
});

// Featured Products Provider
final featuredProductsProvider = FutureProvider<List<Product>>((ref) async {
  final query = ProductsQuery(isAvailable: true, limit: 10);
  final products = await ref.watch(productsProvider(query).future);
  
  // Filter featured products
  return products.where((p) => p.isFeatured ?? false).toList();
});

// Product Search Provider
final productSearchProvider = StateNotifierProvider<ProductSearchNotifier, String>((ref) {
  return ProductSearchNotifier();
});

class ProductSearchNotifier extends StateNotifier<String> {
  ProductSearchNotifier() : super('');
  
  void updateSearch(String query) {
    state = query;
  }
  
  void clearSearch() {
    state = '';
  }
}

// Filtered Products Provider
final filteredProductsProvider = Provider<AsyncValue<List<Product>>>((ref) {
  final searchQuery = ref.watch(productSearchProvider);
  final selectedCategory = ref.watch(selectedCategoryProvider);
  
  final query = ProductsQuery(
    categoryId: selectedCategory,
    searchQuery: searchQuery.isEmpty ? null : searchQuery,
    isAvailable: true,
  );
  
  return ref.watch(productsProvider(query));
});

// Selected Category Provider
final selectedCategoryProvider = StateProvider<int?>((ref) => null);

// Product Inventory Provider
final productInventoryProvider = FutureProvider.family<int?, int>(
  (ref, productId) async {
    try {
      final service = ref.read(productServiceProvider);
      return await service.getProductStock(productId);
    } catch (e) {
      return null;
    }
  },
);

// Product Analytics Provider
final productAnalyticsProvider = FutureProvider.family<ProductAnalytics, int>(
  (ref, productId) async {
    try {
      final service = ref.read(productServiceProvider);
      return await service.getProductAnalytics(productId);
    } catch (e) {
      return ProductAnalytics(
        productId: productId,
        totalSold: 0,
        revenue: 0,
        averageRating: 0,
        reviewCount: 0,
      );
    }
  },
);

// Product Modifiers Provider
final productModifiersProvider = FutureProvider.family<List<ProductModifier>, int>(
  (ref, productId) async {
    try {
      final service = ref.read(productServiceProvider);
      return await service.getProductModifiers(productId);
    } catch (e) {
      return [];
    }
  },
);

// Analytics Model
class ProductAnalytics {
  final int productId;
  final int totalSold;
  final double revenue;
  final double averageRating;
  final int reviewCount;
  
  ProductAnalytics({
    required this.productId,
    required this.totalSold,
    required this.revenue,
    required this.averageRating,
    required this.reviewCount,
  });
}

// Product Modifier Model
class ProductModifier {
  final int id;
  final String groupName;
  final String name;
  final double price;
  final String type;
  final bool isRequired;
  final int minSelection;
  final int? maxSelection;
  
  ProductModifier({
    required this.id,
    required this.groupName,
    required this.name,
    required this.price,
    required this.type,
    required this.isRequired,
    required this.minSelection,
    this.maxSelection,
  });
}
