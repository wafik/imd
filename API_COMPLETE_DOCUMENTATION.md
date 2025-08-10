# IMD API Complete Documentation

## Overview
API lengkap untuk aplikasi IMD (Inisiasi Menyusui Dini) yang dirancang untuk konsumsi Flutter mobile app. Mencakup authentication, dashboard analytics, data management, dan AI-powered query system.

## Base URL
```
http://localhost:8000/api
```

## Authentication
Semua endpoint (kecuali login) memerlukan Laravel Sanctum Bearer Token:
```
Authorization: Bearer {your-token}
```

## API Endpoints Summary

### 🔐 Authentication Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/login` | User login |
| POST | `/api/auth/logout` | User logout |
| GET | `/api/auth/profile` | Get user profile |
| PUT | `/api/auth/profile` | Update user profile |
| PUT | `/api/auth/change-password` | Change password |
| POST | `/api/auth/upload-avatar` | Upload profile picture |

### 📊 Dashboard Analytics Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/dashboard` | Complete dashboard data |
| GET | `/api/dashboard/stats` | Statistics only (lightweight) |
| GET | `/api/dashboard/charts` | Charts data only |

### 📋 IMD Data Management Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/imd` | List IMD records with filters |
| POST | `/api/imd` | Create new IMD record |
| GET | `/api/imd/{id}` | Get specific IMD record |
| PUT | `/api/imd/{id}` | Update IMD record |
| DELETE | `/api/imd/{id}` | Delete IMD record |

### 🤖 Ask AI Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/ask-ai/question` | Ask natural language question |
| POST | `/api/ask-ai/execute-query` | Execute SQL query directly |
| GET | `/api/ask-ai/samples` | Get sample questions |
| GET | `/api/ask-ai/schema` | Get database schema info |

## Quick Start Guide

### 1. Authentication Flow
```dart
// Login
final loginResponse = await authService.login(email, password);
final token = loginResponse.data.token;

// Set token for all subsequent requests
apiService.setAuthToken(token);
```

### 2. Dashboard Data
```dart
// Get complete dashboard
final dashboard = await dashboardService.getDashboard(
  year: 2025,
  month: 8,
  caraPersalinan: 'SC'
);

// Get only stats for quick updates
final stats = await dashboardService.getStats();
```

### 3. IMD Data Management
```dart
// List with filters
final imdList = await imdService.getList(
  page: 1,
  search: 'Siti',
  caraPersalinan: 'SC'
);

// Create new record
final newImd = await imdService.create(imdData);
```

### 4. AI Questions
```dart
// Ask question
final aiResponse = await askAIService.askQuestion(
  'Berapa total data IMD bulan ini?'
);

// Execute specific query
final queryResult = await askAIService.executeQuery(
  'SELECT COUNT(*) FROM imds WHERE tanggal_imd >= CURDATE()'
);
```

## Flutter Integration Examples

### Complete Service Setup
```dart
class ApiService {
  final Dio _dio = Dio();
  final String baseUrl = 'http://localhost:8000/api';
  
  // Individual services
  late final AuthService auth;
  late final DashboardService dashboard;
  late final ImdService imd;
  late final AskAIService askAI;
  
  ApiService() {
    _setupDio();
    auth = AuthService(_dio, baseUrl);
    dashboard = DashboardService(_dio, baseUrl);
    imd = ImdService(_dio, baseUrl);
    askAI = AskAIService(_dio, baseUrl);
  }
  
  void _setupDio() {
    _dio.options.headers['Accept'] = 'application/json';
    _dio.options.headers['Content-Type'] = 'application/json';
    
    // Add interceptor for automatic token management
    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) {
          final token = getStoredToken();
          if (token != null) {
            options.headers['Authorization'] = 'Bearer $token';
          }
          handler.next(options);
        },
        onError: (error, handler) {
          if (error.response?.statusCode == 401) {
            // Handle unauthorized - redirect to login
            handleUnauthorized();
          }
          handler.next(error);
        },
      ),
    );
  }
  
  String? getStoredToken() {
    // Implement secure token storage
    return SecureStorage.getToken();
  }
  
  void handleUnauthorized() {
    // Clear token and redirect to login
    SecureStorage.clearToken();
    NavigationService.goToLogin();
  }
}
```

