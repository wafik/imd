# Ask AI API Documentation

## Overview
API Ask AI untuk aplikasi IMD yang memungkinkan pengguna mengajukan pertanyaan dalam bahasa natural dan mendapatkan jawaban cerdas dari AI. API ini dapat memberikan jawaban langsung atau mengeksekusi query SQL untuk analisis data.

## Base URL
```
http://localhost:8000/api
```

## Authentication
Semua endpoint Ask AI memerlukan authentication menggunakan Laravel Sanctum Bearer Token:
```
Authorization: Bearer {your-token}
```

## Endpoints

### 1. Ask Question to AI
```http
POST /api/ask-ai/question
```

**Description:** Submit pertanyaan dalam bahasa natural ke AI dan dapatkan jawaban cerdas.

**Request Body:**
```json
{
  "question": "Berapa total data IMD yang tercatat bulan ini?"
}
```

**Request Validation:**
- `question` (required, string, max: 1000): Pertanyaan dalam bahasa natural

**Example Request:**
```bash
curl -X POST "http://localhost:8000/api/ask-ai/question" \
  -H "Authorization: Bearer your-token-here" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "question": "Berapa total data IMD yang tercatat bulan ini?"
  }'
```

**Example Response (Text Answer):**
```json
{
  "success": true,
  "question": "Berapa total data IMD yang tercatat bulan ini?",
  "is_query": false,
  "answer": "Berdasarkan data yang tersedia, total data IMD yang tercatat bulan ini adalah 45 records.",
  "query_result": null,
  "timestamp": "2025-08-11T10:30:00.000000Z"
}
```

**Example Response (With Query Execution):**
```json
{
  "success": true,
  "question": "Tampilkan 5 data IMD terbaru",
  "is_query": true,
  "answer": "Data berhasil diambil. Ditemukan 5 record yang sesuai dengan pertanyaan Anda.",
  "query_result": {
    "success": true,
    "data": [
      {
        "id": "01HXYZ123456789",
        "nama_pasien": "Siti Nurhaliza",
        "cara_persalinan": "SC",
        "tanggal_imd": "2025-08-11"
      },
      {
        "id": "01HXYZ123456790",
        "nama_pasien": "Dewi Sartika",
        "cara_persalinan": "Spontan",
        "tanggal_imd": "2025-08-10"
      }
    ],
    "count": 5,
    "query": "SELECT id, nama_pasien, cara_persalinan, tanggal_imd FROM imds ORDER BY tanggal_imd DESC LIMIT 5"
  },
  "timestamp": "2025-08-11T10:30:00.000000Z"
}
```

### 2. Execute SQL Query
```http
POST /api/ask-ai/execute-query
```

**Description:** Eksekusi query SQL SELECT secara langsung.

**Request Body:**
```json
{
  "query": "SELECT COUNT(*) as total FROM imds WHERE cara_persalinan = 'SC'"
}
```

**Security Notes:**
- Hanya query SELECT yang diizinkan
- Keyword berbahaya (DROP, UPDATE, INSERT, dll.) akan ditolak
- Query divalidasi sebelum eksekusi

**Example Request:**
```bash
curl -X POST "http://localhost:8000/api/ask-ai/execute-query" \
  -H "Authorization: Bearer your-token-here" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "query": "SELECT cara_persalinan, COUNT(*) as jumlah FROM imds GROUP BY cara_persalinan"
  }'
```

**Example Response:**
```json
{
  "success": true,
  "data": [
    {
      "cara_persalinan": "SC",
      "jumlah": 45
    },
    {
      "cara_persalinan": "Spontan",
      "jumlah": 105
    }
  ],
  "count": 2,
  "query": "SELECT cara_persalinan, COUNT(*) as jumlah FROM imds GROUP BY cara_persalinan"
}
```

### 3. Get Sample Questions
```http
GET /api/ask-ai/samples
```

