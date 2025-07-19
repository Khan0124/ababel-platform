class Tenant {
  final int id;
  final String name;
  final String subdomain;
  final String plan;
  final String status;

  Tenant({
    required this.id,
    required this.name,
    required this.subdomain,
    required this.plan,
    required this.status,
  });

  factory Tenant.fromJson(Map<String, dynamic> json) {
    return Tenant(
      id: json['id'],
      name: json['name'],
      subdomain: json['subdomain'],
      plan: json['plan'],
      status: json['status'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'subdomain': subdomain,
      'plan': plan,
      'status': status,
    };
  }
}