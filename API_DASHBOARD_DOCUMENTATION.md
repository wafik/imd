# IMD Dashboard API Documentation

## Overview
API Dashboard untuk aplikasi IMD (Inisiasi Menyusui Dini) yang dirancang khusus untuk konsumsi Flutter mobile app. API ini menyediakan data analytics yang lengkap dengan filtering yang fleksibel.

## Base URL
```
http://localhost:8000/api
```

## Authentication
Semua endpoint dashboard memerlukan authentication menggunakan Laravel Sanctum Bearer Token:
```
Authorization: Bearer {your-token}
```

## Endpoints

### 1. Get Complete Dashboard Data
```http
GET /api/dashboard
```

**Query Parameters:**
- `year` (optional): Filter by year (e.g., 2025)
- `month` (optional): Filter by month 1-12 (e.g., 8)
- `cara_persalinan` (optional): Filter by delivery method ("SC" or "Spontan")

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/dashboard?year=2025&month=8" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json"
```

**Example Response:**
```json
{
  "status": "success",
  "message": "Dashboard data retrieved successfully",
  "data": {
    "stats": {
      "total_imd": 150,
      "sc_percentage": 35.5,
      "spontan_percentage": 64.5,
      "avg_duration": 42.5
    },
    "charts": {
      "imd_by_cara_persalinan": [
        {
          "name": "SC",
          "value": 45,
          "color": "#ef4444"
        },
        {
          "name": "Spontan",
          "value": 105,
          "color": "#10b981"
        }
      ],
      "imd_by_waktu": [
        {
          "name": "15 menit",
          "value": 20,
          "color": "#ef4444"
        },
        {
          "name": "30 menit",
          "value": 45,
          "color": "#f59e0b"
        },
        {
          "name": "45 menit",
          "value": 50,
          "color": "#3b82f6"
        },
        {
          "name": "60 menit",
          "value": 35,
          "color": "#10b981"
        }
      ],
      "monthly_trend": [
        {
          "month": "Sep 2024",
          "value": 8
        },
        {
          "month": "Oct 2024",
          "value": 12
        },
        {
          "month": "Nov 2024",
          "value": 15
        },
        {
          "month": "Dec 2024",
          "value": 18
        },
        {
          "month": "Jan 2025",
          "value": 22
        },
        {
          "month": "Feb 2025",
          "value": 25
        },
        {
          "month": "Mar 2025",
          "value": 28
        },
        {
          "month": "Apr 2025",
          "value": 30
        },
        {
          "month": "May 2025",
          "value": 32
        },
        {
          "month": "Jun 2025",
          "value": 35
        },
        {
          "month": "Jul 2025",
          "value": 38
        },
        {
          "month": "Aug 2025",
          "value": 40
        }
      ],
      "age_distribution": [
        {
          "name": "< 20 tahun",
          "value": 15
        },
        {
          "name": "20-25 tahun",
          "value": 45
        },
        {
          "name": "26-30 tahun",
          "value": 55
        },
        {
          "name": "31-35 tahun",
          "value": 25
        },
        {
          "name": "36-40 tahun",
          "value": 8
        },
        {
          "name": "> 40 tahun",
          "value": 2
        }
      ]
    },
    "recent_imds": [
      {
        "id": "01HXYZ123456789",
        "nama_pasien": "Siti Nurhaliza",
        "no_rm": "RM001234",
        "cara_persalinan": "SC",
        "waktu_imd": "30 menit",
        "tanggal_imd": "2025-08-11",
        "nama_petugas": "Dr. Ahmad"
      },
      {
        "id": "01HXYZ123456790",
        "nama_pasien": "Dewi Sartika",
        "no_rm": "RM001235",
        "cara_persalinan": "Spontan",
        "waktu_imd": "45 menit",
        "tanggal_imd": "2025-08-10",
        "nama_petugas": "Dr. Sari"
      }
    ],
    "filters": {
      "year": 2025,
      "month": 8,
      "cara_persalinan": null
    },
    "available_years": [2020, 2021, 2022, 2023, 2024, 2025]
  }
}
```

### 2. Get Statistics Only (Lightweight)
```http
GET /api/dashboard/stats
```

**Query Parameters:** Same as above

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/dashboard/stats?year=2025" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json"
```