### Main App Data Provider
```dart
class AppDataProvider extends ChangeNotifier {
  final ApiService _apiService = ApiService();
  
  // Dashboard data
  DashboardData? _dashboardData;
  DashboardStats? _stats;
  
  // IMD data
  List<ImdRecord> _imdRecords = [];
  int _currentPage = 1;
  bool _hasMoreData = true;
  
  // AI data
  List<AskAIResponse> _aiHistory = [];
  List<String> _sampleQuestions = [];
  
  // Getters
  DashboardData? get dashboardData => _dashboardData;
  DashboardStats? get stats => _stats;
  List<ImdRecord> get imdRecords => _imdRecords;
  List<AskAIResponse> get aiHistory => _aiHistory;
  List<String> get sampleQuestions => _sampleQuestions;
  
  // Dashboard methods
  Future<void> loadDashboard({
    int? year,
    int? month,
    String? caraPersalinan,
  }) async {
    try {
      _dashboardData = await _apiService.dashboard.getDashboard(
        year: year,
        month: month,
        caraPersalinan: caraPersalinan,
      );
      notifyListeners();
    } catch (e) {
      throw Exception('Failed to load dashboard: $e');
    }
  }
  
  Future<void> loadStats() async {
    try {
      _stats = await _apiService.dashboard.getStats();
      notifyListeners();
    } catch (e) {
      throw Exception('Failed to load stats: $e');
    }
  }
  
  // IMD methods
  Future<void> loadImdRecords({
    bool refresh = false,
    String? search,
    String? caraPersalinan,
  }) async {
    if (refresh) {
      _currentPage = 1;
      _imdRecords.clear();
      _hasMoreData = true;
    }
    
    if (!_hasMoreData) return;
    
    try {
      final response = await _apiService.imd.getList(
        page: _currentPage,
        search: search,
        caraPersalinan: caraPersalinan,
      );
      
      if (refresh) {
        _imdRecords = response.data;
      } else {
        _imdRecords.addAll(response.data);
      }
      
      _currentPage++;
      _hasMoreData = response.hasNextPage;
      notifyListeners();
    } catch (e) {
      throw Exception('Failed to load IMD records: $e');
    }
  }
  
  Future<void> createImdRecord(ImdRecord record) async {
    try {
      final newRecord = await _apiService.imd.create(record);
      _imdRecords.insert(0, newRecord);
      notifyListeners();
    } catch (e) {
      throw Exception('Failed to create IMD record: $e');
    }
  }
  
  // AI methods
  Future<void> askAIQuestion(String question) async {
    try {
      final response = await _apiService.askAI.askQuestion(question);
      _aiHistory.insert(0, response);
      notifyListeners();
    } catch (e) {
      throw Exception('Failed to ask AI: $e');
    }
  }
  
  Future<void> loadSampleQuestions() async {
    try {
      _sampleQuestions = await _apiService.askAI.getSampleQuestions();
      notifyListeners();
    } catch (e) {
      throw Exception('Failed to load samples: $e');
    }
  }
}
```

### Main App Structure
```dart
void main() {
  runApp(
    MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AppDataProvider()),
        ChangeNotifierProvider(create: (_) => AuthProvider()),
      ],
      child: MyApp(),
    ),
  );
}

class MyApp extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'IMD App',
      theme: ThemeData(primarySwatch: Colors.blue),
      home: Consumer<AuthProvider>(
        builder: (context, auth, _) {
          if (auth.isLoggedIn) {
            return MainScreen();
          } else {
            return LoginScreen();
          }
        },
      ),
    );
  }
}
```

## Error Handling Best Practices

### Global Error Handler
```dart
class ApiErrorHandler {
  static void handleError(dynamic error, BuildContext context) {
    String message = 'An error occurred';
    
    if (error is DioError) {
      switch (error.response?.statusCode) {
        case 400:
          message = 'Bad request. Please check your input.';
          break;
        case 401:
          message = 'Unauthorized. Please login again.';
          // Auto logout
          context.read<AuthProvider>().logout();
          break;
        case 403:
          message = 'Access forbidden.';
          break;
        case 404:
          message = 'Resource not found.';
          break;
        case 422:
          message = _extractValidationMessage(error.response?.data);
          break;
        case 500:
          message = 'Server error. Please try again later.';
          break;
        default:
          message = 'Network error: ${error.message}';
      }
    }
    
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: Colors.red,
        action: SnackBarAction(
          label: 'Dismiss',
          textColor: Colors.white,
          onPressed: () {
            ScaffoldMessenger.of(context).hideCurrentSnackBar();
          },
        ),
      ),
    );
  }
  
  static String _extractValidationMessage(dynamic responseData) {
    if (responseData is Map && responseData.containsKey('errors')) {
      final errors = responseData['errors'] as Map;
      final firstError = errors.values.first;
      if (firstError is List && firstError.isNotEmpty) {
        return firstError.first.toString();
      }
    }
    return 'Validation failed';
  }
}
```

## Caching Strategy

### Local Storage Service
```dart
class CacheService {
  static const String _dashboardKey = 'dashboard_cache';
  static const String _imdListKey = 'imd_list_cache';
  static const String _samplesKey = 'ai_samples_cache';
  
  // Cache dashboard data for 5 minutes
  static Future<void> cacheDashboard(DashboardData data) async {
    final prefs = await SharedPreferences.getInstance();
    final cacheData = {
      'data': data.toJson(),
      'timestamp': DateTime.now().millisecondsSinceEpoch,
    };
    await prefs.setString(_dashboardKey, jsonEncode(cacheData));
  }
  
  static Future<DashboardData?> getCachedDashboard() async {
    final prefs = await SharedPreferences.getInstance();
    final cacheString = prefs.getString(_dashboardKey);
    
    if (cacheString != null) {
      final cacheData = jsonDecode(cacheString);
      final timestamp = cacheData['timestamp'] as int;
      final now = DateTime.now().millisecondsSinceEpoch;
      
      // Check if cache is still valid (5 minutes)
      if (now - timestamp < 5 * 60 * 1000) {
        return DashboardData.fromJson(cacheData['data']);
      }
    }
    
    return null;
  }
  
  // Similar methods for other data types...
}
```

