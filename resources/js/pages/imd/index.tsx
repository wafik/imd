import { Head, Link, router, useForm } from '@inertiajs/react';
import { Calendar, Clock, Download, Edit, Eye, FileText, Filter, MapPin, Plus, Search, Trash2, User, X } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';

import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';

interface ImdData {
    id: string;
    nama_pasien: string;
    alamat: string;
    no_rm: string;
    tanggal_lahir: string;
    cara_persalinan: 'SC' | 'Spontan';
    tanggal_imd: string;
    waktu_imd: '15 menit' | '30 menit' | '45 menit' | '60 menit';
    nama_petugas: string;
    created_at: string;
    updated_at: string;
}

interface PaginatedImds {
    data: ImdData[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}

interface Props {
    imds: PaginatedImds;
    filters: {
        search?: string;
        cara_persalinan?: string;
        waktu_imd?: string;
        tanggal_lahir_dari?: string;
        tanggal_lahir_sampai?: string;
    };
}

export default function ImdIndex({ imds, filters }: Props) {
    const [isCreateOpen, setIsCreateOpen] = useState(false);
    const [isEditOpen, setIsEditOpen] = useState(false);
    const [editingImd, setEditingImd] = useState<ImdData | null>(null);
    const [search, setSearch] = useState(filters.search || '');
    const [showFilters, setShowFilters] = useState(false);

    // Filter states
    const [caraPersalinan, setCaraPersalinan] = useState(filters.cara_persalinan || '');
    const [waktuImd, setWaktuImd] = useState(filters.waktu_imd || '');
    const [tanggalLahirDari, setTanggalLahirDari] = useState(filters.tanggal_lahir_dari || '');
    const [tanggalLahirSampai, setTanggalLahirSampai] = useState(filters.tanggal_lahir_sampai || '');

    const { data, setData, post, put, processing, errors, reset } = useForm({
        nama_pasien: '',
        alamat: '',
        no_rm: '',
        tanggal_lahir: '',
        cara_persalinan: '',
        tanggal_imd: '',
        waktu_imd: '',
        nama_petugas: '',
    });

    const handleSearch = (value: string) => {
        setSearch(value);
        applyFilters({ search: value });
    };

    const applyFilters = (additionalFilters: Record<string, string> = {}) => {
        const filterParams: Record<string, string> = {
            search,
            cara_persalinan: caraPersalinan,
            waktu_imd: waktuImd,
            tanggal_lahir_dari: tanggalLahirDari,
            tanggal_lahir_sampai: tanggalLahirSampai,
            ...additionalFilters,
        };

        // Remove empty filters
        Object.keys(filterParams).forEach((key) => {
            if (!filterParams[key]) {
                delete filterParams[key];
            }
        });

        router.get(route('imds.index'), filterParams, { preserveState: true });
    };

    const handleFilterChange = (key: string, value: string) => {
        switch (key) {
            case 'cara_persalinan':
                setCaraPersalinan(value);
                break;
            case 'waktu_imd':
                setWaktuImd(value);
                break;
            case 'tanggal_lahir_dari':
                setTanggalLahirDari(value);
                break;
            case 'tanggal_lahir_sampai':
                setTanggalLahirSampai(value);
                break;
        }
        applyFilters({ [key]: value });
    };

    const clearFilters = () => {
        setSearch('');
        setCaraPersalinan('');
        setWaktuImd('');
        setTanggalLahirDari('');
        setTanggalLahirSampai('');
        router.get(route('imds.index'));
    };

    const hasActiveFilters = caraPersalinan || waktuImd || tanggalLahirDari || tanggalLahirSampai;

    const handleExport = () => {
        const filterParams = new URLSearchParams();

        if (search) filterParams.append('search', search);
        if (caraPersalinan) filterParams.append('cara_persalinan', caraPersalinan);
        if (waktuImd) filterParams.append('waktu_imd', waktuImd);
        if (tanggalLahirDari) filterParams.append('tanggal_lahir_dari', tanggalLahirDari);
        if (tanggalLahirSampai) filterParams.append('tanggal_lahir_sampai', tanggalLahirSampai);

        const url = route('imds.export') + (filterParams.toString() ? '?' + filterParams.toString() : '');
        window.open(url, '_blank');
        toast.success('Export Excel sedang diproses...');
    };

    const openCreateDialog = () => {
        reset();
        setIsCreateOpen(true);
    };

    const openEditDialog = (imd: ImdData) => {
        setEditingImd(imd);
        setData({
            nama_pasien: imd.nama_pasien,
            alamat: imd.alamat,
            no_rm: imd.no_rm,
            tanggal_lahir: imd.tanggal_lahir,
            cara_persalinan: imd.cara_persalinan,
            tanggal_imd: imd.tanggal_imd,
            waktu_imd: imd.waktu_imd,
            nama_petugas: imd.nama_petugas,
        });
        setIsEditOpen(true);
    };

    const handleCreate = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('imds.store'), {
            onSuccess: () => {
                setIsCreateOpen(false);
                toast.success('Data IMD berhasil ditambahkan!');
                reset();
            },
            onError: () => {
                toast.error('Gagal menambahkan data IMD. Periksa kembali form Anda.');
            },
        });
    };

    const handleUpdate = (e: React.FormEvent) => {
        e.preventDefault();
        if (!editingImd) return;

        put(route('imds.update', editingImd.id), {
            onSuccess: () => {
                setIsEditOpen(false);
                toast.success('Data IMD berhasil diperbarui!');
                reset();
                setEditingImd(null);
            },
            onError: () => {
                toast.error('Gagal memperbarui data IMD. Periksa kembali form Anda.');
            },
        });
    };

    const handleDelete = (imd: ImdData) => {
        if (confirm('Apakah Anda yakin ingin menghapus data IMD ini?')) {
            router.delete(route('imds.destroy', imd.id), {
                onSuccess: () => {
                    toast.success('Data IMD berhasil dihapus!');
                },
                onError: () => {
                    toast.error('Gagal menghapus data IMD.');
                },
            });
        }
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

    return (
        <AppLayout>
            <Head title="Data IMD" />

            <div className="p-6">
                <div className="mb-6 flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Data IMD (Inisiasi Menyusu Dini)</h1>
                        <p className="text-gray-600 dark:text-gray-400">Kelola data Inisiasi Menyusu Dini (IMD) pasien</p>
                    </div>
                    <div className="flex items-center gap-2">
                        <Button variant="outline" onClick={handleExport} className="flex items-center gap-2">
                            <Download className="h-4 w-4" />
                            Export Excel
                        </Button>
                        <Dialog open={isCreateOpen} onOpenChange={setIsCreateOpen}>
                            <DialogTrigger asChild>
                                <Button onClick={openCreateDialog} className="flex items-center gap-2">
                                    <Plus className="h-4 w-4" />
                                    Tambah Data IMD
                                </Button>
                            </DialogTrigger>
                            <DialogContent className="max-h-[90vh] max-w-2xl overflow-y-auto">
                                <DialogHeader>
                                    <DialogTitle>Tambah Data IMD Baru</DialogTitle>
                                    <DialogDescription>Isi form di bawah untuk menambahkan data IMD baru.</DialogDescription>
                                </DialogHeader>
                                <form onSubmit={handleCreate} className="space-y-4">
                                    <div className="grid grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <Label htmlFor="nama_pasien">Nama Pasien</Label>
                                            <Input
                                                id="nama_pasien"
                                                value={data.nama_pasien}
                                                onChange={(e) => setData('nama_pasien', e.target.value)}
                                                placeholder="Masukkan nama pasien"
                                            />
                                            <InputError message={errors.nama_pasien} />
                                        </div>
                                        <div className="space-y-2">
                                            <Label htmlFor="no_rm">No RM</Label>
                                            <Input
                                                id="no_rm"
                                                value={data.no_rm}
                                                onChange={(e) => setData('no_rm', e.target.value)}
                                                placeholder="Masukkan No RM"
                                            />
                                            <InputError message={errors.no_rm} />
                                        </div>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="alamat">Alamat</Label>
                                        <Input
                                            id="alamat"
                                            value={data.alamat}
                                            onChange={(e) => setData('alamat', e.target.value)}
                                            placeholder="Masukkan alamat lengkap"
                                        />
                                        <InputError message={errors.alamat} />
                                    </div>

                                    <div className="grid grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <Label htmlFor="tanggal_lahir">Tanggal Lahir</Label>
                                            <Input
                                                id="tanggal_lahir"
                                                type="date"
                                                value={data.tanggal_lahir}
                                                onChange={(e) => setData('tanggal_lahir', e.target.value)}
                                            />
                                            <InputError message={errors.tanggal_lahir} />
                                        </div>
                                        <div className="space-y-2">
                                            <Label htmlFor="cara_persalinan">Cara Persalinan</Label>
                                            <Select value={data.cara_persalinan} onValueChange={(value) => setData('cara_persalinan', value)}>
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Pilih cara persalinan" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="SC">SC (Sectio Caesarea)</SelectItem>
                                                    <SelectItem value="Spontan">Spontan</SelectItem>
                                                </SelectContent>
                                            </Select>
                                            <InputError message={errors.cara_persalinan} />
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <Label htmlFor="tanggal_imd">Tanggal IMD</Label>
                                            <Input
                                                id="tanggal_imd"
                                                type="date"
                                                value={data.tanggal_imd}
                                                onChange={(e) => setData('tanggal_imd', e.target.value)}
                                            />
                                            <InputError message={errors.tanggal_imd} />
                                        </div>
                                        <div className="space-y-2">
                                            <Label htmlFor="waktu_imd">Waktu IMD</Label>
                                            <Select value={data.waktu_imd} onValueChange={(value) => setData('waktu_imd', value)}>
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Pilih waktu IMD" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="15 menit">15 menit</SelectItem>
                                                    <SelectItem value="30 menit">30 menit</SelectItem>
                                                    <SelectItem value="45 menit">45 menit</SelectItem>
                                                    <SelectItem value="60 menit">60 menit</SelectItem>
                                                </SelectContent>
                                            </Select>
                                            <InputError message={errors.waktu_imd} />
                                        </div>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="nama_petugas">Nama Petugas</Label>
                                        <Input
                                            id="nama_petugas"
                                            value={data.nama_petugas}
                                            onChange={(e) => setData('nama_petugas', e.target.value)}
                                            placeholder="Masukkan nama petugas"
                                        />
                                        <InputError message={errors.nama_petugas} />
                                    </div>

                                    <div className="flex justify-end space-x-2 pt-4">
                                        <Button type="button" variant="outline" onClick={() => setIsCreateOpen(false)}>
                                            Batal
                                        </Button>
                                        <Button type="submit" disabled={processing}>
                                            {processing ? 'Menyimpan...' : 'Simpan'}
                                        </Button>
                                    </div>
                                </form>
                            </DialogContent>
                        </Dialog>
                    </div>
                </div>

                <Card>
                    <CardHeader>
                        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <CardTitle>Data IMD</CardTitle>
                                <CardDescription>Kelola data Inisiasi Menyusu Dini (IMD) pasien</CardDescription>
                            </div>
                            <div className="flex items-center space-x-2">
                                <div className="relative">
                                    <Search className="absolute top-2.5 left-2 h-4 w-4 text-muted-foreground" />
                                    <Input
                                        placeholder="Cari nama pasien, No RM, atau petugas..."
                                        value={search}
                                        onChange={(e) => handleSearch(e.target.value)}
                                        className="w-80 pl-8"
                                    />
                                </div>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() => setShowFilters(!showFilters)}
                                    className={hasActiveFilters ? 'border-blue-500 text-blue-600' : ''}
                                >
                                    <Filter className="mr-2 h-4 w-4" />
                                    Filter
                                    {hasActiveFilters && (
                                        <span className="ml-2 rounded-full bg-blue-500 px-2 py-0.5 text-xs text-white">
                                            {[caraPersalinan, waktuImd, tanggalLahirDari, tanggalLahirSampai].filter(Boolean).length}
                                        </span>
                                    )}
                                </Button>
                                {hasActiveFilters && (
                                    <Button variant="ghost" size="sm" onClick={clearFilters}>
                                        <X className="h-4 w-4" />
                                    </Button>
                                )}
                            </div>
                        </div>

                        {/* Filter Section */}
                        {showFilters && (
                            <div className="mt-4 border-t pt-4">
                                <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                                    <div className="space-y-2">
                                        <Label>Cara Persalinan</Label>
                                        <Select
                                            value={caraPersalinan || 'all'}
                                            onValueChange={(value) => handleFilterChange('cara_persalinan', value === 'all' ? '' : value)}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Semua cara persalinan" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="all">Semua cara persalinan</SelectItem>
                                                <SelectItem value="SC">SC (Sectio Caesarea)</SelectItem>
                                                <SelectItem value="Spontan">Spontan</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    <div className="space-y-2">
                                        <Label>Waktu IMD</Label>
                                        <Select
                                            value={waktuImd || 'all'}
                                            onValueChange={(value) => handleFilterChange('waktu_imd', value === 'all' ? '' : value)}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Semua waktu IMD" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="all">Semua waktu IMD</SelectItem>
                                                <SelectItem value="15 menit">15 menit</SelectItem>
                                                <SelectItem value="30 menit">30 menit</SelectItem>
                                                <SelectItem value="45 menit">45 menit</SelectItem>
                                                <SelectItem value="60 menit">60 menit</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    <div className="space-y-2">
                                        <Label>Tanggal Lahir Dari</Label>
                                        <Input
                                            type="date"
                                            value={tanggalLahirDari}
                                            onChange={(e) => handleFilterChange('tanggal_lahir_dari', e.target.value)}
                                        />
                                    </div>

                                    <div className="space-y-2">
                                        <Label>Tanggal Lahir Sampai</Label>
                                        <Input
                                            type="date"
                                            value={tanggalLahirSampai}
                                            onChange={(e) => handleFilterChange('tanggal_lahir_sampai', e.target.value)}
                                        />
                                    </div>
                                </div>
                            </div>
                        )}
                    </CardHeader>
                    <CardContent>
                        {imds.data.length > 0 ? (
                            <div className="space-y-4">
                                {imds.data.map((imd) => (
                                    <Card key={imd.id} className="transition-shadow hover:shadow-md">
                                        <CardContent className="p-6">
                                            <div className="flex items-start justify-between">
                                                <div className="grid flex-1 grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                                                    <div className="space-y-2">
                                                        <div className="flex items-center gap-2">
                                                            <User className="h-4 w-4 text-muted-foreground" />
                                                            <span className="font-medium">{imd.nama_pasien}</span>
                                                        </div>
                                                        <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                                            <FileText className="h-4 w-4" />
                                                            <span>No RM: {imd.no_rm}</span>
                                                        </div>
                                                        <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                                            <MapPin className="h-4 w-4" />
                                                            <span className="truncate">{imd.alamat}</span>
                                                        </div>
                                                    </div>

                                                    <div className="space-y-2">
                                                        <div className="flex items-center gap-2 text-sm">
                                                            <Calendar className="h-4 w-4 text-muted-foreground" />
                                                            <span>TTL: {formatDate(imd.tanggal_lahir)}</span>
                                                        </div>
                                                        <div className="flex items-center gap-2">
                                                            <span className="text-sm text-muted-foreground">Persalinan:</span>
                                                            {getCaraPersalinanBadge(imd.cara_persalinan)}
                                                        </div>
                                                        <div className="text-sm text-muted-foreground">Petugas: {imd.nama_petugas}</div>
                                                    </div>

                                                    <div className="space-y-2">
                                                        <div className="flex items-center gap-2 text-sm">
                                                            <Calendar className="h-4 w-4 text-muted-foreground" />
                                                            <span>IMD: {formatDate(imd.tanggal_imd)}</span>
                                                        </div>
                                                        <div className="flex items-center gap-2">
                                                            <Clock className="h-4 w-4 text-muted-foreground" />
                                                            {getWaktuImdBadge(imd.waktu_imd)}
                                                        </div>
                                                    </div>
                                                </div>

                                                <div className="ml-4 flex flex-col gap-2">
                                                    <Link href={route('imds.show', imd.id)}>
                                                        <Button size="sm" variant="outline" className="w-full">
                                                            <Eye className="h-4 w-4" />
                                                        </Button>
                                                    </Link>
                                                    <Button size="sm" variant="outline" onClick={() => openEditDialog(imd)}>
                                                        <Edit className="h-4 w-4" />
                                                    </Button>
                                                    <Button
                                                        size="sm"
                                                        variant="outline"
                                                        onClick={() => handleDelete(imd)}
                                                        className="text-red-600 hover:bg-red-50 hover:text-red-700 dark:hover:bg-red-950"
                                                    >
                                                        <Trash2 className="h-4 w-4" />
                                                    </Button>
                                                </div>
                                            </div>
                                        </CardContent>
                                    </Card>
                                ))}

                                {/* Pagination */}
                                {imds.last_page > 1 && (
                                    <div className="mt-6 flex justify-center space-x-2">
                                        {imds.links.map((link, index) => (
                                            <button
                                                key={index}
                                                onClick={() => link.url && router.get(link.url)}
                                                disabled={!link.url}
                                                className={`rounded-md px-3 py-2 text-sm ${
                                                    link.active
                                                        ? 'bg-blue-500 text-white'
                                                        : link.url
                                                          ? 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'
                                                          : 'cursor-not-allowed bg-gray-100 text-gray-400 dark:bg-gray-700'
                                                }`}
                                                dangerouslySetInnerHTML={{ __html: link.label }}
                                            />
                                        ))}
                                    </div>
                                )}
                            </div>
                        ) : (
                            <div className="py-12 text-center">
                                <User className="mx-auto h-12 w-12 text-gray-400" />
                                <h3 className="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Tidak ada data IMD</h3>
                                <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">Mulai dengan menambahkan data IMD baru.</p>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>

            {/* Edit Dialog */}
            <Dialog open={isEditOpen} onOpenChange={setIsEditOpen}>
                <DialogContent className="max-h-[90vh] max-w-2xl overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle>Edit Data IMD</DialogTitle>
                        <DialogDescription>Perbarui informasi data IMD.</DialogDescription>
                    </DialogHeader>
                    <form onSubmit={handleUpdate} className="space-y-4">
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="edit_nama_pasien">Nama Pasien</Label>
                                <Input
                                    id="edit_nama_pasien"
                                    value={data.nama_pasien}
                                    onChange={(e) => setData('nama_pasien', e.target.value)}
                                    placeholder="Masukkan nama pasien"
                                />
                                <InputError message={errors.nama_pasien} />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="edit_no_rm">No RM</Label>
                                <Input
                                    id="edit_no_rm"
                                    value={data.no_rm}
                                    onChange={(e) => setData('no_rm', e.target.value)}
                                    placeholder="Masukkan No RM"
                                />
                                <InputError message={errors.no_rm} />
                            </div>
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="edit_alamat">Alamat</Label>
                            <Input
                                id="edit_alamat"
                                value={data.alamat}
                                onChange={(e) => setData('alamat', e.target.value)}
                                placeholder="Masukkan alamat lengkap"
                            />
                            <InputError message={errors.alamat} />
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="edit_tanggal_lahir">Tanggal Lahir</Label>
                                <Input
                                    id="edit_tanggal_lahir"
                                    type="date"
                                    value={data.tanggal_lahir}
                                    onChange={(e) => setData('tanggal_lahir', e.target.value)}
                                />
                                <InputError message={errors.tanggal_lahir} />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="edit_cara_persalinan">Cara Persalinan</Label>
                                <Select value={data.cara_persalinan} onValueChange={(value) => setData('cara_persalinan', value)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Pilih cara persalinan" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="SC">SC (Sectio Caesarea)</SelectItem>
                                        <SelectItem value="Spontan">Spontan</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.cara_persalinan} />
                            </div>
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="edit_tanggal_imd">Tanggal IMD</Label>
                                <Input
                                    id="edit_tanggal_imd"
                                    type="date"
                                    value={data.tanggal_imd}
                                    onChange={(e) => setData('tanggal_imd', e.target.value)}
                                />
                                <InputError message={errors.tanggal_imd} />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="edit_waktu_imd">Waktu IMD</Label>
                                <Select value={data.waktu_imd} onValueChange={(value) => setData('waktu_imd', value)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Pilih waktu IMD" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="15 menit">15 menit</SelectItem>
                                        <SelectItem value="30 menit">30 menit</SelectItem>
                                        <SelectItem value="45 menit">45 menit</SelectItem>
                                        <SelectItem value="60 menit">60 menit</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.waktu_imd} />
                            </div>
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="edit_nama_petugas">Nama Petugas</Label>
                            <Input
                                id="edit_nama_petugas"
                                value={data.nama_petugas}
                                onChange={(e) => setData('nama_petugas', e.target.value)}
                                placeholder="Masukkan nama petugas"
                            />
                            <InputError message={errors.nama_petugas} />
                        </div>

                        <div className="flex justify-end space-x-2 pt-4">
                            <Button type="button" variant="outline" onClick={() => setIsEditOpen(false)}>
                                Batal
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {processing ? 'Menyimpan...' : 'Perbarui'}
                            </Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
