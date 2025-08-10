import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Baby, Calendar, CalendarDays, Clock, FileText, Heart, MapPin, Printer, Stethoscope, User, UserCheck } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';

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

interface Props {
    imd: ImdData;
}

export default function ImdShow({ imd }: Props) {
    const [isPrinting, setIsPrinting] = useState(false);

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
    };

    const formatDateTime = (dateString: string) => {
        return new Date(dateString).toLocaleString('id-ID', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    const getCaraPersalinanBadge = (cara: string) => {
        return cara === 'SC' ? (
            <Badge variant="destructive" className="text-sm">
                {cara} (Sectio Caesarea)
            </Badge>
        ) : (
            <Badge variant="default" className="text-sm">
                {cara}
            </Badge>
        );
    };

    const getWaktuImdBadge = (waktu: string) => {
        const colorClasses =
            {
                '15 menit': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                '30 menit': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                '45 menit': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                '60 menit': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            }[waktu] || 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';

        return <Badge className={`text-sm ${colorClasses}`}>{waktu}</Badge>;
    };

    const handlePrint = () => {
        setIsPrinting(true);
        toast.success('Mempersiapkan kartu untuk dicetak...');

        // Create a new window for printing
        const printWindow = window.open('', '_blank');
        if (printWindow) {
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Kartu IMD - ${imd.nama_pasien}</title>
                    <style>
                        * { margin: 0; padding: 0; box-sizing: border-box; }
                        body { 
                            font-family: 'Arial', sans-serif; 
                            line-height: 1.6; 
                            color: #333;
                            padding: 20px;
                        }
                        .card { 
                            max-width: 400px; 
                            margin: 0 auto; 
                            border: 2px solid #e2e8f0; 
                            border-radius: 12px; 
                            overflow: hidden;
                            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
                        }
                        .header { 
                            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                            color: white; 
                            padding: 20px; 
                            text-align: center; 
                        }
                        .header h1 { 
                            font-size: 24px; 
                            margin-bottom: 5px; 
                            font-weight: bold;
                        }
                        .header p { 
                            font-size: 14px; 
                            opacity: 0.9; 
                        }
                        .content { 
                            padding: 25px; 
                            background: white;
                        }
                        .info-row { 
                            display: flex; 
                            justify-content: space-between; 
                            align-items: center; 
                            padding: 12px 0; 
                            border-bottom: 1px solid #f1f5f9; 
                        }
                        .info-row:last-child { 
                            border-bottom: none; 
                        }
                        .label { 
                            font-weight: 600; 
                            color: #64748b; 
                            font-size: 14px;
                        }
                        .value { 
                            font-weight: 500; 
                            color: #1e293b; 
                            font-size: 14px;
                            text-align: right;
                        }
                        .badge-sc { 
                            background: #fecaca; 
                            color: #dc2626; 
                            padding: 4px 8px; 
                            border-radius: 6px; 
                            font-size: 12px; 
                            font-weight: 600;
                        }
                        .badge-spontan { 
                            background: #d1fae5; 
                            color: #059669; 
                            padding: 4px 8px; 
                            border-radius: 6px; 
                            font-size: 12px; 
                            font-weight: 600;
                        }
                        .badge-waktu { 
                            padding: 4px 8px; 
                            border-radius: 6px; 
                            font-size: 12px; 
                            font-weight: 600;
                        }
                        .waktu-15 { background: #fecaca; color: #dc2626; }
                        .waktu-30 { background: #fef3c7; color: #d97706; }
                        .waktu-45 { background: #dbeafe; color: #2563eb; }
                        .waktu-60 { background: #d1fae5; color: #059669; }
                        .footer { 
                            background: #f8fafc; 
                            padding: 15px 25px; 
                            text-align: center; 
                            font-size: 12px; 
                            color: #64748b; 
                        }
                        .qr-placeholder {
                            width: 60px;
                            height: 60px;
                            background: #f1f5f9;
                            border: 2px dashed #cbd5e1;
                            border-radius: 8px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 10px;
                            color: #64748b;
                            margin: 0 auto 10px;
                        }
                        @media print {
                            body { padding: 0; }
                            .card { box-shadow: none; }
                        }
                    </style>
                </head>
                <body>
                    <div class="card">
                        <div class="header">
                            <h1>KARTU IMD</h1>
                            <p>Inisiasi Menyusui Dini</p>
                        </div>
                        <div class="content">
                            <div class="info-row">
                                <span class="label">Nama Pasien</span>
                                <span class="value">${imd.nama_pasien}</span>
                            </div>
                            <div class="info-row">
                                <span class="label">No. RM</span>
                                <span class="value">${imd.no_rm}</span>
                            </div>
                            <div class="info-row">
                                <span class="label">Tanggal Lahir</span>
                                <span class="value">${formatDate(imd.tanggal_lahir)}</span>
                            </div>
                            <div class="info-row">
                                <span class="label">Cara Persalinan</span>
                                <span class="value">
                                    <span class="${imd.cara_persalinan === 'SC' ? 'badge-sc' : 'badge-spontan'}">
                                        ${imd.cara_persalinan === 'SC' ? 'SC (Sectio Caesarea)' : 'Spontan'}
                                    </span>
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="label">Tanggal IMD</span>
                                <span class="value">${formatDate(imd.tanggal_imd)}</span>
                            </div>
                            <div class="info-row">
                                <span class="label">Waktu IMD</span>
                                <span class="value">
                                    <span class="badge-waktu waktu-${imd.waktu_imd.split(' ')[0]}">
                                        ${imd.waktu_imd}
                                    </span>
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="label">Petugas</span>
                                <span class="value">${imd.nama_petugas}</span>
                            </div>
                        </div>
                        <div class="footer">
                            <div class="qr-placeholder">QR Code</div>
                            <p>Dicetak pada: ${new Date().toLocaleDateString('id-ID', {
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit',
                            })}</p>
                        </div>
                    </div>
                </body>
                </html>
            `);
            printWindow.document.close();

            setTimeout(() => {
                printWindow.print();
                printWindow.close();
                setIsPrinting(false);
            }, 500);
        } else {
            toast.error('Gagal membuka jendela cetak');
            setIsPrinting(false);
        }
    };

    return (
        <AppLayout>
            <Head title={`Detail IMD - ${imd.nama_pasien}`} />

            <div className="p-6">
                {/* Header */}
                <div className="mb-6 flex items-center justify-between">
                    <div className="flex items-center space-x-4">
                        <Link href={route('imds.index')}>
                            <Button variant="outline" size="sm" className="flex items-center gap-2">
                                <ArrowLeft className="h-4 w-4" />
                                Kembali
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Detail Data IMD</h1>
                            <p className="text-gray-600 dark:text-gray-400">Informasi lengkap data Inisiasi Menyusui Dini</p>
                        </div>
                    </div>
                    <Button
                        onClick={handlePrint}
                        disabled={isPrinting}
                        className="flex items-center gap-2 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700"
                    >
                        <Printer className="h-4 w-4" />
                        {isPrinting ? 'Mempersiapkan...' : 'Cetak Kartu'}
                    </Button>
                </div>

                <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    {/* Main Info Card */}
                    <div className="lg:col-span-2">
                        <Card className="shadow-lg">
                            <CardHeader className="bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20">
                                <CardTitle className="flex items-center space-x-2">
                                    <User className="h-5 w-5 text-blue-600" />
                                    <span>Informasi Pasien</span>
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="p-6">
                                <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <div className="space-y-4">
                                        <div className="flex items-start space-x-3">
                                            <User className="mt-0.5 h-5 w-5 text-gray-400" />
                                            <div>
                                                <p className="text-sm font-medium text-gray-500 dark:text-gray-400">Nama Pasien</p>
                                                <p className="text-lg font-semibold text-gray-900 dark:text-white">{imd.nama_pasien}</p>
                                            </div>
                                        </div>

                                        <div className="flex items-start space-x-3">
                                            <FileText className="mt-0.5 h-5 w-5 text-gray-400" />
                                            <div>
                                                <p className="text-sm font-medium text-gray-500 dark:text-gray-400">No. RM</p>
                                                <p className="text-lg font-semibold text-gray-900 dark:text-white">{imd.no_rm}</p>
                                            </div>
                                        </div>

                                        <div className="flex items-start space-x-3">
                                            <MapPin className="mt-0.5 h-5 w-5 text-gray-400" />
                                            <div>
                                                <p className="text-sm font-medium text-gray-500 dark:text-gray-400">Alamat</p>
                                                <p className="text-base text-gray-900 dark:text-white">{imd.alamat}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="space-y-4">
                                        <div className="flex items-start space-x-3">
                                            <Calendar className="mt-0.5 h-5 w-5 text-gray-400" />
                                            <div>
                                                <p className="text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal Lahir</p>
                                                <p className="text-lg font-semibold text-gray-900 dark:text-white">{formatDate(imd.tanggal_lahir)}</p>
                                            </div>
                                        </div>

                                        <div className="flex items-start space-x-3">
                                            <Stethoscope className="mt-0.5 h-5 w-5 text-gray-400" />
                                            <div>
                                                <p className="text-sm font-medium text-gray-500 dark:text-gray-400">Cara Persalinan</p>
                                                <div className="mt-1">{getCaraPersalinanBadge(imd.cara_persalinan)}</div>
                                            </div>
                                        </div>

                                        <div className="flex items-start space-x-3">
                                            <UserCheck className="mt-0.5 h-5 w-5 text-gray-400" />
                                            <div>
                                                <p className="text-sm font-medium text-gray-500 dark:text-gray-400">Petugas Medis</p>
                                                <p className="text-lg font-semibold text-gray-900 dark:text-white">{imd.nama_petugas}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* IMD Info Card */}
                    <div>
                        <Card className="shadow-lg">
                            <CardHeader className="bg-gradient-to-r from-pink-50 to-red-50 dark:from-pink-900/20 dark:to-red-900/20">
                                <CardTitle className="flex items-center space-x-2">
                                    <Heart className="h-5 w-5 text-pink-600" />
                                    <span>Informasi IMD</span>
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="p-6">
                                <div className="space-y-6">
                                    <div className="flex items-start space-x-3">
                                        <CalendarDays className="mt-0.5 h-5 w-5 text-gray-400" />
                                        <div>
                                            <p className="text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal IMD</p>
                                            <p className="text-lg font-semibold text-gray-900 dark:text-white">{formatDate(imd.tanggal_imd)}</p>
                                        </div>
                                    </div>

                                    <div className="flex items-start space-x-3">
                                        <Clock className="mt-0.5 h-5 w-5 text-gray-400" />
                                        <div>
                                            <p className="text-sm font-medium text-gray-500 dark:text-gray-400">Durasi IMD</p>
                                            <div className="mt-1">{getWaktuImdBadge(imd.waktu_imd)}</div>
                                        </div>
                                    </div>

                                    <Separator />

                                    <div className="rounded-lg bg-gradient-to-r from-green-50 to-blue-50 p-4 dark:from-green-900/20 dark:to-blue-900/20">
                                        <div className="mb-2 flex items-center space-x-2">
                                            <Baby className="h-5 w-5 text-green-600" />
                                            <h3 className="font-semibold text-green-800 dark:text-green-200">Status IMD</h3>
                                        </div>
                                        <p className="text-sm text-green-700 dark:text-green-300">
                                            IMD telah dilaksanakan dengan durasi {imd.waktu_imd} pada {formatDate(imd.tanggal_imd)}
                                        </p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Metadata Card */}
                        <Card className="mt-6 shadow-lg">
                            <CardHeader>
                                <CardTitle className="flex items-center space-x-2 text-sm">
                                    <FileText className="h-4 w-4 text-gray-400" />
                                    <span>Metadata</span>
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="p-6">
                                <div className="space-y-3 text-sm">
                                    <div>
                                        <p className="font-medium text-gray-500 dark:text-gray-400">Data Dibuat</p>
                                        <p className="text-gray-900 dark:text-white">{formatDateTime(imd.created_at)}</p>
                                    </div>
                                    <div>
                                        <p className="font-medium text-gray-500 dark:text-gray-400">Terakhir Diperbarui</p>
                                        <p className="text-gray-900 dark:text-white">{formatDateTime(imd.updated_at)}</p>
                                    </div>
                                    <div>
                                        <p className="font-medium text-gray-500 dark:text-gray-400">ID Record</p>
                                        <p className="font-mono text-xs text-gray-900 dark:text-white">{imd.id}</p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
