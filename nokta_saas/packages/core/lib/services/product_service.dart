import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:riverpod/riverpod.dart';
import '../db/local_db.dart';
import '../models/product.dart';
import '../models/category.dart';
import 'api.dart';

class ProductService {
  final LocalDB _localDB = LocalDB();
  final ApiService _apiService = ApiService();

  // Get all products for current tenant
  Future<List<Product>> getProducts({
    int? categoryId,
    bool? isAvailable,
    String? searchQuery,
    int page = 1,
    int limit = 50,
  }) async {
    try {
      if (kIsWeb) {
        // Use API for web
        final queryParams = <String, dynamic>{
          'page': page,
          'limit': limit,
        };

        if (categoryId != null) queryParams['category_id'] = categoryId;
        if (isAvailable != null) queryParams['is_available'] = isAvailable;
        if (searchQuery != null && searchQuery.isNotEmpty) {
          queryParams['search'] = searchQuery;
        }

        final response = await _apiService.get('/products', queryParams: queryParams);
        final List<dynamic> data = response.data['data'];
        
        return data.map((json) => Product.fromJson(json)).toList();
      } else {
        // Use local DB for mobile
        return await _localDB.allProducts();
      }
    } catch (e) {
      throw Exception('Failed to fetch products: $e');
    }
  }

  // Get single product by ID
  Future<Product> getProduct(int id) async {
    try {
      if (kIsWeb) {
        final response = await _apiService.get('/products/$id');
        return Product.fromJson(response.data);
      } else {
        final products = await _localDB.allProducts();
        return products.firstWhere((p) => p.id == id);
      }
    } catch (e) {
      throw Exception('Failed to fetch product: $e');
    }
  }

  // Create new product
  Future<Product> createProduct(Product product) async {
    try {
      if (kIsWeb) {
        final response = await _apiService.post('/products', data: product.toJson());
        return Product.fromJson(response.data);
      } else {
        await _localDB.insertProduct(product);
        return product;
      }
    } catch (e) {
      throw Exception('Failed to create product: $e');
    }
  }

  // Update existing product
  Future<Product> updateProduct(Product product) async {
    try {
      if (kIsWeb) {
        final response = await _apiService.put('/products/${product.id}', data: product.toJson());
        return Product.fromJson(response.data);
      } else {
        await _localDB.updateProduct(product);
        return product;
      }
    } catch (e) {
      throw Exception('Failed to update product: $e');
    }
  }

  // Delete product
  Future<void> deleteProduct(int id) async {
    try {
      if (kIsWeb) {
        await _apiService.delete('/products/$id');
      } else {
        await _localDB.deleteProduct(id);
      }
    } catch (e) {
      throw Exception('Failed to delete product: $e');
    }
  }

  // Get categories
  Future<List<Category>> getCategories() async {
    try {
      if (kIsWeb) {
        final response = await _apiService.get('/categories');
        final List<dynamic> data = response.data['data'];
        
        return data.map((json) => Category.fromJson(json)).toList();
      } else {
        // Return default categories for local DB
        return [
          Category(
            id: 1,
            tenantId: 1,
            name: 'الوجبات الرئيسية',
            isActive: true,
            createdAt: DateTime.now(),
            updatedAt: DateTime.now(),
          ),
          Category(
            id: 2,
            tenantId: 1,
            name: 'المشروبات',
            isActive: true,
            createdAt: DateTime.now(),
            updatedAt: DateTime.now(),
          ),
          Category(
            id: 3,
            tenantId: 1,
            name: 'الحلويات',
            isActive: true,
            createdAt: DateTime.now(),
            updatedAt: DateTime.now(),
          ),
        ];
      }
    } catch (e) {
      throw Exception('Failed to fetch categories: $e');
    }
  }

  // Search products
  Future<List<Product>> searchProducts(String query) async {
    try {
      if (kIsWeb) {
        final response = await _apiService.get('/products/search', queryParams: {'q': query});
        final List<dynamic> data = response.data['data'];
        
        return data.map((json) => Product.fromJson(json)).toList();
      } else {
        final allProducts = await _localDB.allProducts();
        return allProducts.where((product) => 
          product.name.toLowerCase().contains(query.toLowerCase()) ||
          product.description.toLowerCase().contains(query.toLowerCase())
        ).toList();
      }
    } catch (e) {
      throw Exception('Failed to search products: $e');
    }
  }

  // Update product availability
  Future<void> updateProductAvailability(int productId, bool isAvailable) async {
    try {
      if (kIsWeb) {
        await _apiService.put('/products/$productId/availability', 
          data: {'is_available': isAvailable});
      } else {
        // Handle local DB update
        final products = await _localDB.allProducts();
        final product = products.firstWhere((p) => p.id == productId);
        final updatedProduct = product.copyWith(isAvailable: isAvailable);
        await _localDB.updateProduct(updatedProduct);
      }
    } catch (e) {
      throw Exception('Failed to update product availability: $e');
    }
  }
}

// Providers
final productServiceProvider = Provider<ProductService>((ref) {
  return ProductService();
});

final productsProvider = FutureProvider.autoDispose.family<List<Product>, ProductsQuery>((ref, query) {
  final productService = ref.watch(productServiceProvider);
  return productService.getProducts(
    categoryId: query.categoryId,
    isAvailable: query.isAvailable,
    searchQuery: query.searchQuery,
    page: query.page,
    limit: query.limit,
  );
});

final categoriesProvider = FutureProvider.autoDispose<List<Category>>((ref) {
  final productService = ref.watch(productServiceProvider);
  return productService.getCategories();
});

final productProvider = FutureProvider.autoDispose.family<Product, int>((ref, id) {
  final productService = ref.watch(productServiceProvider);
  return productService.getProduct(id);
});

// Data classes
class ProductsQuery {
  final int? categoryId;
  final bool? isAvailable;
  final String? searchQuery;
  final int page;
  final int limit;

  const ProductsQuery({
    this.categoryId,
    this.isAvailable,
    this.searchQuery,
    this.page = 1,
    this.limit = 50,
  });

  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      other is ProductsQuery &&
          runtimeType == other.runtimeType &&
          categoryId == other.categoryId &&
          isAvailable == other.isAvailable &&
          searchQuery == other.searchQuery &&
          page == other.page &&
          limit == other.limit;

  @override
  int get hashCode =>
      categoryId.hashCode ^
      isAvailable.hashCode ^
      searchQuery.hashCode ^
      page.hashCode ^
      limit.hashCode;
}