## Performance Optimization

### Pagination Helper
```dart
class PaginationHelper<T> {
  List<T> items = [];
  int currentPage = 1;
  bool hasMoreData = true;
  bool isLoading = false;
  
  Future<void> loadMore(
    Future<PaginatedResponse<T>> Function(int page) fetcher,
    VoidCallback onUpdate,
  ) async {
    if (isLoading || !hasMoreData) return;
    
    isLoading = true;
    onUpdate();
    
    try {
      final response = await fetcher(currentPage);
      items.addAll(response.data);
      currentPage++;
      hasMoreData = response.hasNextPage;
    } catch (e) {
      // Handle error
      rethrow;
    } finally {
      isLoading = false;
      onUpdate();
    }
  }
  
  void refresh(
    Future<PaginatedResponse<T>> Function(int page) fetcher,
    VoidCallback onUpdate,
  ) {
    items.clear();
    currentPage = 1;
    hasMoreData = true;
    loadMore(fetcher, onUpdate);
  }
}
```

## Testing Examples

### Unit Tests
```dart
void main() {
  group('ApiService Tests', () {
    late ApiService apiService;
    late MockDio mockDio;
    
    setUp(() {
      mockDio = MockDio();
      apiService = ApiService(dio: mockDio);
    });
    
    test('should login successfully', () async {
      // Mock response
      when(mockDio.post('/auth/login', data: anyNamed('data')))
          .thenAnswer((_) async => Response(
                data: {
                  'success': true,
                  'data': {
                    'user': {'id': 1, 'email': 'test@example.com'},
                    'token': 'mock_token'
                  }
                },
                statusCode: 200,
                requestOptions: RequestOptions(path: '/auth/login'),
              ));
      
      final result = await apiService.auth.login('test@example.com', 'password');
      
      expect(result.success, true);
      expect(result.data.token, 'mock_token');
    });
    
    test('should handle dashboard data', () async {
      // Mock dashboard response
      when(mockDio.get('/dashboard'))
          .thenAnswer((_) async => Response(
                data: {
                  'status': 'success',
                  'data': {
                    'stats': {
                      'total_imd': 100,
                      'sc_percentage': 40.0,
                      'spontan_percentage': 60.0,
                      'avg_duration': 45.5
                    }
                  }
                },
                statusCode: 200,
                requestOptions: RequestOptions(path: '/dashboard'),
              ));
      
      final result = await apiService.dashboard.getDashboard();
      
      expect(result.stats.totalImd, 100);
      expect(result.stats.scPercentage, 40.0);
    });
  });
}
```

## Deployment Notes

### Environment Configuration
```dart
class Config {
  static const String baseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'http://localhost:8000/api',
  );
  
  static const bool isProduction = bool.fromEnvironment(
    'DART_DEFINES_IS_PRODUCTION',
    defaultValue: false,
  );
  
  static const String appName = String.fromEnvironment(
    'APP_NAME',
    defaultValue: 'IMD Development',
  );
}
```

### Build Commands
```bash
# Development build
flutter build apk --dart-define=API_BASE_URL=http://localhost:8000/api

# Production build
flutter build apk --dart-define=API_BASE_URL=https://api.imd.example.com/api --dart-define=DART_DEFINES_IS_PRODUCTION=true
```

## Security Checklist

- ✅ All API endpoints require authentication
- ✅ SQL injection protection in Ask AI
- ✅ Input validation on all endpoints
- ✅ Secure token storage in Flutter
- ✅ HTTPS in production
- ✅ Rate limiting (implement if needed)
- ✅ CORS configuration
- ✅ Error message sanitization

## Monitoring & Analytics

### API Response Logging
```dart
class ApiLogger {
  static void logRequest(RequestOptions options) {
    if (!Config.isProduction) {
      print('→ ${options.method} ${options.path}');
      print('→ Headers: ${options.headers}');
      if (options.data != null) {
        print('→ Data: ${options.data}');
      }
    }
  }
  
  static void logResponse(Response response) {
    if (!Config.isProduction) {
      print('← ${response.statusCode} ${response.requestOptions.path}');
      print('← Data: ${response.data}');
    }
  }
  
  static void logError(DioError error) {
    print('✗ ${error.requestOptions.method} ${error.requestOptions.path}');
    print('✗ Error: ${error.message}');
    if (error.response != null) {
      print('✗ Response: ${error.response?.data}');
    }
  }
}
```

## Support & Documentation

- **Swagger UI**: `http://localhost:8000/api/documentation`
- **Postman Collection**: Available in project repository
- **Flutter Samples**: Complete example app in `/flutter_example` directory
- **API Documentation**: This document and individual API docs

## Contact & Support

- **API Issues**: Create issue in repository
- **Flutter Integration**: Check example code and documentation
- **Production Support**: Contact development team

---

**Last Updated**: August 11, 2025  
**API Version**: 1.0.0  
**Flutter Compatibility**: >=3.0.0
