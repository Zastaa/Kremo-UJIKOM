<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrderExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithColumnFormatting
{
    private $no = 0;

    public function __construct(private $data) {}

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return [
            ['LAPORAN DATA ORDER (PENGAJUAN KREDIT)'],
            ['Kremo - Kredit Motor Online'],
            ['Tanggal Export: ' . now()->format('d F Y H:i') . ' WIB'],
            [],
            [
                'No',
                'Tanggal Pengajuan',
                'Nama Pelanggan',
                'Motor',
                'Tenor (Bulan)',
                'Harga Cash (Rp)',
                'DP (Rp)',
                'Cicilan /Bulan (Rp)',
                'Status'
            ]
        ];
    }

    public function map($row): array
    {
        $this->no++;

        return [
            $this->no,
            $row->tgl_pengajuan_kredit ? $row->tgl_pengajuan_kredit->format('d/m/Y') : '-',
            $row->pelanggan->nama_pelanggan ?? '-',
            $row->motor->nama_motor ?? '-',
            $row->jenisCicilan->lama_cicilan ?? '-',
            $row->harga_cash,
            $row->dp,
            $row->cicilan_perbulan,
            $row->status_pengajuan,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => '#,##0',
            'G' => '#,##0',
            'H' => '#,##0',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:I1');
        $sheet->mergeCells('A2:I2');
        $sheet->mergeCells('A3:I3');

        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];

        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle('A5:I' . $highestRow)->applyFromArray($styleArray);

        return [
            1 => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => 'center']],
            2 => ['font' => ['bold' => true], 'alignment' => ['horizontal' => 'center']],
            3 => ['font' => ['italic' => true], 'alignment' => ['horizontal' => 'center']],
            5 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => '4F46E5']]
            ],
        ];
    }
}