**Description:** Dapatkan daftar contoh pertanyaan yang bisa diajukan ke AI.

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/ask-ai/samples" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json"
```

**Example Response:**
```json
{
  "success": true,
  "data": [
    "Berapa total data IMD yang tercatat?",
    "Tampilkan data IMD untuk bulan ini",
    "Berapa rata-rata durasi IMD?",
    "Tampilkan distribusi cara persalinan",
    "Data IMD dengan durasi paling lama",
    "Berapa ibu yang melakukan IMD lebih dari 60 menit?",
    "Tampilkan trend IMD per bulan tahun ini",
    "Siapa petugas yang paling sering menangani IMD?",
    "Berapa persentase persalinan SC vs Spontan?",
    "Tampilkan 5 data IMD terbaru",
    "Berapa rata-rata waktu IMD berdasarkan cara persalinan?",
    "Data IMD dengan waktu kurang dari 30 menit",
    "Tampilkan jumlah IMD per petugas medis",
    "Berapa total IMD bulan lalu dibanding bulan ini?",
    "Data pasien yang lahir hari ini"
  ],
  "count": 15
}
```

### 4. Get Database Schema
```http
GET /api/ask-ai/schema
```

**Description:** Dapatkan informasi skema database untuk membantu konstruksi query.

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/ask-ai/schema" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json"
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "table_name": "imds",
    "columns": [
      {
        "name": "id",
        "type": "char(26)",
        "description": "ULID primary key"
      },
      {
        "name": "nama_pasien",
        "type": "varchar(255)",
        "description": "Nama lengkap pasien/ibu"
      },
      {
        "name": "alamat",
        "type": "text",
        "description": "Alamat lengkap pasien"
      },
      {
        "name": "no_rm",
        "type": "varchar(50)",
        "description": "Nomor rekam medis"
      },
      {
        "name": "tanggal_lahir",
        "type": "date",
        "description": "Tanggal lahir bayi"
      },
      {
        "name": "cara_persalinan",
        "type": "enum(SC,Spontan)",
        "description": "Metode persalinan: SC (Sectio Caesarea) atau Spontan"
      },
      {
        "name": "tanggal_imd",
        "type": "date",
        "description": "Tanggal pelaksanaan IMD"
      },
      {
        "name": "waktu_imd",
        "type": "enum(15 menit,30 menit,45 menit,60 menit)",
        "description": "Durasi pelaksanaan IMD"
      },
      {
        "name": "nama_petugas",
        "type": "varchar(255)",
        "description": "Nama petugas medis yang menangani"
      },
      {
        "name": "created_at",
        "type": "timestamp",
        "description": "Waktu pembuatan record"
      },
      {
        "name": "updated_at",
        "type": "timestamp",
        "description": "Waktu update terakhir record"
      },
      {
        "name": "deleted_at",
        "type": "timestamp nullable",
        "description": "Waktu soft delete (NULL jika aktif)"
      }
    ],
    "sample_queries": [
      "SELECT COUNT(*) as total FROM imds WHERE deleted_at IS NULL",
      "SELECT cara_persalinan, COUNT(*) as jumlah FROM imds GROUP BY cara_persalinan",
      "SELECT waktu_imd, COUNT(*) as jumlah FROM imds GROUP BY waktu_imd",
      "SELECT nama_petugas, COUNT(*) as jumlah_pasien FROM imds GROUP BY nama_petugas",
      "SELECT DATE(tanggal_imd) as tanggal, COUNT(*) as jumlah FROM imds GROUP BY DATE(tanggal_imd)",
      "SELECT * FROM imds WHERE tanggal_imd >= CURDATE() - INTERVAL 30 DAY",
      "SELECT AVG(CAST(SUBSTRING_INDEX(waktu_imd, ' ', 1) AS UNSIGNED)) as rata_rata_menit FROM imds"
    ]
  }
}
```

## Error Responses

### 400 Bad Request
```json
{
  "success": false,
  "message": "The question field is required.",
  "errors": {
    "question": ["The question field is required."]
  }
}
```

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 500 Server Error
```json
{
  "success": false,
  "message": "Terjadi kesalahan saat menghubungi AI",
  "error": "Connection timeout to AI service"
}
```

### Query Security Error
```json
{
  "success": false,
  "error": "Hanya query SELECT yang diizinkan untuk keamanan",
  "query": "UPDATE imds SET..."
}
```

## Flutter Integration

