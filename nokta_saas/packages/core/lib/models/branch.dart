// lib/models/branch.dart
class Branch {
  final int id;
  final String name;
  final String? address;

  Branch({
    required this.id,
    required this.name,
    this.address,
  });

  factory Branch.fromJson(Map<String, dynamic> json) {
    return Branch(
      id: json['id'],
      name: json['name'],
      address: json['address'],
    );
  }
}