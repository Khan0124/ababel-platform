import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../models/product.dart';
import '../models/category.dart';
import '../providers/product_provider.dart';
import '../db/local_db.dart';
import 'api.dart';

class ProductService {
  final Dio _dio;
  
  ProductService(this._dio);
  
  Future<List<Product>> getProducts(ProductsQuery query) async {
    try {
      // Try to get from API
      final response = await _dio.get('/products', queryParameters: {
        'category_id': query.categoryId,
        'is_available': query.isAvailable,
        'search': query.searchQuery,
        'sort_by': query.sortBy,
        'limit': query.limit,
        'offset': query.offset,
      });
      
      if (response.statusCode == 200) {
        final products = (response.data['data'] as List)
            .map((p) => Product.fromJson(p))
            .toList();
        
        // Save to local database for offline access
        for (final product in products) {
          await LocalDB.insertProduct(product);
        }
        
        return products;
      }
    } catch (e) {
      // Fallback to local database
      print('Failed to fetch products from API: $e');
    }
    
    // Get from local database
    return await LocalDB.getProducts(
      categoryId: query.categoryId,
      isAvailable: query.isAvailable,
      searchQuery: query.searchQuery,
    );
  }
  
  Future<Product?> getProduct(int productId) async {
    try {
      final response = await _dio.get('/products/$productId');
      
      if (response.statusCode == 200) {
        final product = Product.fromJson(response.data);
        
        // Update local database
        await LocalDB.updateProduct(product);
        
        return product;
      }
    } catch (e) {
      print('Failed to fetch product from API: $e');
    }
    
    // Get from local database
    return await LocalDB.getProduct(productId);
  }
  
  Future<List<Category>> getCategories() async {
    try {
      final response = await _dio.get('/categories');
      
      if (response.statusCode == 200) {
        return (response.data['data'] as List)
            .map((c) => Category.fromJson(c))
            .toList();
      }
    } catch (e) {
      print('Failed to fetch categories: $e');
    }
    
    // Return default categories
    return [
      const Category(id: 1, name: 'Appetizers', nameAr: 'المقبلات'),
      const Category(id: 2, name: 'Main Dishes', nameAr: 'الأطباق الرئيسية'),
      const Category(id: 3, name: 'Beverages', nameAr: 'المشروبات'),
      const Category(id: 4, name: 'Desserts', nameAr: 'الحلويات'),
    ];
  }
  
  Future<Product> createProduct(Product product) async {
    try {
      final response = await _dio.post('/products', data: product.toJson());
      
      if (response.statusCode == 201) {
        final newProduct = Product.fromJson(response.data);
        
        // Save to local database
        await LocalDB.insertProduct(newProduct);
        
        return newProduct;
      }
    } catch (e) {
      throw Exception('Failed to create product: $e');
    }
    
    throw Exception('Failed to create product');
  }
  
  Future<Product> updateProduct(Product product) async {
    try {
      final response = await _dio.put(
        '/products/${product.id}',
        data: product.toJson(),
      );
      
      if (response.statusCode == 200) {
        final updatedProduct = Product.fromJson(response.data);
        
        // Update local database
        await LocalDB.updateProduct(updatedProduct);
        
        return updatedProduct;
      }
    } catch (e) {
      throw Exception('Failed to update product: $e');
    }
    
    throw Exception('Failed to update product');
  }
  
  Future<bool> deleteProduct(int productId) async {
    try {
      final response = await _dio.delete('/products/$productId');
      
      if (response.statusCode == 200) {
        // Delete from local database
        await LocalDB.deleteProduct(productId);
        return true;
      }
    } catch (e) {
      print('Failed to delete product: $e');
    }
    
    return false;
  }
  
  Future<int?> getProductStock(int productId) async {
    try {
      final response = await _dio.get('/products/$productId/stock');
      
      if (response.statusCode == 200) {
        return response.data['stock'] as int;
      }
    } catch (e) {
      print('Failed to get product stock: $e');
    }
    
    return null;
  }
  
