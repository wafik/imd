import { Head, router } from '@inertiajs/react';
import { Activity, Baby, Calendar, Clock, Filter, Timer, TrendingUp, Users } from 'lucide-react';
import { useState } from 'react';
import { Bar, BarChart, CartesianGrid, Cell, Line, LineChart, Pie, PieChart, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';

interface ChartData {
    imdByCaraPersalinan: Array<{ name: string; value: number; color: string }>;
    imdByWaktu: Array<{ name: string; value: number; color: string }>;
    monthlyTrend: Array<{ month: string; value: number }>;
    ageDistribution: Array<{ name: string; value: number }>;
}

interface Stats {
    total_imd: number;
    sc_percentage: number;
    spontan_percentage: number;
    avg_duration: number;
}

interface ImdData {
    id: string;
    nama_pasien: string;
    cara_persalinan: string;
    waktu_imd: string;
    tanggal_imd: string;
    nama_petugas: string;
}

interface Props {
    chartData: ChartData;
    stats: Stats;
    recentImds: ImdData[];
    filters: {
        year?: string;
        month?: string;
        cara_persalinan?: string;
    };
    availableYears: number[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

export default function Dashboard({ chartData, stats, recentImds, filters, availableYears }: Props) {
    const [showFilters, setShowFilters] = useState(false);

    const handleFilterChange = (key: string, value: string) => {
        const newFilters = { ...filters };
        if (value === 'all' || value === '') {
            delete newFilters[key as keyof typeof filters];
        } else {
            newFilters[key as keyof typeof filters] = value;
        }

        router.get(route('dashboard'), newFilters, { preserveState: true });
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
    };

    const getCaraPersalinanBadge = (cara: string) => {
        return cara === 'SC' ? <Badge variant="destructive">{cara}</Badge> : <Badge variant="default">{cara}</Badge>;
    };

    const getWaktuImdBadge = (waktu: string) => {
        const color =
            {
                '15 menit': 'bg-red-500',
                '30 menit': 'bg-yellow-500',
                '45 menit': 'bg-blue-500',
                '60 menit': 'bg-green-500',
            }[waktu] || 'bg-gray-500';

        return <Badge className={`${color} text-white`}>{waktu}</Badge>;
    };

    const months = [
        { value: '1', label: 'Januari' },
        { value: '2', label: 'Februari' },
        { value: '3', label: 'Maret' },
        { value: '4', label: 'April' },
        { value: '5', label: 'Mei' },
        { value: '6', label: 'Juni' },
        { value: '7', label: 'Juli' },
        { value: '8', label: 'Agustus' },
        { value: '9', label: 'September' },
        { value: '10', label: 'Oktober' },
        { value: '11', label: 'November' },
        { value: '12', label: 'Desember' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />

            <div className="space-y-6 p-6">
                {/* Header */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900 dark:text-white">Dashboard IMD</h1>
                        <p className="text-gray-600 dark:text-gray-400">Analisis data Inisiasi Menyusu Dini (IMD)</p>
                    </div>

                    <Button variant="outline" onClick={() => setShowFilters(!showFilters)} className="flex items-center gap-2">
                        <Filter className="h-4 w-4" />
                        Filter Data
                    </Button>
                </div>

                {/* Filters */}
                {showFilters && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Filter Data</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div className="space-y-2">
                                    <Label>Tahun</Label>
                                    <Select value={filters.year || 'all'} onValueChange={(value) => handleFilterChange('year', value)}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Pilih tahun" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">Semua tahun</SelectItem>
                                            {availableYears.map((year) => (
                                                <SelectItem key={year} value={year.toString()}>
                                                    {year}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div className="space-y-2">
                                    <Label>Bulan</Label>
                                    <Select value={filters.month || 'all'} onValueChange={(value) => handleFilterChange('month', value)}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Pilih bulan" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">Semua bulan</SelectItem>
                                            {months.map((month) => (
                                                <SelectItem key={month.value} value={month.value}>
                                                    {month.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div className="space-y-2">
                                    <Label>Cara Persalinan</Label>
                                    <Select
                                        value={filters.cara_persalinan || 'all'}
                                        onValueChange={(value) => handleFilterChange('cara_persalinan', value)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Pilih cara persalinan" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">Semua cara persalinan</SelectItem>
                                            <SelectItem value="SC">SC (Sectio Caesarea)</SelectItem>
                                            <SelectItem value="Spontan">Spontan</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Stats Cards */}
                <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                    <Card className="bg-gradient-to-r from-blue-500 to-blue-600 text-white">
                        <CardContent className="p-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-blue-100">Total IMD</p>
                                    <p className="text-3xl font-bold">{stats.total_imd}</p>
                                </div>
                                <Baby className="h-12 w-12 text-blue-200" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="bg-gradient-to-r from-red-500 to-red-600 text-white">
                        <CardContent className="p-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-red-100">SC Persalinan</p>
                                    <p className="text-3xl font-bold">{stats.sc_percentage}%</p>
                                </div>
                                <Activity className="h-12 w-12 text-red-200" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="bg-gradient-to-r from-green-500 to-green-600 text-white">
                        <CardContent className="p-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-green-100">Spontan</p>
                                    <p className="text-3xl font-bold">{stats.spontan_percentage}%</p>
                                </div>
                                <TrendingUp className="h-12 w-12 text-green-200" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="bg-gradient-to-r from-purple-500 to-purple-600 text-white">
                        <CardContent className="p-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-purple-100">Rata-rata Waktu</p>
                                    <p className="text-3xl font-bold">{Math.round(stats.avg_duration)} min</p>
                                </div>
                                <Timer className="h-12 w-12 text-purple-200" />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Charts */}
                <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    {/* Delivery Method Chart */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Distribusi Cara Persalinan</CardTitle>
                            <CardDescription>Perbandingan metode persalinan SC vs Spontan</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <ResponsiveContainer width="100%" height={300}>
                                <PieChart>
                                    <Pie
                                        dataKey="value"
                                        data={chartData.imdByCaraPersalinan}
                                        cx="50%"
                                        cy="50%"
                                        outerRadius={80}
                                        fill="#8884d8"
                                        label={({ name, value, percent }) => `${name}: ${value} (${(percent * 100).toFixed(0)}%)`}
                                    >
                                        {chartData.imdByCaraPersalinan.map((entry, index) => (
                                            <Cell key={`cell-${index}`} fill={entry.color} />
                                        ))}
                                    </Pie>
                                    <Tooltip />
                                </PieChart>
                            </ResponsiveContainer>
                        </CardContent>
                    </Card>

                    {/* IMD Duration Chart */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Distribusi Waktu IMD</CardTitle>
                            <CardDescription>Durasi pelaksanaan IMD</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <ResponsiveContainer width="100%" height={300}>
                                <BarChart data={chartData.imdByWaktu}>
                                    <CartesianGrid strokeDasharray="3 3" />
                                    <XAxis dataKey="name" />
                                    <YAxis />
                                    <Tooltip />
                                    <Bar dataKey="value" fill="#8884d8">
                                        {chartData.imdByWaktu.map((entry, index) => (
                                            <Cell key={`cell-${index}`} fill={entry.color} />
                                        ))}
                                    </Bar>
                                </BarChart>
                            </ResponsiveContainer>
                        </CardContent>
                    </Card>
                </div>

                {/* Monthly Trend and Age Distribution */}
                <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    {/* Monthly Trend */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Tren Bulanan IMD</CardTitle>
                            <CardDescription>Perkembangan jumlah IMD dalam 12 bulan terakhir</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <ResponsiveContainer width="100%" height={300}>
                                <LineChart data={chartData.monthlyTrend}>
                                    <CartesianGrid strokeDasharray="3 3" />
                                    <XAxis dataKey="month" />
                                    <YAxis />
                                    <Tooltip />
                                    <Line
                                        type="monotone"
                                        dataKey="value"
                                        stroke="#3b82f6"
                                        strokeWidth={3}
                                        dot={{ fill: '#3b82f6', strokeWidth: 2, r: 6 }}
                                    />
                                </LineChart>
                            </ResponsiveContainer>
                        </CardContent>
                    </Card>

                    {/* Age Distribution */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Distribusi Usia Ibu</CardTitle>
                            <CardDescription>Kelompok usia ibu saat melahirkan</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <ResponsiveContainer width="100%" height={300}>
                                <BarChart data={chartData.ageDistribution}>
                                    <CartesianGrid strokeDasharray="3 3" />
                                    <XAxis dataKey="name" />
                                    <YAxis />
                                    <Tooltip />
                                    <Bar dataKey="value" fill="#10b981" />
                                </BarChart>
                            </ResponsiveContainer>
                        </CardContent>
                    </Card>
                </div>

                {/* Recent IMD Records */}
                <Card>
                    <CardHeader>
                        <CardTitle>Data IMD Terbaru</CardTitle>
                        <CardDescription>5 data IMD terbaru yang tercatat</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {recentImds.map((imd) => (
                                <div key={imd.id} className="flex items-center justify-between rounded-lg border p-4">
                                    <div className="flex-1">
                                        <div className="mb-2 flex items-center gap-2">
                                            <Users className="h-4 w-4 text-muted-foreground" />
                                            <span className="font-medium">{imd.nama_pasien}</span>
                                        </div>
                                        <div className="flex items-center gap-4 text-sm text-muted-foreground">
                                            <div className="flex items-center gap-1">
                                                <Calendar className="h-3 w-3" />
                                                {formatDate(imd.tanggal_imd)}
                                            </div>
                                            <div className="flex items-center gap-1">
                                                <Clock className="h-3 w-3" />
                                                {imd.waktu_imd}
                                            </div>
                                            <span>Petugas: {imd.nama_petugas}</span>
                                        </div>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        {getCaraPersalinanBadge(imd.cara_persalinan)}
                                        {getWaktuImdBadge(imd.waktu_imd)}
                                    </div>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
