import 'package:flutter/foundation.dart' show kIsWeb;
import '../db/local_db.dart';
import '../models/product.dart';
import 'api.dart';  // هنا صححت الاسم

class ProductService {
  static final LocalDB _localDB = LocalDB();

  // جلب المنتجات
  static Future<List<Product>> fetchProducts() async {
    if (kIsWeb) {
      // استدعاء API في الويب
      return await ApiService.fetchProducts();
    } else {
      // استخدام LocalDB في الموبايل
      return await _localDB.allProducts();
    }
  }

  // إضافة منتج
  static Future<bool> addProduct(Product product) async {
    try {
      if (kIsWeb) {
        await ApiService.addProduct(product);
        return true;  // مهم ترجع true بعد الإضافة في الويب
      } else {
        await _localDB.insertProduct(product);
        return true;
      }
    } catch (e) {
      print('Error adding product: $e');
      return false;
    }
  }

  // تحديث المنتج (غير مدعوم في API الآن)
  static Future<void> updateProduct(Product product) async {
    if (kIsWeb) {
      throw UnimplementedError('تحديث المنتج غير مدعوم من الـ API');
    } else {
      await _localDB.updateProduct(product);
    }
  }

  // حذف المنتج (غير مدعوم في API الآن)
  static Future<void> deleteProduct(int id) async {
    if (kIsWeb) {
      throw UnimplementedError('حذف المنتج غير مدعوم من الـ API');
    } else {
      await _localDB.deleteProduct(id);
    }
  }
}
