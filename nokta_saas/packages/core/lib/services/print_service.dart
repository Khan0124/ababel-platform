// lib/services/print_service.dart
import 'package:http/http.dart' as http;
import 'package:nokta_pos/services/api.dart';

class PrintService {
  static Future<void> printOrder(int orderId, String printerId) async {
    await ApiService.post('/print/order', {
      'order_id': orderId,
      'printer_id': printerId,
    });
  }
}