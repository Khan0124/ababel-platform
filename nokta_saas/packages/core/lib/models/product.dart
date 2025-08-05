import 'package:freezed_annotation/freezed_annotation.dart';

part 'product.freezed.dart';
part 'product.g.dart';

@freezed
class Product with _$Product {
  const factory Product({
    required int id,
    required int tenantId,
    required String name,
    required String description,
    required double price,
    required bool isAvailable,
    required DateTime createdAt,
    required DateTime updatedAt,
    String? imageUrl,
    int? categoryId,
    String? categoryName,
    int? stockQuantity,
    String? barcode,
    String? sku,
    double? cost,
    double? weight,
    String? unit,
    List<String>? allergens,
    List<String>? ingredients,
    int? preparationTime,
    bool? isVegetarian,
    bool? isVegan,
    bool? isGlutenFree,
  }) = _Product;

  factory Product.fromJson(Map<String, dynamic> json) =>
      _$ProductFromJson(json);

  // Factory method for database mapping
  factory Product.fromMap(Map<String, dynamic> map) => Product(
    id: map['id'] as int,
    tenantId: map['tenant_id'] as int? ?? 1,
    name: map['name'] as String,
    description: map['description'] as String? ?? '',
    price: (map['price'] as num).toDouble(),
    imageUrl: map['image_url'] as String?,
    categoryId: map['category_id'] as int?,
    categoryName: map['category_name'] as String?,
    isAvailable: (map['is_available'] as int? ?? 1) == 1,
    stockQuantity: map['stock_quantity'] as int?,
    barcode: map['barcode'] as String?,
    sku: map['sku'] as String?,
    cost: map['cost'] != null ? (map['cost'] as num).toDouble() : null,
    weight: map['weight'] != null ? (map['weight'] as num).toDouble() : null,
    unit: map['unit'] as String?,
    allergens: map['allergens'] != null
        ? (map['allergens'] as String).split(',')
        : null,
    ingredients: map['ingredients'] != null
        ? (map['ingredients'] as String).split(',')
        : null,
    preparationTime: map['preparation_time'] as int?,
    isVegetarian: map['is_vegetarian'] != null
        ? (map['is_vegetarian'] as int) == 1
        : null,
    isVegan: map['is_vegan'] != null ? (map['is_vegan'] as int) == 1 : null,
    isGlutenFree: map['is_gluten_free'] != null
        ? (map['is_gluten_free'] as int) == 1
        : null,
    createdAt: DateTime.parse(map['created_at'] as String),
    updatedAt: DateTime.parse(map['updated_at'] as String),
  );

  // Method to convert to database map
  Map<String, dynamic> toMap() => {
    'id': id,
    'tenant_id': tenantId,
    'name': name,
    'description': description,
    'price': price,
    'image_url': imageUrl,
    'category_id': categoryId,
    'is_available': isAvailable ? 1 : 0,
    'stock_quantity': stockQuantity,
    'barcode': barcode,
    'sku': sku,
    'cost': cost,
    'weight': weight,
    'unit': unit,
    'allergens': allergens?.join(','),
    'ingredients': ingredients?.join(','),
    'preparation_time': preparationTime,
    'is_vegetarian': isVegetarian != null ? (isVegetarian! ? 1 : 0) : null,
    'is_vegan': isVegan != null ? (isVegan! ? 1 : 0) : null,
    'is_gluten_free': isGlutenFree != null ? (isGlutenFree! ? 1 : 0) : null,
    'created_at': createdAt.toIso8601String(),
    'updated_at': updatedAt.toIso8601String(),
  };
}