### Service Class
```dart
import 'package:dio/dio.dart';

class AskAIService {
  final Dio _dio = Dio();
  final String baseUrl = 'http://localhost:8000/api';
  
  AskAIService() {
    _dio.options.headers['Accept'] = 'application/json';
    _dio.options.headers['Content-Type'] = 'application/json';
  }
  
  void setAuthToken(String token) {
    _dio.options.headers['Authorization'] = 'Bearer $token';
  }
  
  Future<AskAIResponse> askQuestion(String question) async {
    try {
      final response = await _dio.post(
        '$baseUrl/ask-ai/question',
        data: {'question': question},
      );
      
      if (response.data['success'] == true) {
        return AskAIResponse.fromJson(response.data);
      } else {
        throw Exception('Failed to get AI response');
      }
    } on DioError catch (e) {
      if (e.response?.statusCode == 401) {
        throw Exception('Unauthorized: Please login again');
      } else if (e.response?.statusCode == 400) {
        throw Exception('Invalid question: ${e.response?.data['message']}');
      }
      throw Exception('Network error: ${e.message}');
    }
  }
  
  Future<QueryResult> executeQuery(String query) async {
    try {
      final response = await _dio.post(
        '$baseUrl/ask-ai/execute-query',
        data: {'query': query},
      );
      
      return QueryResult.fromJson(response.data);
    } catch (e) {
      throw Exception('Query execution failed: $e');
    }
  }
  
  Future<List<String>> getSampleQuestions() async {
    try {
      final response = await _dio.get('$baseUrl/ask-ai/samples');
      
      if (response.data['success'] == true) {
        return List<String>.from(response.data['data']);
      } else {
        throw Exception('Failed to load sample questions');
      }
    } catch (e) {
      throw Exception('Error: $e');
    }
  }
  
  Future<DatabaseSchema> getSchema() async {
    try {
      final response = await _dio.get('$baseUrl/ask-ai/schema');
      
      if (response.data['success'] == true) {
        return DatabaseSchema.fromJson(response.data['data']);
      } else {
        throw Exception('Failed to load schema');
      }
    } catch (e) {
      throw Exception('Error: $e');
    }
  }
}
```

### Data Models
```dart
class AskAIResponse {
  final bool success;
  final String question;
  final bool isQuery;
  final String answer;
  final QueryResult? queryResult;
  final DateTime timestamp;

  AskAIResponse({
    required this.success,
    required this.question,
    required this.isQuery,
    required this.answer,
    this.queryResult,
    required this.timestamp,
  });

  factory AskAIResponse.fromJson(Map<String, dynamic> json) {
    return AskAIResponse(
      success: json['success'],
      question: json['question'],
      isQuery: json['is_query'],
      answer: json['answer'],
      queryResult: json['query_result'] != null 
          ? QueryResult.fromJson(json['query_result']) 
          : null,
      timestamp: DateTime.parse(json['timestamp']),
    );
  }
}

class QueryResult {
  final bool success;
  final List<Map<String, dynamic>>? data;
  final int? count;
  final String? query;
  final String? error;

  QueryResult({
    required this.success,
    this.data,
    this.count,
    this.query,
    this.error,
  });

  factory QueryResult.fromJson(Map<String, dynamic> json) {
    return QueryResult(
      success: json['success'],
      data: json['data'] != null 
          ? List<Map<String, dynamic>>.from(json['data']) 
          : null,
      count: json['count'],
      query: json['query'],
      error: json['error'],
    );
  }
}

class DatabaseSchema {
  final String tableName;
  final List<ColumnInfo> columns;
  final List<String> sampleQueries;

  DatabaseSchema({
    required this.tableName,
    required this.columns,
    required this.sampleQueries,
  });

  factory DatabaseSchema.fromJson(Map<String, dynamic> json) {
    return DatabaseSchema(
      tableName: json['table_name'],
      columns: (json['columns'] as List)
          .map((col) => ColumnInfo.fromJson(col))
          .toList(),
      sampleQueries: List<String>.from(json['sample_queries']),
    );
  }
}

class ColumnInfo {
  final String name;
  final String type;
  final String description;

  ColumnInfo({
    required this.name,
    required this.type,
    required this.description,
  });

  factory ColumnInfo.fromJson(Map<String, dynamic> json) {
    return ColumnInfo(
      name: json['name'],
      type: json['type'],
      description: json['description'],
    );
  }
}
```

