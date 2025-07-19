import 'dart:convert';
import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:nokta_pos/providers/session.dart';
import 'package:provider/provider.dart';

class ApiService {
  static const String baseUrl = 'http://62.77.155.129:3000';
  static String? authToken;
  static String? tenantId;

  static Future<http.Response> get(String endpoint) async {
    final uri = Uri.parse('$baseUrl$endpoint');
    return await http.get(uri, headers: _getHeaders());
  }

  static Future<http.Response> post(String endpoint, dynamic body) async {
    final uri = Uri.parse('$baseUrl$endpoint');
    return await http.post(
      uri,
      headers: _getHeaders(),
      body: jsonEncode(body),
    );
  }

  static Future<http.Response> put(String endpoint, dynamic body) async {
    final uri = Uri.parse('$baseUrl$endpoint');
    return await http.put(
      uri,
      headers: _getHeaders(),
      body: jsonEncode(body),
    );
  }

  static Map<String, String> _getHeaders() {
    final headers = {
      'Content-Type': 'application/json',
    };

    if (authToken != null) {
      headers['Authorization'] = 'Bearer $authToken';
    }

    if (tenantId != null) {
      headers['X-Tenant-ID'] = tenantId!;
    }

    return headers;
  }

  static Future<void> initAuth(BuildContext context) async {
    final session = Provider.of<Session>(context, listen: false);
    if (session.current != null && session.token != null) {
      authToken = session.token;
      tenantId = session.current!.tenantId.toString();
    }
  }
}