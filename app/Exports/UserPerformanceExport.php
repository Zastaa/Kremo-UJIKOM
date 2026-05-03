<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UserPerformanceExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    private int $no = 0;

    public function __construct(private Collection $data) {}

    public function collection(): Collection
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            ['LAPORAN KINERJA USER OPERASIONAL'],
            ['Kremo - Kredit Motor Online'],
            ['Tanggal Export: ' . now()->format('d F Y H:i') . ' WIB'],
            [],
            [
                'No',
                'Nama',
                'Email',
                'Role',
                'Total Respons',
                'Pengajuan Dibuat',
                'Pelanggan Dibuat',
                'Survey Diambil',
                'Survey Selesai',
                'Survey Aktif',
                'Approval Ditangani',
                'Disetujui',
                'Ditolak',
                'Aktivitas Terakhir',
            ],
        ];
    }

    public function map($row): array
    {
        $this->no++;

        return [
            $this->no,
            $row['nama'],
            $row['email'],
            ucfirst($row['role']),
            $row['total_respons'],
            $row['pengajuan_dibuat'],
            $row['pelanggan_dibuat'],
            $row['survey_diambil'],
            $row['survey_selesai'],
            $row['survey_aktif'],
            $row['approval_ditangani'],
            $row['approval_disetujui'],
            $row['approval_ditolak'],
            $row['last_activity'] ? $row['last_activity']->format('d/m/Y H:i') : '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->mergeCells('A1:N1');
        $sheet->mergeCells('A2:N2');
        $sheet->mergeCells('A3:N3');

        return [
            1 => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => 'center']],
            2 => ['font' => ['bold' => true], 'alignment' => ['horizontal' => 'center']],
            3 => ['font' => ['italic' => true], 'alignment' => ['horizontal' => 'center']],
            5 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => '4F46E5']],
            ],
        ];
    }
}