### Usage Example in Flutter Widget
```dart
class AskAIScreen extends StatefulWidget {
  @override
  _AskAIScreenState createState() => _AskAIScreenState();
}

class _AskAIScreenState extends State<AskAIScreen> {
  final AskAIService _aiService = AskAIService();
  final TextEditingController _questionController = TextEditingController();
  AskAIResponse? _lastResponse;
  bool _isLoading = false;
  List<String> _sampleQuestions = [];

  @override
  void initState() {
    super.initState();
    _loadSampleQuestions();
  }

  void _loadSampleQuestions() async {
    try {
      final samples = await _aiService.getSampleQuestions();
      setState(() {
        _sampleQuestions = samples;
      });
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Failed to load samples: $e')),
      );
    }
  }

  void _askQuestion() async {
    if (_questionController.text.trim().isEmpty) return;

    setState(() {
      _isLoading = true;
    });

    try {
      final response = await _aiService.askQuestion(_questionController.text);
      setState(() {
        _lastResponse = response;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _isLoading = false;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error: $e')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Ask AI')),
      body: Padding(
        padding: EdgeInsets.all(16),
        child: Column(
          children: [
            // Question input
            TextField(
              controller: _questionController,
              decoration: InputDecoration(
                labelText: 'Tanyakan sesuatu tentang data IMD...',
                suffixIcon: IconButton(
                  icon: Icon(Icons.send),
                  onPressed: _isLoading ? null : _askQuestion,
                ),
              ),
              maxLines: 3,
              onSubmitted: (_) => _askQuestion(),
            ),
            
            SizedBox(height: 16),
            
            // Sample questions
            if (_sampleQuestions.isNotEmpty) ...[
              Text('Contoh Pertanyaan:', 
                style: Theme.of(context).textTheme.subtitle1),
              SizedBox(height: 8),
              Wrap(
                spacing: 8,
                runSpacing: 4,
                children: _sampleQuestions.take(5).map((question) =>
                  ActionChip(
                    label: Text(question, overflow: TextOverflow.ellipsis),
                    onPressed: () {
                      _questionController.text = question;
                      _askQuestion();
                    },
                  ),
                ).toList(),
              ),
              SizedBox(height: 16),
            ],
            
            // Response area
            Expanded(
              child: _isLoading
                  ? Center(child: CircularProgressIndicator())
                  : _lastResponse != null
                      ? _buildResponseCard()
                      : Center(child: Text('Ajukan pertanyaan untuk memulai')),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildResponseCard() {
    if (_lastResponse == null) return SizedBox();

    return Card(
      child: Padding(
        padding: EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Q: ${_lastResponse!.question}',
              style: TextStyle(fontWeight: FontWeight.bold)),
            SizedBox(height: 8),
            Text('A: ${_lastResponse!.answer}'),
            
            if (_lastResponse!.isQuery && _lastResponse!.queryResult != null) ...[
              SizedBox(height: 16),
              Text('Data Result:', style: TextStyle(fontWeight: FontWeight.bold)),
              SizedBox(height: 8),
              if (_lastResponse!.queryResult!.success) ...[
                Text('Found ${_lastResponse!.queryResult!.count} records'),
                SizedBox(height: 8),
                Expanded(
                  child: SingleChildScrollView(
                    child: _buildDataTable(_lastResponse!.queryResult!.data!),
                  ),
                ),
              ] else ...[
                Text('Error: ${_lastResponse!.queryResult!.error}',
                  style: TextStyle(color: Colors.red)),
              ],
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildDataTable(List<Map<String, dynamic>> data) {
    if (data.isEmpty) return Text('No data found');

    final columns = data.first.keys.toList();
    
    return DataTable(
      columns: columns.map((col) => DataColumn(label: Text(col))).toList(),
      rows: data.map((row) =>
        DataRow(
          cells: columns.map((col) =>
            DataCell(Text(row[col]?.toString() ?? '')),
          ).toList(),
        ),
      ).toList(),
    );
  }
}
```

## Testing

### Using curl
```bash
# Ask a question
curl -X POST "http://localhost:8000/api/ask-ai/question" \
  -H "Authorization: Bearer 1|your-token" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"question": "Berapa total data IMD?"}'

# Execute query
curl -X POST "http://localhost:8000/api/ask-ai/execute-query" \
  -H "Authorization: Bearer 1|your-token" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"query": "SELECT COUNT(*) as total FROM imds"}'

# Get samples
curl -X GET "http://localhost:8000/api/ask-ai/samples" \
  -H "Authorization: Bearer 1|your-token" \
  -H "Accept: application/json"

# Get schema
curl -X GET "http://localhost:8000/api/ask-ai/schema" \
  -H "Authorization: Bearer 1|your-token" \
  -H "Accept: application/json"
```

## Security Features

1. **Authentication Required**: Semua endpoint memerlukan valid Sanctum token
2. **SQL Injection Protection**: Hanya SELECT query yang diizinkan
3. **Keyword Filtering**: Keyword berbahaya diblokir (DROP, UPDATE, INSERT, dll.)
4. **Input Validation**: Validasi panjang dan format input
5. **Error Handling**: Error message yang informatif tanpa expose sensitive data
6. **Timeout Protection**: Request timeout untuk prevent hanging requests

## Performance Tips

1. **Caching**: Cache sample questions dan schema di Flutter
2. **Debouncing**: Implement debouncing untuk menghindari spam requests
3. **Pagination**: Untuk query dengan hasil banyak, gunakan LIMIT
4. **Background Processing**: Jalankan query berat di background thread
5. **Connection Pooling**: Gunakan connection pooling untuk HTTP requests

## Best Practices for Flutter

1. **Loading States**: Tampilkan loading indicator saat request
2. **Error Handling**: Handle semua possible error scenarios
3. **Offline Mode**: Cache recent responses untuk offline viewing
4. **User Experience**: Provide suggested questions untuk user guidance
5. **Data Visualization**: Format hasil query dalam table atau chart yang mudah dibaca