**Example Response:**
```json
{
  "status": "success",
  "message": "Statistics retrieved successfully",
  "data": {
    "total_imd": 150,
    "sc_percentage": 35.5,
    "spontan_percentage": 64.5,
    "avg_duration": 42.5
  }
}
```

### 3. Get Charts Data Only
```http
GET /api/dashboard/charts
```

**Query Parameters:**
- All previous filters, plus:
- `chart_type` (optional): Specific chart ("cara_persalinan", "waktu", "monthly_trend", "age_distribution")

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/dashboard/charts?chart_type=cara_persalinan" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json"
```

**Example Response:**
```json
{
  "status": "success",
  "message": "Charts data retrieved successfully",
  "data": {
    "imd_by_cara_persalinan": [
      {
        "name": "SC",
        "value": 45,
        "color": "#ef4444"
      },
      {
        "name": "Spontan",
        "value": 105,
        "color": "#10b981"
      }
    ]
  }
}
```

## Error Responses

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 422 Validation Error
```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "year": ["The year must be between 2020 and 2030."],
    "month": ["The month must be between 1 and 12."]
  }
}
```

### 500 Server Error
```json
{
  "status": "error",
  "message": "Failed to retrieve dashboard data",
  "error": "Database connection failed"
}
```

## Flutter Integration Examples

### Using Dio HTTP Client

```dart
import 'package:dio/dio.dart';

class DashboardService {
  final Dio _dio = Dio();
  final String baseUrl = 'http://localhost:8000/api';
  
  DashboardService() {
    _dio.options.headers['Accept'] = 'application/json';
  }
  
  void setAuthToken(String token) {
    _dio.options.headers['Authorization'] = 'Bearer $token';
  }
  
  Future<DashboardData> getDashboard({
    int? year,
    int? month,
    String? caraPersalinan,
  }) async {
    try {
      final response = await _dio.get(
        '$baseUrl/dashboard',
        queryParameters: {
          if (year != null) 'year': year,
          if (month != null) 'month': month,
          if (caraPersalinan != null) 'cara_persalinan': caraPersalinan,
        },
      );
      
      if (response.data['status'] == 'success') {
        return DashboardData.fromJson(response.data['data']);
      } else {
        throw Exception('Failed to load dashboard data');
      }
    } on DioError catch (e) {
      if (e.response?.statusCode == 401) {
        throw Exception('Unauthorized: Please login again');
      }
      throw Exception('Network error: ${e.message}');
    }
  }
  
  Future<DashboardStats> getStats({
    int? year,
    int? month,
    String? caraPersalinan,
  }) async {
    try {
      final response = await _dio.get(
        '$baseUrl/dashboard/stats',
        queryParameters: {
          if (year != null) 'year': year,
          if (month != null) 'month': month,
          if (caraPersalinan != null) 'cara_persalinan': caraPersalinan,
        },
      );
      
      if (response.data['status'] == 'success') {
        return DashboardStats.fromJson(response.data['data']);
      } else {
        throw Exception('Failed to load statistics');
      }
    } catch (e) {
      throw Exception('Error: $e');
    }
  }
}
```

### Data Models

```dart
class DashboardData {
  final DashboardStats stats;
  final DashboardCharts charts;
  final List<RecentImd> recentImds;
  final DashboardFilters filters;
  final List<int> availableYears;

  DashboardData({
    required this.stats,
    required this.charts,
    required this.recentImds,
    required this.filters,
    required this.availableYears,
  });

  factory DashboardData.fromJson(Map<String, dynamic> json) {
    return DashboardData(
      stats: DashboardStats.fromJson(json['stats']),
      charts: DashboardCharts.fromJson(json['charts']),
      recentImds: (json['recent_imds'] as List)
          .map((item) => RecentImd.fromJson(item))
          .toList(),
      filters: DashboardFilters.fromJson(json['filters']),
      availableYears: List<int>.from(json['available_years']),
    );
  }
}

class DashboardStats {
  final int totalImd;
  final double scPercentage;
  final double spontanPercentage;
  final double avgDuration;