  Future<bool> updateProductStock(int productId, int quantity) async {
    try {
      final response = await _dio.put(
        '/products/$productId/stock',
        data: {'quantity': quantity},
      );
      
      return response.statusCode == 200;
    } catch (e) {
      print('Failed to update product stock: $e');
      return false;
    }
  }
  
  Future<List<ProductModifier>> getProductModifiers(int productId) async {
    try {
      final response = await _dio.get('/products/$productId/modifiers');
      
      if (response.statusCode == 200) {
        return (response.data['data'] as List)
            .map((m) => ProductModifier(
                  id: m['id'],
                  groupName: m['group_name'],
                  name: m['name'],
                  price: (m['price'] as num).toDouble(),
                  type: m['type'],
                  isRequired: m['is_required'],
                  minSelection: m['min_selection'],
                  maxSelection: m['max_selection'],
                ))
            .toList();
      }
    } catch (e) {
      print('Failed to get product modifiers: $e');
    }
    
    return [];
  }
  
  Future<ProductAnalytics> getProductAnalytics(int productId) async {
    try {
      final response = await _dio.get('/products/$productId/analytics');
      
      if (response.statusCode == 200) {
        return ProductAnalytics(
          productId: productId,
          totalSold: response.data['total_sold'],
          revenue: (response.data['revenue'] as num).toDouble(),
          averageRating: (response.data['average_rating'] as num).toDouble(),
          reviewCount: response.data['review_count'],
        );
      }
    } catch (e) {
      print('Failed to get product analytics: $e');
    }
    
    return ProductAnalytics(
      productId: productId,
      totalSold: 0,
      revenue: 0,
      averageRating: 0,
      reviewCount: 0,
    );
  }
  
  Future<void> syncProducts() async {
    try {
      // Get last sync time
      final lastSync = DateTime.now().subtract(const Duration(days: 1));
      
      final response = await _dio.get('/products/sync', queryParameters: {
        'last_sync': lastSync.toIso8601String(),
      });
      
      if (response.statusCode == 200) {
        final products = (response.data['data'] as List)
            .map((p) => Product.fromJson(p))
            .toList();
        
        // Update local database
        for (final product in products) {
          await LocalDB.updateProduct(product);
        }
      }
    } catch (e) {
      print('Product sync failed: $e');
    }
  }
  
  // Barcode scanning
  Future<Product?> getProductByBarcode(String barcode) async {
    try {
      final response = await _dio.get('/products/barcode/$barcode');
      
      if (response.statusCode == 200) {
        return Product.fromJson(response.data);
      }
    } catch (e) {
      print('Failed to get product by barcode: $e');
    }
    
    // Try local database
    final products = await LocalDB.getProducts();
    try {
      return products.firstWhere((p) => p.barcode == barcode);
    } catch (e) {
      return null;
    }
  }
  
  // Bulk operations
  Future<bool> bulkUpdatePrices(Map<int, double> priceUpdates) async {
    try {
      final response = await _dio.put('/products/bulk/prices', data: {
        'updates': priceUpdates.entries
            .map((e) => {'id': e.key, 'price': e.value})
            .toList(),
      });
      
      return response.statusCode == 200;
    } catch (e) {
      print('Failed to bulk update prices: $e');
      return false;
    }
  }
  
  Future<bool> bulkUpdateAvailability(List<int> productIds, bool isAvailable) async {
    try {
      final response = await _dio.put('/products/bulk/availability', data: {
        'product_ids': productIds,
        'is_available': isAvailable,
      });
      
      return response.statusCode == 200;
    } catch (e) {
      print('Failed to bulk update availability: $e');
      return false;
    }
  }
}

// Provider
final productServiceProvider = Provider((ref) {
  final dio = ref.watch(dioProvider);
  return ProductService(dio);
});
