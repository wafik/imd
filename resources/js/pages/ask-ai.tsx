import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { Head, useForm } from '@inertiajs/react';
import { Brain, CheckCircle, Database, Info, Lightbulb, Loader2, MessageSquare, Send, Sparkles } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';

const sampleQuestions = [
    'Berapa total data IMD yang tercatat?',
    'Tampilkan data IMD untuk bulan ini',
    'Berapa rata-rata durasi IMD?',
    'Tampilkan distribusi cara persalinan',
    'Data IMD dengan durasi paling lama',
    'Berapa ibu yang melakukan IMD lebih dari 60 menit?',
    'Siapa petugas yang paling sering menangani IMD?',
    'Berapa bayi laki-laki dan perempuan yang sudah IMD?',
];

interface QueryResult {
    success: boolean;
    data?: Record<string, unknown>[];
    error?: string;
    count?: number;
}

interface AIResponse {
    success: boolean;
    question: string;
    is_query: boolean;
    answer: string;
    query_result?: QueryResult;
    timestamp: string;
}

export default function AskAI() {
    const [aiResponse, setAiResponse] = useState<AIResponse | null>(null);
    const [isLoading, setIsLoading] = useState(false);

    const { data, setData } = useForm({
        question: '',
    });

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!data.question.trim()) {
            toast.error('Silakan masukkan pertanyaan Anda');
            return;
        }

        setIsLoading(true);
        setAiResponse(null);

        try {
            const response = await fetch('/api/ask-ai/question', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    Accept: 'application/json',
                },
                body: JSON.stringify({
                    question: data.question,
                }),
            });

            const result = await response.json();

            if (result.success) {
                setAiResponse(result);
                if (result.is_query) {
                    toast.success('Data berhasil ditemukan!');
                } else {
                    toast.success('Jawaban berhasil diterima!');
                }
            } else {
                toast.error(result.message || 'Terjadi kesalahan');
            }
        } catch {
            toast.error('Terjadi kesalahan saat menghubungi AI');
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <AppLayout>
            <Head title="Tanya AI" />

            <div className="py-6">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="space-y-6">
                        {/* Header */}
                        <div className="rounded-2xl bg-gradient-to-r from-purple-600 to-blue-600 p-8 text-white">
                            <div className="flex items-center space-x-4">
                                <div className="rounded-xl bg-white/20 p-3">
                                    <Brain className="h-8 w-8" />
                                </div>
                                <div>
                                    <h1 className="text-3xl font-bold">Tanya AI</h1>
                                    <p className="mt-2 text-purple-100">
                                        Ajukan pertanyaan tentang data IMD dan dapatkan jawaban atau informasi data yang Anda butuhkan
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                            {/* Input Section */}
                            <Card className="shadow-lg">
                                <CardHeader className="bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20">
                                    <CardTitle className="flex items-center space-x-2">
                                        <MessageSquare className="h-5 w-5 text-blue-600" />
                                        <span>Pertanyaan Anda</span>
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="p-6">
                                    <form onSubmit={handleSubmit} className="space-y-4">
                                        <div>
                                            <textarea
                                                placeholder="Contoh: Tampilkan semua data pasien dengan persalinan caesar, atau Berapa total pasien yang melakukan IMD bulan ini?"
                                                value={data.question}
                                                onChange={(e) => setData('question', e.target.value)}
                                                className="min-h-[120px] w-full resize-none rounded-lg border border-gray-300 p-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                                disabled={isLoading}
                                            />
                                        </div>

                                        {/* Sample Questions */}
                                        <div className="space-y-2">
                                            <div className="flex items-center space-x-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                <Lightbulb className="h-4 w-4 text-yellow-500" />
                                                <span>Contoh Pertanyaan:</span>
                                            </div>
                                            <div className="flex flex-wrap gap-2">
                                                {sampleQuestions.slice(0, 4).map((question, index) => (
                                                    <button
                                                        key={index}
                                                        type="button"
                                                        onClick={() => setData('question', question)}
                                                        className="rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-600 transition-colors hover:bg-blue-100 hover:text-blue-700 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-blue-900 dark:hover:text-blue-300"
                                                        disabled={isLoading}
                                                    >
                                                        {question}
                                                    </button>
                                                ))}
                                            </div>
                                        </div>

                                        <div className="flex items-center justify-between">
                                            <div className="flex items-center space-x-2 text-sm text-gray-500">
                                                <Info className="h-4 w-4" />
                                                <span>AI akan menganalisis dan memberikan respons yang sesuai</span>
                                            </div>

                                            <Button
                                                type="submit"
                                                disabled={isLoading || !data.question.trim()}
                                                className="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700"
                                            >
                                                {isLoading ? (
                                                    <>
                                                        <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                                        Memproses...
                                                    </>
                                                ) : (
                                                    <>
                                                        <Send className="mr-2 h-4 w-4" />
                                                        Tanya AI
                                                    </>
                                                )}
                                            </Button>
                                        </div>
                                    </form>

                                    {/* Quick Questions */}
                                    <div className="mt-6">
                                        <h4 className="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">Pertanyaan Cepat:</h4>
                                        <div className="flex flex-wrap gap-2">
                                            {[
                                                'Tampilkan semua data pasien caesar',
                                                'Berapa total pasien IMD hari ini?',
                                                'Data pasien dengan waktu IMD 60 menit',
                                                'Statistik cara persalinan',
                                            ].map((question) => (
                                                <Badge
                                                    key={question}
                                                    variant="secondary"
                                                    className="cursor-pointer transition-colors hover:bg-blue-100 dark:hover:bg-blue-900/50"
                                                    onClick={() => setData('question', question)}
                                                >
                                                    {question}
                                                </Badge>
                                            ))}
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Response Section */}
                            <Card className="shadow-lg">
                                <CardHeader className="bg-gradient-to-r from-green-50 to-blue-50 dark:from-green-900/20 dark:to-blue-900/20">
                                    <CardTitle className="flex items-center space-x-2">
                                        <Sparkles className="h-5 w-5 text-green-600" />
                                        <span>Respons AI</span>
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="p-6">
                                    {!aiResponse ? (
                                        <div className="py-12 text-center text-gray-500">
                                            <Brain className="mx-auto mb-4 h-12 w-12 opacity-50" />
                                            <p>Ajukan pertanyaan untuk mendapatkan respons dari AI</p>
                                        </div>
                                    ) : (
                                        <div className="space-y-4">
                                            {aiResponse.is_query ? (
                                                <div className="space-y-4">
                                                    <div className="flex items-center space-x-2">
                                                        <Database className="h-5 w-5 text-blue-600" />
                                                        <span className="font-medium">Hasil Data:</span>
                                                    </div>

                                                    {/* For queries, show query results status */}
                                                    {aiResponse.query_result?.success ? (
                                                        <div className="rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
                                                            <p className="text-blue-800 dark:text-blue-200">
                                                                Data berhasil ditemukan. Lihat hasil di bawah.
                                                            </p>
                                                        </div>
                                                    ) : (
                                                        <div className="rounded-lg bg-red-50 p-4 dark:bg-red-900/20">
                                                            <p className="text-red-800 dark:text-red-200">
                                                                {aiResponse.query_result?.error || 'Tidak dapat memproses data'}
                                                            </p>
                                                        </div>
                                                    )}
                                                </div>
                                            ) : (
                                                <div className="space-y-4">
                                                    <div className="flex items-center space-x-2">
                                                        <MessageSquare className="h-5 w-5 text-green-600" />
                                                        <span className="font-medium">Jawaban AI:</span>
                                                    </div>

                                                    {/* For non-queries, show the original answer from n8n */}
                                                    <div className="rounded-lg bg-green-50 p-4 dark:bg-green-900/20">
                                                        <p className="whitespace-pre-wrap text-green-800 dark:text-green-200">{aiResponse.answer}</p>
                                                    </div>
                                                </div>
                                            )}
                                        </div>
                                    )}
                                </CardContent>
                            </Card>
                        </div>

                        {/* Query Results - only show for successful queries */}
                        {aiResponse?.is_query && aiResponse.query_result?.success && (
                            <Card className="shadow-lg">
                                <CardHeader>
                                    <CardTitle className="flex items-center space-x-2">
                                        <CheckCircle className="h-5 w-5 text-green-600" />
                                        <span>Hasil Data IMD</span>
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-4">
                                        <div className="flex items-center justify-between">
                                            <p className="text-sm text-gray-600 dark:text-gray-400">
                                                {aiResponse.query_result.count === 1
                                                    ? 'Ditemukan 1 data'
                                                    : `Ditemukan ${aiResponse.query_result.count || 0} data`}
                                            </p>
                                        </div>

                                        {aiResponse.query_result.data && aiResponse.query_result.data.length > 0 ? (
                                            <div className="overflow-x-auto">
                                                <table className="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                                    <thead className="bg-gray-50 dark:bg-gray-800">
                                                        <tr>
                                                            {Object.keys(aiResponse.query_result.data[0]).map((key) => (
                                                                <th
                                                                    key={key}
                                                                    className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400"
                                                                >
                                                                    {/* Convert database column names to readable labels */}
                                                                    {key === 'nama_ibu'
                                                                        ? 'Nama Ibu'
                                                                        : key === 'umur_ibu'
                                                                          ? 'Umur Ibu'
                                                                          : key === 'nama_bayi'
                                                                            ? 'Nama Bayi'
                                                                            : key === 'jenis_kelamin'
                                                                              ? 'Jenis Kelamin'
                                                                              : key === 'tanggal_lahir'
                                                                                ? 'Tanggal Lahir'
                                                                                : key === 'berat_badan'
                                                                                  ? 'Berat Badan'
                                                                                  : key === 'cara_persalinan'
                                                                                    ? 'Cara Persalinan'
                                                                                    : key === 'tempat_persalinan'
                                                                                      ? 'Tempat Persalinan'
                                                                                      : key === 'nama_petugas'
                                                                                        ? 'Nama Petugas'
                                                                                        : key === 'waktu_imd'
                                                                                          ? 'Waktu IMD'
                                                                                          : key === 'total'
                                                                                            ? 'Total'
                                                                                            : key === 'count'
                                                                                              ? 'Jumlah'
                                                                                              : key === 'rata_rata'
                                                                                                ? 'Rata-rata'
                                                                                                : key
                                                                                                      .replace(/_/g, ' ')
                                                                                                      .replace(/\b\w/g, (l) => l.toUpperCase())}
                                                                </th>
                                                            ))}
                                                        </tr>
                                                    </thead>
                                                    <tbody className="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                                                        {aiResponse.query_result.data.slice(0, 10).map((row, index) => (
                                                            <tr key={index} className="hover:bg-gray-50 dark:hover:bg-gray-800">
                                                                {Object.values(row).map((value, i) => (
                                                                    <td
                                                                        key={i}
                                                                        className="px-6 py-4 text-sm whitespace-nowrap text-gray-900 dark:text-gray-100"
                                                                    >
                                                                        {value === null ? '-' : String(value)}
                                                                    </td>
                                                                ))}
                                                            </tr>
                                                        ))}
                                                    </tbody>
                                                </table>

                                                {aiResponse.query_result.data.length > 10 && (
                                                    <div className="mt-4 text-center">
                                                        <p className="text-sm text-gray-500 dark:text-gray-400">
                                                            Menampilkan 10 dari {aiResponse.query_result.data.length} data.
                                                            <span className="ml-1 text-blue-600 dark:text-blue-400">
                                                                Untuk melihat lebih banyak, coba perbaiki pertanyaan Anda.
                                                            </span>
                                                        </p>
                                                    </div>
                                                )}
                                            </div>
                                        ) : (
                                            <div className="py-8 text-center text-gray-500">
                                                <Database className="mx-auto mb-4 h-12 w-12 opacity-50" />
                                                <p>Tidak ada data yang ditemukan</p>
                                                <p className="mt-2 text-sm">Coba ubah pertanyaan Anda atau gunakan kata kunci yang berbeda</p>
                                            </div>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
