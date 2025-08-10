<?php

namespace App\Exports;

use App\Models\Imd;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ImdExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Imd::query();

        // Apply filters
        if (isset($this->filters['search']) && !empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('nama_pasien', 'like', '%' . $search . '%')
                    ->orWhere('no_rm', 'like', '%' . $search . '%')
                    ->orWhere('nama_petugas', 'like', '%' . $search . '%');
            });
        }

        if (isset($this->filters['cara_persalinan']) && !empty($this->filters['cara_persalinan'])) {
            $query->where('cara_persalinan', $this->filters['cara_persalinan']);
        }

        if (isset($this->filters['waktu_imd']) && !empty($this->filters['waktu_imd'])) {
            $query->where('waktu_imd', $this->filters['waktu_imd']);
        }

        if (isset($this->filters['tanggal_lahir_dari']) && !empty($this->filters['tanggal_lahir_dari'])) {
            $query->where('tanggal_lahir', '>=', $this->filters['tanggal_lahir_dari']);
        }

        if (isset($this->filters['tanggal_lahir_sampai']) && !empty($this->filters['tanggal_lahir_sampai'])) {
            $query->where('tanggal_lahir', '<=', $this->filters['tanggal_lahir_sampai']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'No',
            'Nama Pasien',
            'No RM',
            'Alamat',
            'Tanggal Lahir',
            'Usia (tahun)',
            'Cara Persalinan',
            'Tanggal IMD',
            'Waktu IMD',
            'Nama Petugas',
            'Tanggal Input'
        ];
    }

    /**
     * @param mixed $imd
     * @return array
     */
    public function map($imd): array
    {
        static $no = 1;

        // Calculate age
        $birthDate = new \DateTime($imd->tanggal_lahir);
        $imdDate = new \DateTime($imd->tanggal_imd);
        $age = $birthDate->diff($imdDate)->y;

        return [
            $no++,
            $imd->nama_pasien,
            $imd->no_rm,
            $imd->alamat,
            \Carbon\Carbon::parse($imd->tanggal_lahir)->format('d/m/Y'),
            $age,
            $imd->cara_persalinan,
            \Carbon\Carbon::parse($imd->tanggal_imd)->format('d/m/Y'),
            $imd->waktu_imd,
            $imd->nama_petugas,
            \Carbon\Carbon::parse($imd->created_at)->format('d/m/Y H:i')
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        // Get the highest row number
        $highestRow = $sheet->getHighestRow();

        return [
            // Style the header row
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            // Style all data rows
            "A2:K{$highestRow}" => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            // Style specific columns
            "A:A" => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // No
            "C:C" => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // No RM
            "E:E" => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // Tanggal Lahir
            "F:F" => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // Usia
            "G:G" => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // Cara Persalinan
            "H:H" => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // Tanggal IMD
            "I:I" => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // Waktu IMD
            "K:K" => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // Tanggal Input
        ];
    }
}
