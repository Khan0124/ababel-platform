import 'package:freezed_annotation/freezed_annotation.dart';

part 'category.freezed.dart';
part 'category.g.dart';

@freezed
class Category with _$Category {
  const factory Category({
    required int id,
    required int tenantId,
    required String name,
    required bool isActive,
    required DateTime createdAt,
    required DateTime updatedAt,
    String? description,
    String? imageUrl,
    String? color,
    int? sortOrder,
  }) = _Category;

  factory Category.fromJson(Map<String, dynamic> json) =>
      _$CategoryFromJson(json);

  factory Category.fromMap(Map<String, dynamic> map) => Category(
    id: map['id'] as int,
    tenantId: map['tenant_id'] as int? ?? 1,
    name: map['name'] as String,
    description: map['description'] as String?,
    imageUrl: map['image_url'] as String?,
    color: map['color'] as String?,
    isActive: (map['is_active'] as int? ?? 1) == 1,
    sortOrder: map['sort_order'] as int?,
    createdAt: DateTime.parse(map['created_at'] as String),
    updatedAt: DateTime.parse(map['updated_at'] as String),
  );

  Map<String, dynamic> toMap() => {
    'id': id,
    'tenant_id': tenantId,
    'name': name,
    'description': description,
    'image_url': imageUrl,
    'color': color,
    'is_active': isActive ? 1 : 0,
    'sort_order': sortOrder,
    'created_at': createdAt.toIso8601String(),
    'updated_at': updatedAt.toIso8601String(),
  };
}