  DashboardStats({
    required this.totalImd,
    required this.scPercentage,
    required this.spontanPercentage,
    required this.avgDuration,
  });

  factory DashboardStats.fromJson(Map<String, dynamic> json) {
    return DashboardStats(
      totalImd: json['total_imd'],
      scPercentage: json['sc_percentage'].toDouble(),
      spontanPercentage: json['spontan_percentage'].toDouble(),
      avgDuration: json['avg_duration'].toDouble(),
    );
  }
}

class ChartData {
  final String name;
  final int value;
  final String color;

  ChartData({
    required this.name,
    required this.value,
    required this.color,
  });

  factory ChartData.fromJson(Map<String, dynamic> json) {
    return ChartData(
      name: json['name'],
      value: json['value'],
      color: json['color'],
    );
  }
}

class MonthlyTrendData {
  final String month;
  final int value;

  MonthlyTrendData({
    required this.month,
    required this.value,
  });

  factory MonthlyTrendData.fromJson(Map<String, dynamic> json) {
    return MonthlyTrendData(
      month: json['month'],
      value: json['value'],
    );
  }
}

class DashboardCharts {
  final List<ChartData> imdByCaraPersalinan;
  final List<ChartData> imdByWaktu;
  final List<MonthlyTrendData> monthlyTrend;
  final List<ChartData> ageDistribution;

  DashboardCharts({
    required this.imdByCaraPersalinan,
    required this.imdByWaktu,
    required this.monthlyTrend,
    required this.ageDistribution,
  });

  factory DashboardCharts.fromJson(Map<String, dynamic> json) {
    return DashboardCharts(
      imdByCaraPersalinan: (json['imd_by_cara_persalinan'] as List)
          .map((item) => ChartData.fromJson(item))
          .toList(),
      imdByWaktu: (json['imd_by_waktu'] as List)
          .map((item) => ChartData.fromJson(item))
          .toList(),
      monthlyTrend: (json['monthly_trend'] as List)
          .map((item) => MonthlyTrendData.fromJson(item))
          .toList(),
      ageDistribution: (json['age_distribution'] as List)
          .map((item) => ChartData.fromJson(item))
          .toList(),
    );
  }
}
```

## Testing

### Using curl
```bash
# Get complete dashboard
curl -X GET "http://localhost:8000/api/dashboard" \
  -H "Authorization: Bearer 1|your-token" \
  -H "Accept: application/json"

# Get filtered data
curl -X GET "http://localhost:8000/api/dashboard?year=2025&month=8&cara_persalinan=SC" \
  -H "Authorization: Bearer 1|your-token" \
  -H "Accept: application/json"

# Get only statistics
curl -X GET "http://localhost:8000/api/dashboard/stats" \
  -H "Authorization: Bearer 1|your-token" \
  -H "Accept: application/json"

# Get specific chart
curl -X GET "http://localhost:8000/api/dashboard/charts?chart_type=cara_persalinan" \
  -H "Authorization: Bearer 1|your-token" \
  -H "Accept: application/json"
```

### Using Postman
1. Set Authorization type to "Bearer Token"
2. Add your Sanctum token
3. Set Accept header to "application/json"
4. Use GET method with appropriate endpoint
5. Add query parameters as needed

## Notes for Flutter Development

1. **Caching**: Consider implementing local caching for dashboard data to improve performance
2. **Refresh Strategy**: Implement pull-to-refresh for real-time data updates
3. **Error Handling**: Handle network errors gracefully with retry mechanisms
4. **Offline Support**: Cache critical statistics for offline viewing
5. **Performance**: Use `/stats` endpoint for quick overview updates
6. **Charts**: Use `chart_type` parameter to load specific charts as needed
7. **Filters**: Persist user filter preferences locally
8. **Security**: Store tokens securely using secure storage packages

## API Response Time Optimization

- `/api/dashboard/stats` - Fastest (1-2ms)
- `/api/dashboard/charts?chart_type=specific` - Medium (5-10ms)  
- `/api/dashboard/charts` - Medium (10-20ms)
- `/api/dashboard` - Comprehensive (20-50ms)

Choose the appropriate endpoint based on your UI needs and performance requirements.
